-- Run on the source instance. msdb timestamps use server local time;
-- BackupAudit timestamps use UTC.
USE msdb;
GO
SELECT TOP (100) b.database_name,
    CASE b.type WHEN 'D' THEN 'FULL' WHEN 'I' THEN 'DIFF' WHEN 'L' THEN 'LOG' END AS BackupType,
    b.backup_start_date,b.backup_finish_date,b.is_copy_only,b.has_backup_checksums,
    CAST(b.backup_size/1048576.0 AS decimal(18,2)) AS OriginalMB,
    CAST(b.compressed_backup_size/1048576.0 AS decimal(18,2)) AS CompressedMB,
    m.physical_device_name,b.first_lsn,b.last_lsn,b.database_backup_lsn
FROM dbo.backupset b
JOIN dbo.backupmediafamily m ON m.media_set_id=b.media_set_id
JOIN DBAOperations.dbo.BackupWhitelist w ON w.DatabaseName=b.database_name AND w.Enabled=1
WHERE b.type IN ('D','I','L')
ORDER BY b.backup_finish_date DESC;
GO
-- OUTER APPLY keeps missing backups visible (NULL), instead of hiding the DB.
SELECT w.DatabaseName,d.recovery_model_desc,
       f.backup_finish_date AS LastFull,
       i.backup_finish_date AS LastDifferential,
       l.backup_finish_date AS LastLog,
       d.log_reuse_wait_desc
FROM DBAOperations.dbo.BackupWhitelist w
LEFT JOIN sys.databases d ON d.name=w.DatabaseName
OUTER APPLY (SELECT TOP (1) backup_finish_date FROM dbo.backupset
    WHERE database_name=w.DatabaseName AND type='D' AND is_copy_only=0
    ORDER BY backup_finish_date DESC) f
OUTER APPLY (SELECT TOP (1) backup_finish_date FROM dbo.backupset
    WHERE database_name=w.DatabaseName AND type='I'
    ORDER BY backup_finish_date DESC) i
OUTER APPLY (SELECT TOP (1) backup_finish_date FROM dbo.backupset
    WHERE database_name=w.DatabaseName AND type='L' AND is_copy_only=0
    ORDER BY backup_finish_date DESC) l
WHERE w.Enabled=1;
GO
SELECT TOP (100) * FROM DBAOperations.dbo.BackupAudit ORDER BY AuditId DESC;
GO
-- Verify the latest successful Jira FULL using its real generated file name.
DECLARE @File nvarchar(260);
SELECT TOP (1) @File=BackupFile FROM DBAOperations.dbo.BackupAudit
WHERE DatabaseName=N'DB-Jira' AND BackupType='FULL' AND Status='SUCCEEDED'
ORDER BY FinishedUtc DESC;
IF @File IS NULL THROW 51300, 'No successful Jira FULL backup found.', 1;
RESTORE VERIFYONLY FROM DISK=@File WITH CHECKSUM,STOP_ON_ERROR;
GO
-- Example freshness thresholds; integrate these rows with your monitoring.
-- A successful Agent execution is not a substitute for per-database checks.
SELECT w.DatabaseName,t.Kind,latest.FinishedUtc,
    CASE WHEN latest.FinishedUtc IS NULL THEN 'MISSING' ELSE 'STALE' END AS AlertReason
FROM DBAOperations.dbo.BackupWhitelist w
-- DIFF has a 12-hour overnight gap (19:00 to 07:00), so 7h would false-alert.
-- Use 13h here, or a schedule-aware check for stricter daytime detection.
CROSS JOIN (VALUES ('FULL',1560),('DIFF',780),('LOG',20)) t(Kind,MaxMinutes)
OUTER APPLY (
    SELECT TOP (1) a.FinishedUtc FROM DBAOperations.dbo.BackupAudit a
    WHERE a.DatabaseName=w.DatabaseName AND a.BackupType=t.Kind AND a.Status='SUCCEEDED'
    ORDER BY a.FinishedUtc DESC
) latest
WHERE w.Enabled=1 AND (latest.FinishedUtc IS NULL OR
    latest.FinishedUtc<DATEADD(minute,-t.MaxMinutes,SYSUTCDATETIME()));
GO
