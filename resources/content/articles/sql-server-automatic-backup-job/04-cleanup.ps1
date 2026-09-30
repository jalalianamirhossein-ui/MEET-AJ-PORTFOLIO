# Windows PowerShell 5.1. Default is preview; -Delete authorizes actual cleanup.
# Retain the verified FULL before the 30-day boundary, plus ALL backups since it.
[CmdletBinding(SupportsShouldProcess=$true)]
param(
    [string]$ServerInstance = 'localhost',
    [string]$BackupRoot = 'D:\SQLBackup',
    [ValidateRange(30,3650)][int]$RetentionDays = 30,
    [switch]$Delete
)
$ErrorActionPreference = 'Stop'
$rootPath = [IO.Path]::GetFullPath($BackupRoot).TrimEnd('\')
if ($rootPath -notmatch '^[A-Za-z]:\\.+' -or $rootPath.Length -gt 100) {
    throw 'Use a dedicated local backup root, not a drive root.'
}
function Assert-NoReparsePath([string]$Path) {
    $currentPath = [IO.Path]::GetFullPath($Path)
    while ($currentPath) {
        if (Test-Path -LiteralPath $currentPath) {
            $item = Get-Item -LiteralPath $currentPath -Force
            if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) {
                throw "Reparse point blocked: $currentPath"
            }
        }
        $parent = [IO.Directory]::GetParent($currentPath)
        if ($null -eq $parent) { break }
        $currentPath = $parent.FullName
    }
}
Assert-NoReparsePath $rootPath
$builder = New-Object System.Data.SqlClient.SqlConnectionStringBuilder
$builder.DataSource = $ServerInstance
$builder.InitialCatalog = 'DBAOperations'
$builder.IntegratedSecurity = $true
$builder.Encrypt = $true
$builder.TrustServerCertificate = $false
$connection = New-Object System.Data.SqlClient.SqlConnection $builder.ConnectionString
$candidates = New-Object System.Data.DataTable
try {
    $connection.Open()
    $command = $connection.CreateCommand()
    $command.CommandText = @'
DECLARE @Cutoff datetime2(3)=DATEADD(day,-@Days,SYSUTCDATETIME());
SELECT a.DatabaseName,a.BackupType,a.BackupFile,f.BackupFile AS AnchorFile
FROM dbo.BackupWhitelist w
CROSS APPLY (
    SELECT TOP (1) b.BackupFile,b.StartedUtc
    FROM dbo.BackupAudit b
    WHERE b.DatabaseName=w.DatabaseName AND b.BackupRoot=@Root
      AND b.BackupType='FULL' AND b.Status='SUCCEEDED' AND b.Verified=1
      AND b.FinishedUtc<=@Cutoff
    ORDER BY b.FinishedUtc DESC,b.AuditId DESC
) f
JOIN dbo.BackupAudit a ON a.DatabaseName=w.DatabaseName
WHERE w.Enabled=1 AND a.BackupRoot=@Root AND a.Status='SUCCEEDED'
  AND a.BackupType IN ('FULL','DIFF','LOG')
  AND a.FinishedUtc<f.StartedUtc AND a.FinishedUtc<@Cutoff
  AND a.BackupFile IS NOT NULL
ORDER BY a.DatabaseName,a.FinishedUtc;
'@
    $command.Parameters.Add('@Days',[Data.SqlDbType]::Int).Value=$RetentionDays
    $command.Parameters.Add('@Root',[Data.SqlDbType]::NVarChar,200).Value=$rootPath
    $reader=$command.ExecuteReader()
    $candidates.Load($reader)
    $reader.Close()
} finally { $connection.Dispose() }
$cutoffUtc=[DateTime]::UtcNow.AddDays(-$RetentionDays)
foreach ($row in $candidates.Rows) {
    $database=[string]$row.DatabaseName
    $kind=[string]$row.BackupType
    if ($database -notmatch '^[A-Za-z0-9_-]+$' -or
        $database -in @('master','model','msdb','tempdb')) {
        throw 'Invalid database name in cleanup manifest.'
    }
    $expectedDir=Join-Path (Join-Path $rootPath $database) $kind
    $filePath=[IO.Path]::GetFullPath([string]$row.BackupFile)
    $anchorPath=[IO.Path]::GetFullPath([string]$row.AnchorFile)
    $anchorDir=Join-Path (Join-Path $rootPath $database) 'FULL'
    if (-not [IO.Path]::GetDirectoryName($filePath).Equals($expectedDir,[StringComparison]::OrdinalIgnoreCase) -or
        -not [IO.Path]::GetDirectoryName($anchorPath).Equals($anchorDir,[StringComparison]::OrdinalIgnoreCase)) {
        throw "Manifest path is outside the expected backup directory: $filePath"
    }
    Assert-NoReparsePath $filePath
    Assert-NoReparsePath $anchorPath
    if (-not (Test-Path -LiteralPath $anchorPath -PathType Leaf)) {
        throw "Protected FULL backup is missing; cleanup stopped: $anchorPath"
    }
    $extension=if ($kind -eq 'LOG') { '.trn' } else { '.bak' }
    if ([IO.Path]::GetExtension($filePath) -ne $extension -or
        -not [IO.Path]::GetFileName($filePath).StartsWith($database+'_'+$kind+'_',[StringComparison]::OrdinalIgnoreCase)) {
        throw "Unexpected backup filename: $filePath"
    }
    if (-not (Test-Path -LiteralPath $filePath -PathType Leaf)) { continue }
    $file=Get-Item -LiteralPath $filePath -Force
    if ($file.LastWriteTimeUtc -ge $cutoffUtc) { continue }
    if (-not $Delete) { Write-Output "PREVIEW: $filePath"; continue }
    if ($PSCmdlet.ShouldProcess($filePath,'Delete expired SQL backup')) {
        Remove-Item -LiteralPath $filePath -ErrorAction Stop
        Write-Output "DELETED: $filePath"
    }
}
