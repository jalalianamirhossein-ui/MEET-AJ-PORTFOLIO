#requires -RunAsAdministrator
<# Fresh-install workflow for Windows Server 2022/2025, Agent 2 7.0.22+.
Download the current stable 7.0 x64 OpenSSL MSI from the official site first.
Requires approved SHA256 AND valid Zabbix Authenticode publisher signature.
Never pass PSK values to msiexec arguments or logs. Existing installs are refused.
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory)][string]$MsiPath,
    [Parameter(Mandatory)][ValidatePattern('^[A-Fa-f0-9]{64}$')][string]$ExpectedSha256,
    [Parameter(Mandatory)][string]$ApprovedSignerThumbprint,
    [Parameter(Mandatory)][string]$ConfigPath,
    [Parameter(Mandatory)][string]$PskSourcePath,
    [string]$ServerAddress = '192.0.2.10'
)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
if (Get-Service -Name 'Zabbix Agent 2' -ErrorAction SilentlyContinue) { throw 'Existing Agent 2 found; use a reviewed upgrade workflow.' }
if (Get-Service -Name 'Zabbix Agent' -ErrorAction SilentlyContinue) { throw 'Classic agent exists; review port and service migration first.' }
$msi = (Resolve-Path -LiteralPath $MsiPath).Path
$config = (Resolve-Path -LiteralPath $ConfigPath).Path
$pskSource = (Resolve-Path -LiteralPath $PskSourcePath).Path
foreach ($path in @($msi, $config, $pskSource)) {
    if ((Get-Item -LiteralPath $path).Attributes -band [IO.FileAttributes]::ReparsePoint) { throw 'Reparse-point input refused.' }
    if ($path.Contains('"')) { throw 'Invalid path.' }
}
if ((Get-FileHash -LiteralPath $msi -Algorithm SHA256).Hash -ne $ExpectedSha256) { throw 'MSI hash mismatch.' }
$signature = Get-AuthenticodeSignature -LiteralPath $msi
if ($signature.Status -ne 'Valid' -or $signature.SignerCertificate.Thumbprint -ne $ApprovedSignerThumbprint) { throw 'MSI signature/publisher mismatch.' }
$content = Get-Content -LiteralPath $config -Raw
$parsedAddress = $null
if (-not [System.Net.IPAddress]::TryParse($ServerAddress, [ref]$parsedAddress)) { throw 'ServerAddress must be an approved IP address for the firewall allowlist.' }
if ($content -notmatch ('(?m)^Server=' + [regex]::Escape($ServerAddress) + '\r?$')) { throw 'Configuration Server must match the firewall ServerAddress.' }
if ($content -notmatch ('(?m)^ServerActive=' + [regex]::Escape($ServerAddress) + ':10051\r?$')) { throw 'Configuration ServerActive must match the selected monitoring server.' }
foreach ($setting in @('TLSConnect=psk','TLSAccept=psk','TLSPSKFile=C:\ProgramData\Zabbix\agent2.psk','DenyKey=system.run[*]')) {
    if (-not $content.Contains($setting)) { throw "Required configuration missing: $setting" }
}
$psk = (Get-Content -LiteralPath $pskSource -Raw).Trim()
if ($psk -notmatch '^[a-fA-F0-9]{64}$') { throw 'Expected a unique 256-bit hex PSK.' }
$data = 'C:\ProgramData\Zabbix'
if (Test-Path -LiteralPath $data) { throw 'Existing data directory refused; inspect before installation.' }
New-Item -ItemType Directory -Path $data | Out-Null
& icacls.exe $data /inheritance:r /grant:r '*S-1-5-18:(OI)(CI)F' '*S-1-5-32-544:(OI)(CI)F' | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'ACL setup failed.' }
[IO.File]::WriteAllText("$data\agent2.psk", $psk + "`r`n", [Text.Encoding]::ASCII)
$psk = $null
Copy-Item -LiteralPath $config -Destination "$data\install.conf"
$arguments = "/i `"$msi`" /qn /norestart /l*v `"$data\install.log`" CONF=`"$data\install.conf`" DONOTSTART=1"
$process = Start-Process -FilePath msiexec.exe -ArgumentList $arguments -Wait -PassThru -WindowStyle Hidden
if ($process.ExitCode -notin @(0,3010)) { throw "MSI installation failed: $($process.ExitCode)" }
$target = 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.conf'
Copy-Item -LiteralPath "$data\install.conf" -Destination $target -Force
& icacls.exe $target /inheritance:r /grant:r '*S-1-5-18:F' '*S-1-5-32-544:F' | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'Configuration ACL setup failed.' }
$service = Get-CimInstance Win32_Service -Filter "Name='Zabbix Agent 2'"
if ($service.StartName -notin @('LocalSystem','NT AUTHORITY\SYSTEM')) { throw 'Adjust ACLs for the actual service account before startup.' }
New-NetFirewallRule -Name 'Zabbix-Agent2-TLS' -DisplayName 'Zabbix Agent 2 TLS from monitoring server' -Direction Inbound -Action Allow -Protocol TCP -LocalPort 10050 -RemoteAddress $ServerAddress -Profile Domain,Private | Out-Null
# Audit and remove any broad installer-created Agent rule before enabling the service.
Get-NetFirewallRule | Where-Object { $_.DisplayName -like '*Zabbix*' -and $_.Name -ne 'Zabbix-Agent2-TLS' } | Disable-NetFirewallRule
Set-Service -Name 'Zabbix Agent 2' -StartupType Automatic
Start-Service -Name 'Zabbix Agent 2'
Get-Service -Name 'Zabbix Agent 2'
Test-NetConnection -ComputerName $ServerAddress -Port 10051
if ($process.ExitCode -eq 3010) { Write-Warning 'MSI requests a reboot; schedule it and validate service startup afterwards.' }
