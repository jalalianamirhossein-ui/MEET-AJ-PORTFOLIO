> **HISTORICAL DECISION (still in force).** Pre-install version evidence. Live versions: [PROJECT-STATUS.md](PROJECT-STATUS.md). ADR: [architecture-decision-record.md](architecture-decision-record.md).

# Framework version decision

Canonical architecture after installation: `docs/architecture-decision-record.md`. This file remains the pre-install version evidence.

Verified 2026-09-15, before installing dependencies. The user subsequently upgraded the target PHP to **8.4**. This supersedes the initial PHP 8.2 constraint.

| Framework | PHP support | Security support ends | Decision |
| --- | --- | --- | --- |
| Laravel 11 | 8.2–8.4 | 2026-03-12 | Rejected: outside security support |
| Laravel 12 | 8.2–8.5 | 2027-02-24 | Initially selected for PHP 8.2; superseded by user upgrade |
| Laravel 13 | 8.3–8.5 | 2028-03-17 | Selected: latest supported major, compatible with PHP 8.4 |

Source: [official Laravel support matrix](https://laravel.com/framework/docs/releases), checked on the date above. Laravel 13 supports PHP 8.4 and receives security fixes through 2028-03-17. Plan framework/PHP upgrades before security support ends.

Use `laravel/framework:^13.0` and let Composer resolve the newest available compatible 13.x release with security blocking enabled. Set the application PHP floor/platform to 8.4. Commit the resulting composer.lock and report its exact installed version after installation; a version constraint is not evidence of a successful installation.

Use Filament 5 (`filament/filament:^5.0`) for the admin panel. Its [official requirements](https://filamentphp.com/docs/5.x/introduction/installation) specify PHP 8.2+ and Laravel 11.28+, covering the selected Laravel major. Use the panel builder's distributed assets, not a new frontend scaffold or custom Tailwind theme. No Node runtime is required in production.

The prior Laravel 11/Filament 3 scaffold and planning references are superseded by this decision. No hard compatibility reason justifies remaining on Laravel 11.

Approved language scope: English and Persian production; German database/draft support only. No German public routes, switcher option or hreflang until real translations and an explicit release change exist. German records cannot be published by the current CMS.

Exact dependency versions and executable environment results are recorded in phase reports and composer.lock once available. Composer platform resolution targets PHP 8.4, and actual PHP extension/version checks remain mandatory on the deployment host.
