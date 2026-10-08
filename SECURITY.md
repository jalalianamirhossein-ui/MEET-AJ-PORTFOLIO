# Security policy

This repository runs a Laravel 13 / Filament 5 portfolio CMS. Report suspected vulnerabilities privately through [GitHub security advisories](https://github.com/jalalianamirhossein-ui/MEET-AJ-PORTFOLIO/security/advisories/new), where enabled, or through an established private maintainer channel. Do not place credentials, contact submissions, or exploit details in public issues. Do not test production without explicit authorization.

The [2026-10-08 audit](docs/SECURITY-AUDIT-REPORT.md) records findings, fixes, evidence and limitations. [Security architecture](docs/current/SECURITY.md) describes authentication, authorization, uploads, CSRF, headers and logging. [Deployment](DEPLOYMENT.md) covers proxy trust, HTTPS and rollback.

Only the maintained Laravel implementation receives fixes. Legacy HTML/forms/workers under `resources/legacy/` are offline references and import sources; never serve them directly. Apply the locked Composer dependencies and patched vendored assets together. Run `composer audit --locked`, repository secret checks, PHP tests and frontend regressions before release. No formal ASVS certification or claim of complete security is made.
