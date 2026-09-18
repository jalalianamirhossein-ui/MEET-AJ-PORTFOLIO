# Admin / Filament 5 override

Admin-only identity. The public site stays blue.

- Keep native Filament 5 tables, filters, modals, notifications.
- Surfaces `#ffffff`, type slate `#334155` / `#0f172a`, primary crimson `#be123c`, selected/hover `#fff1f2`.
- Destructive actions `#7f1d1d` (brick), not the same fill as primary.
- New requests: light rose row + inset bar `#be123c`; sidebar badge white on `#7f1d1d`.
- Article/category colour in admin reuses the public topic palette (dot + tinted chip + readable label). It is not stored as a column.
- Honor `prefers-reduced-motion` in `filament-admin.css`.
- Do not port homepage glass or hero photography into the CMS.
- After editing `resources/css/filament-admin.css`, run `php artisan filament:assets` so `public/css/app/meet-aj-admin.css` stays in sync.
