# Architecture decision records

Decisions that shaped the Meet AJ Laravel CMS. An ADR records **why** a choice was made and what it rejected. It is not a status report: for the running state see [../../current/PROJECT-STATUS.md](../../current/PROJECT-STATUS.md).

| ADR | Title | Status |
|-----|-------|--------|
| [ADR-001](ADR-001-laravel-13-filament-5-stack.md) | Laravel 13 + PHP 8.4 + Filament 5 + Blade + MySQL stack, EN/FA public languages, draft-only German | Accepted, in force |
| [ADR-002](ADR-002-framework-version-selection.md) | Framework version selection: Laravel 13 over Laravel 11 / 12 | Accepted, in force (pre-install evidence) |

Both ADRs explicitly reject Laravel 11, PHP 8.2 as the floor, Filament 3, a `pages` table, a `contact_requests` table, and public `/de` routes. Documents that still describe those choices are historical.
