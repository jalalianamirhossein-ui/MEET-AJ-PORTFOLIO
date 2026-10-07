-- Provision SQLBackupFiles CmdExec proxy first; see article prerequisites.
-- Save 02-prepare-folders.ps1 as C:\DBA\Prepare-SqlBackupFolders.ps1.
USE msdb;
GO
DECLARE @Proxy sysname=N'SQLBackupFiles', @Operator sysname=NULL,
        @Owner sysname=SUSER_SNAME(), @Notify int,
        @Type varchar(4), @Job sysname, @Schedule sysname, @Command nvarchar(max),
        @Start int, @End int, @SubType int, @SubInterval int;
IF NOT EXISTS (SELECT 1 FROM dbo.sysproxies WHERE name=@Proxy AND enabled=1)
    THROW 51100, 'Create the enabled SQLBackupFiles CmdExec proxy first.', 1;
IF @Operator IS NOT NULL AND NOT EXISTS
   (SELECT 1 FROM dbo.sysoperators WHERE name=@Operator AND enabled=1)
    THROW 51101, 'Configured notification operator does not exist.', 1;
IF EXISTS (SELECT 1 FROM dbo.sysjobs
           WHERE name IN (N'MeetAJ - SQL FULL',N'MeetAJ - SQL DIFF',N'MeetAJ - SQL LOG'))
    THROW 51102, 'Jobs already exist. Review/update them; do not overwrite.', 1;
SET @Notify=CASE WHEN @Operator IS NULL THEN 0 ELSE 2 END;
BEGIN TRY
    BEGIN TRANSACTION;
    DECLARE types CURSOR LOCAL FAST_FORWARD FOR
        SELECT Kind FROM (VALUES ('FULL'),('DIFF'),('LOG')) t(Kind);
    OPEN types;
    FETCH NEXT FROM types INTO @Type;
    WHILE @@FETCH_STATUS=0
    BEGIN
        SET @Job=N'MeetAJ - SQL '+@Type;
        SET @Schedule=@Job+N' schedule';
        -- Jobs stay disabled until a manual FULL/DIFF/LOG test succeeds.
        EXEC dbo.sp_add_job @job_name=@Job, @enabled=0,
            @owner_login_name=@Owner, @notify_level_email=@Notify,
            @notify_email_operator_name=@Operator;
        EXEC dbo.sp_add_jobstep @job_name=@Job, @step_id=1,
            @step_name=N'Prepare whitelist folders', @subsystem=N'CmdExec',
            @proxy_name=@Proxy,
            @command=N'powershell.exe -NoProfile -NonInteractive -File "C:\DBA\Prepare-SqlBackupFolders.ps1" -ServerInstance "localhost" -BackupRoot "D:\SQLBackup"',
            @on_success_action=3, @on_fail_action=2, @retry_attempts=0;
        SET @Command=N'EXEC dbo.usp_BackupWhitelist @BackupType='''+@Type+
            N''', @BackupRoot=N''D:\SQLBackup'', @Verify=1;';
        EXEC dbo.sp_add_jobstep @job_name=@Job, @step_id=2,
            @step_name=N'Backup and verify', @subsystem=N'TSQL',
            @database_name=N'DBAOperations', @command=@Command,
            @on_success_action=1, @on_fail_action=2, @retry_attempts=0;
        SET @Start=CASE @Type WHEN 'FULL' THEN 010000 WHEN 'DIFF' THEN 070000 ELSE 000000 END;
        SET @End=CASE WHEN @Type='DIFF' THEN 190000 ELSE 235959 END;
        SET @SubType=CASE @Type WHEN 'FULL' THEN 1 WHEN 'DIFF' THEN 8 ELSE 4 END;
        SET @SubInterval=CASE @Type WHEN 'FULL' THEN 1 WHEN 'DIFF' THEN 6 ELSE 15 END;
        EXEC dbo.sp_add_jobschedule @job_name=@Job, @name=@Schedule,
            @enabled=1, @freq_type=4, @freq_interval=1,
            @freq_subday_type=@SubType, @freq_subday_interval=@SubInterval,
            @active_start_time=@Start, @active_end_time=@End;
        EXEC dbo.sp_add_jobserver @job_name=@Job, @server_name=N'(LOCAL)';
        FETCH NEXT FROM types INTO @Type;
    END;
    CLOSE types;
    DEALLOCATE types;
    COMMIT;
END TRY
BEGIN CATCH
    IF @@TRANCOUNT>0 ROLLBACK;
    THROW;
END CATCH;
GO
