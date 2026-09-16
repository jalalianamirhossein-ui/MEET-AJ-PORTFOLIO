# Phase 1 — project and environment

Status: implementation in progress; runtime/dependency validation pending. This report will be updated with executed results before advancing the phase.

## Files changed

- `composer.json`: Laravel 13 and PHP 8.4 target; Filament will be installed in phase 3.
- `bootstrap/app.php`, `bootstrap/providers.php`: isolate minimal application bootstrap until later phases register middleware/panel.
- `config/app.php`, `config/cms.php`: UTC storage timezone, Tehran display timezone, EN/FA public and EN/FA/DE database languages.
- `app/Providers/AppServiceProvider.php`, `routes/web.php`, `routes/console.php`: minimal application entry points.
- `scripts/validate-environment.php`, `scripts/verify-originals.php`: reproducible environment/source checks.
- `docs/framework-version-decision.md`: official-source framework decision, amended after user upgraded PHP to 8.4.
- Private ignored `.runtime/`: Composer, CA bundle, verified-download work files; not application dependencies or deployment artifacts.
- Existing additive scaffold retained: artisan, config files, public entry/rewrite, environment examples and storage directories. Original website files untouched.

## Commands executed

- `git status --short`, `git branch --show-current`, `Get-Command php,composer,node,mysql,mariadb,curl.exe`, targeted file/config reads.
- Official Laravel/Filament/PHP documentation checks via web tools.
- Bounded `curl.exe` HTTPS checks for PHP, Composer, Packagist and GitHub. Sandbox Schannel failed; escalated requests showed unavailable certificate revocation service or host timeouts.
- Retried with `--ssl-revoke-best-effort`, retaining TLS certificate validation; Composer/GitHub access succeeded. Direct PHP download hosts timed out.
- Downloaded Composer PHAR and compared SHA-256 with Composer's published checksum; downloaded CA bundle from curl.se.
- Queried GitHub PHP runtime mirror metadata. PHP 8.4.25 mirror digest matches the official PHP manifest: `43a8f67ed2e5223fafb21293c85976361808855405278cef2cf3037c3ae2529c`.
- Initial archive transfer timed out after 120s, incomplete archive not extracted. Started parallel byte-range download, with length and final digest validation required before extraction.
- Created runtime extension configuration and writable Laravel storage/cache directories.

## Validation and blockers

- Framework choice verified: Laravel 13 supports PHP 8.4 and security fixes through 2028-03-17. Laravel 11 excluded.
- Composer checksum verified.
- PHP execution, extension validation, Laravel installation, Composer audit and application boot are pending archive completion. No pass claimed for these items yet.
- No MySQL/MariaDB runtime detected locally. Disposable database validation remains required later.
- DirectAdmin target configuration has not been inspected; deployment-host checks are pending.
