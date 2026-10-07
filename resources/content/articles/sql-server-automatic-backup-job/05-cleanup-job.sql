-- Save 04-cleanup.ps1 as C:\DBA\Cleanup-SqlBackups.ps1; preview before enabling.
USE msdb;
GO
IF EXISTS (SELECT 1 FROM dbo.sysjobs WHERE name=N'MeetAJ - SQL Cleanup')
    THROW 51200, 'Cleanup job exists; review it instead of overwriting.', 1;
IF NOT EXISTS (SELECT 1 FROM dbo.sysproxies WHERE name=N'SQLBackupFiles' AND enabled=1)
    THROW 51201, 'SQLBackupFiles proxy is missing.', 1;
BEGIN TRY
    BEGIN TRANSACTION;
    DECLARE @Owner sysname=SUSER_SNAME();
    EXEC dbo.sp_add_job @job_name=N'MeetAJ - SQL Cleanup',@enabled=0,@owner_login_name=@Owner;
    EXEC dbo.sp_add_jobstep @job_name=N'MeetAJ - SQL Cleanup',
        @step_name=N'Clean expired backups',@subsystem=N'CmdExec',@proxy_name=N'SQLBackupFiles',
        @command=N'powershell.exe -NoProfile -NonInteractive -File "C:\DBA\Cleanup-SqlBackups.ps1" -ServerInstance "localhost" -BackupRoot "D:\SQLBackup" -RetentionDays 30 -Delete',
        @on_success_action=1,@on_fail_action=2,@retry_attempts=0;
    EXEC dbo.sp_add_jobschedule @job_name=N'MeetAJ - SQL Cleanup',
        @name=N'MeetAJ - cleanup daily 05:00',@freq_type=4,@freq_interval=1,
        @active_start_time=050000;
    EXEC dbo.sp_add_jobserver @job_name=N'MeetAJ - SQL Cleanup',@server_name=N'(LOCAL)';
    COMMIT;
END TRY
BEGIN CATCH
    IF @@TRANCOUNT>0 ROLLBACK;
    THROW;
END CATCH;
GO
