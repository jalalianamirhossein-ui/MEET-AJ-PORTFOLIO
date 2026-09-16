# PWA — Meet AJ

**Authority:** AUTHORITATIVE PWA document.  
**Verified:** 2026-09-16 against `public/sw.js`, `public/manifest.json`, `public/offline.html`.

## Files

| File | Role |
|------|------|
| `public/manifest.json` | Web app manifest (copied/published with site assets) |
| `public/sw.js` | Service worker |
| `public/offline.html` | Offline fallback document |

Registration is in homepage (and rebuilt Blade) markup pointing at `/sw.js` and `/manifest.json`.

## Cache name / version

```javascript
const ASSET_VERSION = "cms-3";
const CACHE_NAME = `meet-aj-v2.0.0-${ASSET_VERSION}`;
// → meet-aj-v2.0.0-cms-3
```

On activate, caches whose names are not `CACHE_NAME` are deleted. Navigation preload is enabled when the browser supports it.

## Precached on install

`cache.addAll(["/", "/manifest.json", "/offline.html"])`.

## Runtime caching

- **Documents** (navigation): network first; successful GET without `Cache-Control: no-store` is stored; 404/410 deletes the cached URL; on network failure: match request, else `/`, else `/offline.html`
- **Assets** matching `css|js|png|jpg|jpeg|gif|webp|svg|woff|woff2|ico`: cache-then-network update

## Excluded from the worker

The fetch handler **returns without responding** (browser default) when:

- method is not GET
- request is cross-origin
- `Authorization` header is present
- path is private (below)
- path ends with `.php`
- query string has `signature`

### Private prefixes

```javascript
const PRIVATE_PREFIXES = ["/admin", "/livewire", "/forms", "/storage/livewire-tmp"];
```

Match: exact prefix, `prefix/…`, or `prefix-…`.

This excludes:

- Filament admin
- Livewire endpoints
- Contact CSRF/POST (`/forms/…` and `*.php`)
- Livewire temp uploads

## Status

| Behaviour | Classification |
|-----------|----------------|
| Source files present in `public/` | IMPLEMENTED |
| Logic as described | IMPLEMENTED (code review) |
| HTTP availability under `php artisan serve` | LOCAL TESTED historically (asset publish); not re-probed in the documentation-only pass |
| Add-to-home-screen / offline article browse | NOT TESTED |
| Lighthouse PWA | NOT TESTED |
| Production service worker on meetaj.ir | NOT TESTED |

Do not claim the PWA is production-verified.
