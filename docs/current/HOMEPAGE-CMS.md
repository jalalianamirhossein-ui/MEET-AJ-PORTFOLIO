# Homepage CMS — Meet AJ

**Verified:** 2026-09-21  
**Admin route:** `/admin/homepage-contents`  
**Public view:** `/`

The homepage is rendered by `HomeController` and reads its editable copy from the `homepage_contents` table through `HomepageContentCatalog`. The catalog creates the seven default records on the first public or admin visit and never overwrites an existing record.

## Managed sections

| Key | Section | Editable content |
|-----|---------|------------------|
| `site` | Site identity | Site name, profile/logo images, navigation order, social links |
| `hero` | Hero | Badge, name, role, subtitle, image and CTA labels/links |
| `about` | About | Intro copy, facts, values, expertise domains, philosophy and motto |
| `stats` | Statistics | Counters, icons and bilingual labels |
| `skills` | Skills | Skill groups, names and percentage values |
| `resume` | Resume | Full education timeline plus bilingual professional experience summaries and highlights |
| `contact` | Contact | Headings, copy, location, map and contact cards |

Articles, services, testimonials and contact requests remain separate relational resources because they have their own publishing, ordering and authorization rules.

## Editing workflow

1. Open **Content → Homepage section** in Filament.
2. Edit the section's common fields or its **Section JSON** for repeatable data.
3. Keep the `key` unchanged. It is the stable identifier used by the Blade view.
4. Set **Published on homepage** off to hide the section without deleting its content.
5. Use **Order** to control the catalog order. Lower values appear first in the admin table and public query.

The JSON editor accepts UTF-8 and pretty-printed JSON. Bilingual values use the `_en` and `_fa` suffixes. Images and links should use paths or URLs that are available in the deployed application.

The canonical resume seed contains nine education/certification entries and two jobs with bilingual highlight lists. Migration `2026_09_20_000014_restore_resume_content` repairs older shortened records once; later admin edits are preserved.

## Deployment

Run the migration and clear compiled configuration/views after deploying the code:

```bash
php artisan migrate --force
php artisan optimize:clear
```

`DatabaseSeeder` also synchronizes the default records. The synchronizer uses `firstOrCreate`, so running it is safe after editorial changes.

## Code ownership

| Responsibility | File |
|----------------|------|
| Defaults and sync | `app/Services/HomepageContentCatalog.php` |
| Eloquent model | `app/Models/HomepageContent.php` |
| Admin resource | `app/Filament/Resources/HomepageContentResource.php` |
| Authorization | `app/Policies/HomepageContentPolicy.php` |
| Homepage data loading | `app/Http/Controllers/HomeController.php` |
| Public rendering | `resources/views/home.blade.php` |
| Schema | `database/migrations/2026_09_20_000013_create_homepage_contents_table.php`; resume repair: `2026_09_20_000014_restore_resume_content.php` |
