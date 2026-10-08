# Security and bug audit — 2026-10-08

The local Laravel application, CMS authorization and uploads, public content rendering, contact/session controls, service worker, first-party JavaScript, deployment configuration and locked Composer dependencies were reviewed. Confirmed defects were fixed in this checkout. No release, commit, production database change or real contact submission was performed.

## Findings and fixes

| Finding | Change and regression coverage |
|---|---|
| Stored article HTML rendered executable markup, including unsafe markup inside code blocks | Sanitize on every display, including existing records. Remove scripts/iframes/event handlers/unsafe links, preserve FA/EN attributes and inert code blocks byte-for-byte. Placeholders restore only in text, never attributes. |
| CMS homepage links could contain `javascript:` URLs | Validate structured link/media schemes and control characters before rendering, including contact fallback values. Stored editorial data stays unchanged. |
| Every remote client was a trusted proxy | Default to no trusted proxies; accept forwarded IP/proto/port only from configured IPs/CIDRs, ignore forwarded host. Tests cover forged headers and an actual configured CIDR. |
| Filament routes bypassed the web-only security headers | Apply middleware globally. Login pages and guest redirects receive security and private-cache headers. |
| Homepage CSRF/session HTML was publicly cacheable and precached by the service worker | Mark the homepage `no-store`, remove it from precache, advance cache version to `cms-5`, compare origins exactly, respect `no-store` for assets and reject private redirected responses. Update both worker and generator. |
| Editors could directly mount a requests widget hidden in the UI | Authorize `viewAny` on mount and hydration. The regression returns 403 for an editor. |
| Last administrator could be demoted, locking out administration | Model validation inside a transaction locks administrator rows and rejects the last-admin demotion. Existing deletion policies remain in place. |
| Invalid homepage JSON silently became empty content | Reject invalid JSON and non-object/non-array JSON before saving. Regression verifies original content remains intact. |
| Array/oversized article-search input caused server errors | Validate search/tag types, lengths and slug syntax; malformed requests return 400. |
| Clean old article URLs returned 404 after a slug change | Resolve redirect history for clean URLs as well as `.html` URLs, retain unrelated query parameters, remove `lang`, and never redirect to unpublished content. |
| Public image filenames used client extensions and upload state could claim existing unrelated paths | Use random MIME-derived filenames and Filament file-path tampering protection for article images and testimonial avatars. Tests submit a JPEG with an HTML filename and an unrelated stored path. This is hardening: Laravel temporary-upload validation already rejects PHP extensions. |

The earlier Persian filter/tag correction is retained: localized category labels are loaded from the database with eager loading, with existing English labels and safe fallbacks preserved.

Maintenance defects were also corrected: the Markdown checker resolved root-relative website URLs against the drive root instead of `public/`, falsely reporting 35 broken links. Five retired FortiGate public image copies now use the existing allowlisted redirect mechanism to their maintained equivalents; publishing removes only those known obsolete copies while retaining their URLs.

## Dependencies

135 locked production/development packages were queried against OSV. The first scan reported three advisories in two packages. The final scan reports none for those exact locked versions; this is a point-in-time registry check, not a proof that every dependency is vulnerability-free.

| Package | Update | Advisory |
|---|---|---|
| Filament (10 related packages) | 5.8.2 → 5.8.3 | [GHSA-7m6h-rg42-m449](https://github.com/filamentphp/filament/security/advisories/GHSA-7m6h-rg42-m449), MFA reauthentication. This app does not enable MFA. OSV flagged 5.8.2 although the upstream advisory describes versions below 5.8.2; updating avoids the discrepancy. |
| league/commonmark | 2.10.1 → 2.10.2 | [GHSA-3q6v-r5mr-hxv8](https://github.com/thephpleague/commonmark/security/advisories/GHSA-3q6v-r5mr-hxv8), quadratic table processing; [GHSA-97jj-33gv-5xf9](https://github.com/thephpleague/commonmark/security/advisories/GHSA-97jj-33gv-5xf9), raw-HTML filter bypass. Application-specific exploitability was not demonstrated. |

Composer's Packagist audit endpoint timed out; the successful OSV batch query was used instead. No broad unrelated dependency upgrade was made. Package discovery and Filament public asset publication succeeded.

## Verification

The pre-fix broad suite reported 136 tests, 20,845 assertions, 16 failures and one MySQL skip. Focused exploit regressions also reproduced the security defects before their fixes. Existing content tests had obsolete counts, image paths, canonical section IDs, single-language assumptions, pagination fixtures and Windows line-ending expectations. Those fixtures were updated to the current authored content and view contracts while retaining code equality, links, anchors, schema, asset and ordering checks. Content-comparison tests now create a fresh source baseline in their isolated database instead of assuming migrations contain identical legacy content.

The full SQLite suite passed: **150 tests, 21,840 assertions, zero failures/errors and one opt-in MySQL skip**, in 2m28s. A subsequent focused run passed **23 tests and 834 assertions**, verifying the legacy-image changes plus CMS operations and public-page regressions. PHP syntax checks passed for **223 files**, and Node syntax checks passed for **10 JavaScript files**. Composer validation, package discovery and Filament asset publication succeeded. Local evidence files are under ignored `storage/app/`: `security-audit-baseline.xml`, `security-audit-final.xml`, `security-assets-final.xml`, and the before/after OSV JSON responses. They are not release assets.

The Markdown inventory checker passes with no broken local links. Image verification passes for **101 maintained source images and 286 references**, with no missing, mismatched or obsolete public images remaining.

Frontend checks: `node --test tests/Frontend/scroll-reveal.test.cjs tests/Frontend/ServiceWorkerSecurityTest.cjs` passed all five Node tests. The worker check covers origin spoofing, private paths, no-store documents/assets, private redirects, homepage precaching and public asset caching.

## Deployment and limits

Deploy the lockfile and changed application/public files, refresh package discovery and Filament assets, then rebuild configuration. Set `TRUSTED_PROXIES` to the actual reverse-proxy IPs/CIDRs before caching configuration; leave it empty for direct connections. Do not restore blanket `*` proxy trust. The new worker version clears old application caches on activation.

Verification uses SQLite in-memory feature tests. Real MySQL/MariaDB, concurrent administrator changes on MySQL, SMTP delivery, production HTTPS/proxy settings, server upload execution rules, browser PWA installation/offline behavior, load testing and a production penetration test were not performed. Infrastructure scripts bundled as article examples were not executed against equipment. Existing editorial source/database drift is a separate maintenance issue; this audit did not overwrite live article bodies or CMS edits. No detected credential patterns in tracked files does not constitute a complete Git-history secret audit.
