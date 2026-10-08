# Enterprise security audit — 2026-10-08

## Executive result

**13 findings: 1 Critical, 2 High, 4 Medium, 3 Low, 3 Informational.** Severity includes an upstream-rated dependency advisory and three code-quality findings; it does not mean thirteen remotely exploitable vulnerabilities. Stored article XSS and service upload validation bypass were reproduced locally. A vulnerable Swiper version was confirmed; an application-specific remote exploitation path was not established. Plain HTTP serving was observed remotely. Fixes are implemented locally, with deployment and several operational checks still pending.

Repository: `jalalianamirhossein-ui/MEET-AJ-PORTFOLIO`. Starting state: clean `main`, revision `af5c9da`. Working branch: `codex/security-hardening-2026-10-08`. Remote name: `VSCode`. No unrelated uncommitted changes existed at discovery. No production release, database, credential or environment was modified. Reports omit secret and cookie values.

## Architecture and scope

Inspected the tracked tree, source/config/routes, nine Eloquent models, eight policies, seven controllers, middleware, Filament resources/widgets, all application migrations/seeders, Blade/raw HTML output, first-party JS, asset publisher/worker, deployment scripts, existing documentation/tests, and ignored runtime/storage boundaries. Automated inventory and syntax checks complement targeted manual review; third-party framework internals were inspected at relevant auth/upload boundaries, not exhaustively audited. Historical article runbooks are content, not application infrastructure commands, and were not executed on target equipment.

| Area | Observed architecture |
|---|---|
| Backend | Laravel **13.31.0**, PHP requirement **^8.4**, local PHP **8.4.25** |
| CMS | Filament **5.8.3**, Livewire **4.4.5**, session guard `web`, admin/editor roles |
| Frontend | Blade, vanilla JS/CSS, vendored UI libraries, Swiper now **12.1.2** |
| Database | SQLite locally and in default tests; MySQL/MariaDB deployment target |
| Runtime | File cache/sessions, synchronous mail; no API route file, external auth provider, scheduler, Redis or worker configured |
| Assets | `resources/assets/` and `resources/static/` published to `public/`; imports/runbooks remain outside document root |
| Deployment | Ubuntu shell helpers; DirectAdmin guidance also exists; requested server root `/var/www/meetaj` |
| CI | No workflow initially; now pinned read-only verification workflow, no automatic deployment |
| Dependencies | Composer lock and installed vendor exist; no package.json/npm lock/build pipeline |

## Findings

