-- SQL Server 2019/2022 on Windows, Standard/Enterprise/Developer.
-- DBA installation. No xp_cmdshell or undocumented folder procedures.
USE master;
GO
IF DB_ID(N'DBAOperations') IS NULL CREATE DATABASE [DBAOperations];
GO
USE [DBAOperations];
GO
IF OBJECT_ID(N'dbo.BackupWhitelist', N'U') IS NULL
CREATE TABLE dbo.BackupWhitelist
(
    DatabaseName sysname NOT NULL PRIMARY KEY,
    Enabled bit NOT NULL CONSTRAINT DF_BackupWhitelist_Enabled DEFAULT (1)
);
IF OBJECT_ID(N'dbo.BackupAudit', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.BackupAudit
    (
        AuditId bigint IDENTITY PRIMARY KEY,
        RunId uniqueidentifier NOT NULL,
        DatabaseName sysname NOT NULL,
        BackupType varchar(4) NOT NULL,
        StartedUtc datetime2(3) NOT NULL,
        FinishedUtc datetime2(3) NULL,
        BackupRoot nvarchar(200) NOT NULL,
        BackupFile nvarchar(260) NULL,
        Status varchar(12) NOT NULL,
        Verified bit NOT NULL CONSTRAINT DF_BackupAudit_Verified DEFAULT (0),
        ErrorNumber int NULL,
        ErrorMessage nvarchar(4000) NULL
    );
    CREATE INDEX IX_BackupAudit_Retention
        ON dbo.BackupAudit(DatabaseName, BackupType, Status, FinishedUtc);
END;
INSERT dbo.BackupWhitelist(DatabaseName)
SELECT v.DatabaseName FROM (VALUES
    (N'DB-Confluence'), (N'DB-Jira'), (N'DWConfiguration'), (N'DWDiagnostics'),
    (N'DWQueue'), (N'ORACLE_VIEW'), (N'RAYDANA_DB'), (N'STLA')
) v(DatabaseName)
WHERE NOT EXISTS
    (SELECT 1 FROM dbo.BackupWhitelist w WHERE w.DatabaseName = v.DatabaseName);
GO
CREATE OR ALTER PROCEDURE dbo.usp_BackupWhitelist
    @BackupType varchar(4),
    @BackupRoot nvarchar(200) = N'D:\SQLBackup',
    @Verify bit = 1,
    @EncryptionCertificate sysname = NULL
AS
BEGIN
    SET NOCOUNT ON;
    IF @@TRANCOUNT > 0 THROW 51000, 'Run backups outside a user transaction.', 1;
    SET @BackupType = UPPER(@BackupType);
    IF @BackupType IS NULL OR @BackupType NOT IN ('FULL','DIFF','LOG')
        THROW 51001, 'BackupType must be FULL, DIFF or LOG.', 1;
    IF @Verify IS NULL THROW 51002, 'Verify must be 0 or 1.', 1;
    IF @BackupRoot IS NULL OR LEN(@BackupRoot) > 100
       OR @BackupRoot NOT LIKE N'[A-Za-z]:\%'
       OR CHARINDEX(N'..', @BackupRoot) > 0 OR CHARINDEX(N'/', @BackupRoot) > 0
        THROW 51003, 'Use a local absolute backup root, up to 100 characters.', 1;
    WHILE RIGHT(@BackupRoot, 1) = N'\'
        SET @BackupRoot = LEFT(@BackupRoot, LEN(@BackupRoot)-1);
    IF LEN(@BackupRoot) < 4 THROW 51004, 'Do not use a drive root.', 1;
    IF NOT EXISTS (SELECT 1 FROM dbo.BackupWhitelist WHERE Enabled = 1)
        THROW 51005, 'Whitelist is empty; no backups were taken.', 1;
    IF @EncryptionCertificate IS NOT NULL AND NOT EXISTS
       (SELECT 1 FROM master.sys.certificates WHERE name = @EncryptionCertificate)
        THROW 51006, 'Encryption certificate is missing in master.', 1;

    DECLARE @RunId uniqueidentifier = NEWID(), @Database sysname,
            @AuditId bigint, @File nvarchar(260), @Sql nvarchar(max),
            @Stamp varchar(20), @Recovery nvarchar(60), @DatabaseId int,
            @State nvarchar(60), @SourceId int, @Failures int = 0,
            @LockResult int, @LockResource nvarchar(255), @Locked bit;
    DECLARE databases CURSOR LOCAL FAST_FORWARD FOR
        SELECT DatabaseName FROM dbo.BackupWhitelist WHERE Enabled = 1
        ORDER BY DatabaseName;
    OPEN databases;
    FETCH NEXT FROM databases INTO @Database;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        SET @File = NULL;
        SET @Locked = 0;
        INSERT dbo.BackupAudit(RunId,DatabaseName,BackupType,StartedUtc,BackupRoot,Status)
        VALUES(@RunId,@Database,@BackupType,SYSUTCDATETIME(),@BackupRoot,'RUNNING');
        SET @AuditId = SCOPE_IDENTITY();
        BEGIN TRY
            IF @Database COLLATE Latin1_General_100_BIN2 LIKE N'%[^A-Za-z0-9_-]%'
               OR UPPER(@Database) IN
                  (N'CON',N'PRN',N'AUX',N'NUL',N'COM1',N'COM2',N'COM3',N'COM4',
                   N'COM5',N'COM6',N'COM7',N'COM8',N'COM9',N'LPT1',N'LPT2',N'LPT3',
                   N'LPT4',N'LPT5',N'LPT6',N'LPT7',N'LPT8',N'LPT9')
                THROW 51007, 'Whitelist name is not safe for this folder layout.', 1;
            SELECT @DatabaseId = NULL, @Recovery = NULL, @State = NULL, @SourceId = NULL;
            SELECT @DatabaseId=database_id, @Recovery=recovery_model_desc,
                   @State=state_desc, @SourceId=source_database_id
            FROM sys.databases WHERE name = @Database;
            IF @DatabaseId IS NULL THROW 51008, 'Whitelist database does not exist.', 1;
            IF @DatabaseId <= 4 THROW 51009, 'System databases are excluded.', 1;
            IF @State <> N'ONLINE' OR @SourceId IS NOT NULL
                THROW 51010, 'Database must be online and not a snapshot.', 1;
            IF @BackupType = 'LOG' AND @Recovery = N'SIMPLE'
                THROW 51011, 'SIMPLE recovery does not support log backups.', 1;
            IF EXISTS (SELECT 1 FROM sys.databases
                       WHERE database_id=@DatabaseId AND group_database_id IS NOT NULL)
                THROW 51012, 'Availability Group needs a replica-aware backup policy.', 1;
            -- FULL/DIFF serialize; LOG may run concurrently with a data backup.
            SET @LockResource = N'MeetAJ.Backup.' + @Database +
                CASE WHEN @BackupType='LOG' THEN N'.LOG' ELSE N'.DATA' END;
            EXEC @LockResult = sys.sp_getapplock
                @Resource=@LockResource, @LockMode='Exclusive',
                @LockOwner='Session', @LockTimeout=60000;
            IF @LockResult < 0 THROW 51013, 'Could not acquire the database backup lock.', 1;
            SET @Locked = 1;
            SET @Stamp = CONVERT(char(8),GETUTCDATE(),112) + '_' +
                         REPLACE(CONVERT(char(12),GETUTCDATE(),114),':','');
            IF LEN(@BackupRoot)+LEN(@Database)*2+LEN(@BackupType)+65 > 259
                THROW 51014, 'Backup path would exceed 259 characters.', 1;
            SET @File = @BackupRoot + N'\' + @Database + N'\' + @BackupType +
                        N'\' + @Database + N'_' + @BackupType + N'_' + @Stamp + N'_' +
                        CONVERT(nvarchar(36),NEWID()) +
                        CASE WHEN @BackupType='LOG' THEN N'.trn' ELSE N'.bak' END;
            UPDATE dbo.BackupAudit SET BackupFile=@File WHERE AuditId=@AuditId;
            SET @Sql = CASE WHEN @BackupType='LOG' THEN N'BACKUP LOG ' ELSE N'BACKUP DATABASE ' END
                + QUOTENAME(@Database) + N' TO DISK=@Path WITH '
                + CASE WHEN @BackupType='DIFF' THEN N'DIFFERENTIAL, ' ELSE N'' END
                + N'COMPRESSION, CHECKSUM, STOP_ON_ERROR, STATS=10'
                + CASE WHEN @EncryptionCertificate IS NULL THEN N'' ELSE
                    N', ENCRYPTION (ALGORITHM=AES_256, SERVER CERTIFICATE='
                    + QUOTENAME(@EncryptionCertificate) + N')' END + N';';
            EXEC sys.sp_executesql @Sql, N'@Path nvarchar(260)', @Path=@File;
            IF @Verify=1
            BEGIN
                SET @Sql = N'RESTORE VERIFYONLY FROM DISK=@Path WITH CHECKSUM, STOP_ON_ERROR;';
                EXEC sys.sp_executesql @Sql, N'@Path nvarchar(260)', @Path=@File;
            END;
            UPDATE dbo.BackupAudit
                SET Status='SUCCEEDED',FinishedUtc=SYSUTCDATETIME(),Verified=@Verify
                WHERE AuditId=@AuditId;
        END TRY
        BEGIN CATCH
            SET @Failures += 1;
            UPDATE dbo.BackupAudit SET Status='FAILED',FinishedUtc=SYSUTCDATETIME(),
                ErrorNumber=ERROR_NUMBER(),ErrorMessage=ERROR_MESSAGE()
                WHERE AuditId=@AuditId;
        END CATCH;
        IF @Locked=1
            EXEC sys.sp_releaseapplock @Resource=@LockResource, @LockOwner='Session';
        FETCH NEXT FROM databases INTO @Database;
    END;
    CLOSE databases;
    DEALLOCATE databases;
    IF @Failures > 0
        THROW 51015, 'One or more backups failed. Check DBAOperations.dbo.BackupAudit.', 1;
END;
GO
