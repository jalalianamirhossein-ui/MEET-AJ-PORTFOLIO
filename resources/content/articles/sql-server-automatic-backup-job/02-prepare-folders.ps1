# Windows PowerShell 5.1. Provision folders without enabling xp_cmdshell.
param([string]$ServerInstance = 'localhost', [string]$BackupRoot = 'D:\SQLBackup')
$ErrorActionPreference = 'Stop'
$rootPath = [IO.Path]::GetFullPath($BackupRoot).TrimEnd('\')
if ($rootPath -notmatch '^[A-Za-z]:\\.+' -or $rootPath.Length -gt 100) {
    throw 'Use a local absolute backup folder, not a drive root.'
}
function Assert-NoReparsePath([string]$Path) {
    $currentPath = [IO.Path]::GetFullPath($Path)
    while ($currentPath) {
        if (Test-Path -LiteralPath $currentPath) {
            $item = Get-Item -LiteralPath $currentPath -Force
            if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) {
                throw "Reparse points are not permitted: $currentPath"
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
try {
    $connection.Open()
    $command = $connection.CreateCommand()
    $command.CommandText = 'SELECT DatabaseName FROM dbo.BackupWhitelist WHERE Enabled=1;'
    $reader = $command.ExecuteReader()
    $databases = @()
    while ($reader.Read()) { $databases += $reader.GetString(0) }
    $reader.Close()
    if ($databases.Count -eq 0) { throw 'Whitelist is empty.' }
    foreach ($database in $databases) {
        if ($database -notmatch '^[A-Za-z0-9_-]+$' -or
            $database -match '^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$' -or
            $database -in @('master','model','msdb','tempdb')) {
            throw "Unsupported database folder name: $database"
        }
        foreach ($kind in @('FULL','DIFF','LOG')) {
            $directory = Join-Path (Join-Path $rootPath $database) $kind
            Assert-NoReparsePath $directory
            [IO.Directory]::CreateDirectory($directory) | Out-Null
        }
    }
} finally { $connection.Dispose() }