| ID | Severity | Component | Finding | Root Cause | Remediation | Verification | Status |
|---|---|---|---|---|---|---|---|
| SEC-01 | High | Article rendering | Confirmed stored XSS, CWE-79 | `ArticleContentStandardizer::standardize` decoded entities after `ArticleHtmlSanitizer`, converting inert numeric-entity text to markup | Removed post-sanitization decoding; normalize once in `Article::displayContent` before sanitizing | Regression produced two executable nodes before fix, zero after; full suite passes | Fixed locally; deploy pending |
| SEC-02 | Critical | Vendored Swiper | Confirmed affected dependency, upstream Critical advisory GHSA-hmx5-qpq5-p643 / CWE-1321 | Static Swiper 11.1.9 bypassed Composer dependency audit | Replaced matching JS/CSS/map with upstream 12.1.2, verified npm archive integrity, added MIT license/provenance and icon compatibility setting | Isolated JS checks crafted prototype keys with modified Array.indexOf; Chrome fixture EN/FA sliders initialize | Fixed locally; app-specific remote exploit not confirmed; publish/purge pending |
| SEC-03 | High | Production transport | Remotely observed HTTP homepage 200 without redirect; CWE-319 configuration weakness | HTTPS cookies/HSTS alone do not redirect a first HTTP visit; no source HTTPS enforcement | Added production `EnforceHttps`: canonical configured HTTPS origin for safe requests; unsafe HTTP requests rejected; supplied Nginx redirect/TLS template | HEAD `http://meetaj.ir` returned 200 with no Location; local tests verify 308, hostile Host handling, HTTPS pass-through and HTTP POST rejection | Local mitigation implemented; CDN/server correction and deployment pending |
| SEC-04 | Medium | HSTS | Configuration weakness: unsolicited subdomain scope | Middleware sent `includeSubDomains`; also observed remotely on HTTPS | Host-only `max-age=31536000`, no preload | Regression checks exact HTTPS header and absence on HTTP | Fixed locally; prior browser policy may persist until expiry |
| SEC-05 | Medium | Service uploads | Confirmed CMS validation bypass for existing file paths | `ServiceResource` used ordinary FileUpload while other image fields used SafeImageUpload | Reused SafeImageUpload with random MIME-derived names and path-tampering protection | A fake existing non-image was accepted with old control; old regression failed with no validation errors; fixed regression rejects it | Fixed locally; admin-authenticated scope, no unauthenticated file execution demonstrated |
| SEC-06 | Medium | Notification logs | Potential sensitive data exposure, CWE-532 | SMTP exception `getMessage()` logged arbitrary transport detail | Log request ID and exception class only | Test throws a synthetic sensitive message, asserts only safe context and retained request row | Fixed; no evidence actual production secrets were logged |
| SEC-07 | Medium | Private responses/worker | Configuration gap for Filament downloads and signed GETs | Explicit no-store/private exclusions covered admin/forms/Livewire but omitted Filament paths | Added `/filament` and signed-query no-store handling; worker excludes Filament/signed paths; version bumped to cms-6 | Feature tests check private headers; JS tests verify exclusions and source/published worker parity | Fixed locally; no cached private record leak demonstrated |
| SEC-08 | Low | Browser headers | Configuration weakness: missing CSP and permissions restrictions | Existing middleware only provided nosniff/referrer/frame/HSTS headers | Added `base-uri 'self'; object-src 'none'; frame-ancestors 'self'` and camera/microphone/geolocation denial | Home/login/error header tests; public fixture JS/RTL layouts pass | Compatible restrictions implemented; full script policy deferred |
| SEC-09 | Low | Release checks | No recurring CI verification gate | No `.github/workflows` present initially | Added pinned checkout/setup actions, least-privilege verification workflow, advisory/tests/lint/assets/docs/secret checks | Workflow syntax reviewed; constituent local checks run | Implemented; actual hosted CI execution pending |
| SEC-10 | Low | Frontend dependency provenance | Potential risk: unmanaged static libraries outside npm audit | No manifest covering vendored JS; several older distributions lack verified version provenance | Added vendor inventory, documented review procedure and Swiper advisory regression | Version headers/source reviewed; Composer audit separately clean | Partly addressed; broader frontend advisory/provenance review remains |
| SEC-11 | Informational | Publisher/tooling | Code quality: mixed PHP/JS source, dead generators and source eval | Embedded active worker/offline template; obsolete private homepage-generation chain; CLI repair evaluated migration array text | Extracted static sources, removed four unreachable private methods; repair reads embedded JSON and fails before writing on invalid metadata | Publish succeeds, worker equality/behavior and PHP syntax checks pass | Fixed; repair CLI itself not run against content |
| SEC-12 | Informational | Article search | Confirmed SQLite literal wildcard search bug | Backslash escaping assumed MySQL LIKE semantics without explicit ESCAPE | Explicit `!` escape with parameter-bound LIKE expressions across article/category/tag fields | `100%` test failed before fix; percent/underscore/exclamation/backslash cases now pass | Fixed; real MySQL execution still pending |
| SEC-13 | Informational | Related articles | Confirmed ignored method limit and excessive body loading | `relatedArticles($limit)` unconditionally replaced limit with 3 and selected full peer rows | Bounded caller limit 0–12 and used listing projection | Regression verifies 0/1/bounded limits and absence of peer content column | Fixed |

## Local reproductions and controls

