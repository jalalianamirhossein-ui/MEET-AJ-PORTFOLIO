> **HISTORICAL phase log.** Current behaviour: [../current/ARTICLES.md](../current/ARTICLES.md), [../current/REQUESTS.md](../current/REQUESTS.md), [../current/ADMIN.md](../current/ADMIN.md).

# Phase 08 — Tags and request workflow

**Phase:** 08
**Date:** 2026-09-17
**Status:** **PASS** locally

## Objective

Give the article library a real tag vocabulary and search, and turn the contact table into a light request workflow with private internal notes.

## Changes

- Migration `2026_09_17_000009_create_tags_and_request_workflow`:
  - `tags` with unique `name` and unique `slug`
  - `article_tag` pivot with composite primary key `(article_id, tag_id)` and cascade on both foreign keys, plus an index on `tag_id`
  - `requests.internal_notes` nullable text
  - remapped the earlier `in_progress` / `resolved` / `spam` statuses onto the seven-value workflow
- `App\Models\Tag`, `Article::tags()`, `scopeSearch()`, `scopeWithTag()`, `scopeForListing()` and `relatedArticles()`.
- `App\Services\ArticleTagAssigner` and `php artisan articles:sync-tags` to derive the vocabulary from real article titles and categories.
- `Request::STATUSES` with `new`, `contacted`, `in_discussion`, `quoted`, `approved`, `completed`, `cancelled`; `internal_notes` added to the model's `$hidden`.
- Filament `TagResource` (admin + editor) and an expanded `RequestResource` with status badges, service/status/date filters, a “new” navigation badge and bulk complete / cancel actions.
- Article library toolbar with search field, tag chips and paginated results (9 per page); related articles and share links on the detail page.

## Files changed

`database/migrations/2026_09_17_000009_create_tags_and_request_workflow.php`, `app/Models/{Article,Tag,Request}.php`, `app/Services/ArticleTagAssigner.php`, `app/Console/Commands/SyncArticleTags.php`, `app/Policies/{TagPolicy,RequestPolicy}.php`, `app/Filament/Resources/{TagResource,RequestResource}.php` + pages, `app/Http/Controllers/ArticleController.php`, `resources/views/articles/index.blade.php`, `resources/views/articles/partials/*`, `resources/views/components/article-card.blade.php`, `tests/Feature/{ArticleLibraryTest,RequestWorkflowTest}.php`.

## Commands executed

```text
php artisan migrate
php artisan articles:sync-tags
php artisan route:list
php artisan test
```

## Tests

`ArticleLibraryTest` (5 tests) covers search, tag filtering, pagination, related articles and share links. `RequestWorkflowTest` (3 tests) covers the status workflow, admin-only access and the hidden internal notes. Suite result after this phase: **39 tests, 647 assertions, 1 skipped, 0 failures**.

## Results

8 tags (Linux, Microsoft, MikroTik, VMware, Windows Server, Networking, Security, DevOps) and 38 article↔tag links. `/admin/tags` appeared in `route:list` after `optimize:clear`, bringing the route total to 36. Requests gained the seven-status workflow with private notes that never leave Filament.

## Blockers

- Interactive Filament use of the new screens was not exercised (no CMS user).
- Search remains SQL `LIKE`; no search engine was introduced.
