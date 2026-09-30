# SQL Server Automatic Backup Job — editorial package

Persian enterprise article for the existing `/articles/sql-server-automatic-backup-job` URL.
The legacy HTML stays unchanged. Category, tags, images, publication date and URL are preserved.

## Website installation

```powershell
php scripts/update-sql-backup-article.php          # preview
php scripts/update-sql-backup-article.php --apply  # update this environment's existing row
php artisan view:clear
```

The updater saves the previous row in `storage/app/private/article-revisions/` before changing it.
It executes no SQL Server backup, Agent job or PowerShell cleanup. Do not run legacy import with
`--refresh` after editorial changes: it discards CMS edits. The original-versus-rendered legacy
content comparison will intentionally differ for this rewritten article.

`article.html` is the source text; `metadata.json` supplies title, description and eight visible
FAQ entries. `build.php` embeds the exact SQL/PowerShell files, HTML-escapes them, and derives
the table of contents and FAQPage schema. Reapply explicitly when updating this package;
it is not an automatic sync that overrides later CMS edits.

## SQL Server runbook

1. Review SQL Server 2019/2022 Windows standalone edition, Recovery Models, storage capacity,
   Windows service identities, certificate/FQDN and eight sample database names.
2. Install `01-backup-procedure.sql` through SSMS as a trusted DBA. Review whitelist.
3. Provision a Windows Credential and a CmdExec proxy named `SQLBackupFiles` using secure
   administrative tooling. Grant the proxy account SQL login/access to DBAOperations and
   SELECT on BackupWhitelist/BackupAudit; provision filesystem permissions separately.
4. Save `02-prepare-folders.ps1` as `C:\DBA\Prepare-SqlBackupFolders.ps1`. Protect that directory
   from modification by proxy and SQL service accounts. Prepare folders; initialize FULL
   recovery where approved; take the first normal FULL, then test DIFF and LOG separately.
5. Run `03-agent-jobs.sql` after configuring instance name and optional Operator. Jobs start
   disabled. Test manually, inspect per-database results and validate restores, then enable.
6. Provision/export the encryption certificate/private key securely. Add the encryption
   parameter to all backup jobs before production where policy requires encryption.
7. Save `04-cleanup.ps1` as `C:\DBA\Cleanup-SqlBackups.ps1`. Preview it, test with `-Delete -WhatIf`,
   and review the protected FULL and off-site copies. `05-cleanup-job.sql` creates a disabled job.
8. Use `06-validation.sql`, configure Database Mail/failure notifications and external freshness,
   capacity and Agent-health monitoring. Record a real restore test and measured RPO/RTO.

Times in Agent schedules are server-local; audit and filenames are UTC. Cleanup keeps a verified
FULL before the 30-day boundary and all backups since its start, so retention may exceed 30 days.
No verified anchor means no deletion for that DB. Missing anchor files stop cleanup. Failed or
unknown files are left for investigation. Existing audit/msdb history is not purged.

This example assumes one backup coordinator, intact audit history and a protected filesystem.
Other tools' normal FULL backups, external file deletion, DB recreation/recovery forks and AG
failover need a revised policy and restore-chain validation. VERIFYONLY does not prove a restore
or application consistency. Jira/Confluence application homes and attachments require a
coordinated application-level backup too.

## Verification limits

PHP tests verify rendering, schema/FAQ consistency, anchors and exact code preservation.
PowerShell is parsed without executing it. An actual SQL Server instance is required for
execution, permissions, scheduling, backup/restore and cleanup validation; website tests do
not certify those behaviors on a production instance.