SEC-01 used `<p>&#60;section&#62;...&#60;/section&#62;</p>` containing encoded script/event-handler tags. The initial normalizer did not recognize numeric entities; the sanitizer safely escaped them, but the standardizer recognized and decoded the resulting escaped section. DOM inspection of the final render found a script and onerror node. Removing the downstream decode closes that confirmed path while preserving FA/EN attributes and inert code examples. CMS authors are authenticated; the stored payload would affect public article visitors. The new regression is in `tests/Feature/EnterpriseHardeningTest.php`.

SEC-05 used Livewire, an admin test account, fake public storage, and `other-record/note.txt` as an existing featured-image path. The original control accepted the state; SafeImageUpload reports a featured_image error and does not insert the service. The test never touched real uploads.

Existing controls reviewed/tested: guests redirected to login; admin/editor policies; private requests widget mount/hydration authorization; published/date/language scopes and draft denial; request mass-assignment allowlist; parameter-bound search; plain-text validation errors; CSRF including legacy field contract; contact honeypot and 30/minute plus 5/hour limits; MIME/size-limited uploads; password hashing and 12–72 character UI/CLI rules; Filament login throttling, session regeneration, logout invalidation and token regeneration. No SQL/command injection, SSRF endpoint, unsafe request deserialization, user-controlled local-file inclusion, or unauthenticated administrative bypass was confirmed in the reviewed application paths. Absence of a finding is not proof of absence.

## Read-only production observations

Observed **2026-10-08**, around 19:32 Tehran time. Requests were limited HEAD checks and normal public page reads. No login attempts, contact submissions, exploit payloads, brute force, production vulnerability scanner or database commands were used.

| Endpoint | Observed result |
|---|---|
| `http://meetaj.ir/` | 200; no HTTPS Location redirect; Secure session cookie does not make the page itself encrypted |
| `https://meetaj.ir/` | 200; nosniff, strict-origin-when-cross-origin, SAMEORIGIN; HSTS included subdomains; private/no-cache; no CSP/Permissions-Policy |
| `/admin/login` over HTTPS | 200; no-store/private; sampled response lacked the middleware headers seen on the homepage |
| `/robots.txt`, `/sitemap.xml` over HTTPS | 200 with expected text/XML MIME types and existing security headers |
| `/.env` over HTTPS (HEAD only) | 403; no secret body retrieved |
| `/composer.json` over HTTPS (HEAD only) | 404; no configuration body retrieved |
| Session cookie | Secure, HttpOnly, SameSite=Lax, path `/`, approximately 120-minute expiry; values omitted |
| XSRF cookie | Secure and SameSite=Lax, intentionally readable by JS; values omitted |
| Disclosure | `Server: ArvanCloud`, no sampled PHP version header; edge identity alone is informational |

Windows Schannel initially failed strict revocation checking with CRYPT_E_NO_REVOCATION_CHECK. Retrying with `--ssl-revoke-best-effort` succeeded while retaining ordinary chain/hostname/date checks. This is limited TLS evidence: revocation availability, origin certificate, expiry details, TLS cipher inventory and server configuration were not independently verified. No `-k`/insecure certificate bypass was used. Production header differences do not identify the remote Git revision or prove the local fixes are deployed. Remote cache/proxy topology requires owner verification.

## Verification ledger

| Check | Actual result |
|---|---|
| `php artisan test` (PHP 8.4.25 host runtime) | **158 tests, 21,919 assertions, zero failures/errors, one MySQL skip**, 3:58.148 |
| New hardening regressions | 7 tests, 54 assertions passed; XSS/search/service cases also failed as expected against the old behavior before restoration/fix; trusted-proxy HTTPS regression reproduced an integration redirect and passes after middleware ordering correction |
| `php artisan route:list --json` | Passed; 47 actual Filament/application/hashed Livewire routes enumerated; `/admin/cms-users` absent |
| Route cache / Blade view cache | Passed; verification route/view caches cleared afterward |
| Composer validate / platform requirements | Passed |
| Composer audit `--locked --format=json` | Passed: no advisories, no abandoned packages reported by Packagist at execution time |
| Optimized autoload `--strict-psr --strict-ambiguous` | Passed; 7,752 classes; package discovery completed |
| PHP syntax | **225 PHP files**, zero failures |
| First-party JS syntax / Node frontend tests | Passed; **6 frontend tests**, including worker and Swiper prototype regressions |
| Asset publishing / Filament assets | Passed; final run published 206 public asset files and maintained two Blade view paths; admin assets generated |
| Images | 101 sources, 286 references, zero errors |
| Markdown links / documentation inventory | Passed after updates; final count recorded in refactoring report |
| Repository layout/secret signatures | Passed current tree and available Git text-history high-confidence checks; values never emitted; not an exhaustive secret detector |
| Environment/runtime permissions | Passed using host runtime; sandbox originally reported unreadable/unwritable paths, which were an ACL limitation |
| Chrome fixture QA | Public home/library/article at 1440×1000 and 390×844: no JS exceptions/missing local resources/horizontal overflow; EN/FA slider initialization and language switching passed |
| Browser limitations | Loopback HTTP timed out in the in-app browser and curl despite a listener; used Laravel-rendered local fixtures with local asset interception. Login render sampled only; Livewire login functionality not browser-tested |
| `npm audit`, `npm run build` | **Not applicable, not executed**: no npm manifest/build. Static dependency review was separate |
| Automated workflow YAML parser | Not executed: bundled Python lacks PyYAML; workflow was manually reviewed, and its constituent local checks passed |
| MySQL / actual SMTP / live admin / offline PWA / nginx -t / hosted CI / PHPStan | **Not executed**: disposable MySQL/service environment unavailable; no production credentials/actions used; Nginx/PHPStan not installed/configured; CI not pushed |

Windows sandbox exceptions were resolved using approved host execution for tests, network advisory requests and permission checks; they are not application test failures. Tests used isolated databases/array mail/fake uploads. Generated fixtures/screenshots/npm downloads stay ignored in `.runtime/`.

## Remaining risks and production actions

1. Review and deploy this branch. Correct CDN/server HTTP redirects, actual trusted proxies, header forwarding and private-cache policy before production activation. The local `FORCE_HTTPS` default can expose a bad proxy allowlist as redirect loops; test the real staging topology first.
2. Publish patched Swiper and cms-6 worker, purge old CDN assets, rebuild caches, and recheck HTTPS/home/login/error/download paths. Previous includeSubDomains policy is not instantly forgotten by clients.
3. Verify real MySQL search/schema behavior, protected uploads, SMTP delivery, live admin login/session/logout flows and offline PWA behavior in staging. Complete Nginx validation and the first hosted CI run. Fixture tests do not certify these paths.
4. Review other vendored libraries for provenance/advisories. Extend secret scanning privately if broader coverage is required; this scan skips binary/large current files and uses limited signatures over locally available history.
5. Full nonce/hash-based script CSP requires removal/review of existing inline handlers and Livewire compatibility; current CSP intentionally supplies base/object/frame protections only. Arbitrary permitted article inline styles remain an editorial-content risk requiring separate CSS policy work.
6. Existing content/source parity and optional missing historical NetBox PDF remain editorial maintenance items. No source reimport overwrote current CMS content during this task. Final-admin concurrent deletion/demotion across multiple sessions and data-retention requirements merit further review.

## Standards and references

Review areas were guided by [OWASP Top 10:2025](https://top10.owasp.org/2025/), [OWASP ASVS](https://owasp.org/projects/asvs), [Laravel cheat sheet](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html), [HTTP headers cheat sheet](https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html), [Laravel deployment](https://laravel.com/framework/docs/13.x/deployment), and [PHP security manual](https://www.php.net/manual/en/security.php). CWE references identify weakness classes; no formal compliance score or exhaustive ASVS control certification was performed. Secure-development improvements include regression coverage, dependency/provenance checks, CI, controlled deployment and rollback guidance. [Swiper advisory](https://github.com/nolimits4web/swiper/security/advisories/GHSA-hmx5-qpq5-p643) provides affected/patched ranges and upstream severity.
