# Security — Meet AJ

> Security and bug review: 2026-10-08. Stored article HTML and homepage URLs are sanitized, proxy trust is explicit, private widgets authorize Livewire requests, and private responses bypass browser caches. Filament is 5.8.3 and CommonMark is 2.10.2. See [the security audit](../qa/SECURITY-BUG-AUDIT-2026-10-08.md) for reproductions, verification and limits.

> Maintenance review: 2026-10-06. Eight policy files exist, including homepage content and testimonials. Selected MSI/Bash files are public downloads; the MikroTik source ZIP remains outside the document root. Environment recovery preserves existing APP_KEY. See [current status](PROJECT-STATUS.md) and [the dated audit](../qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md).

**Authority:** AUTHORITATIVE security-controls document.
**Verified:** 2026-09-17 against middleware, the contact stack, Filament policies, `.env.example` and `.env.production.example`.
**No penetration test was performed.**
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md)

## CSRF

Laravel CSRF for session POSTs. Contact form uses a **named field** `csrf_token` (legacy contract) plus `AcceptLegacyCsrfToken` so the original JS keeps working. Token endpoint:

`GET /forms/get-csrf-token.php` → JSON `{ token, success }` with `Cache-Control: no-store`.

Invalid CSRF → Laravel 419 (not the contact `400` text channel).

## Honeypot

POST field `website`. If filled, the controller returns plain `OK` and **does not insert** a `requests` row (PHPUnit).

## Rate limiting

1. Route middleware `throttle:30,1` on `POST /forms/contact.php`
2. Application limiter `contact:{ip}` — **5** successful-path attempts per **3600** seconds → HTTP **429** `text/plain`

Honeypot returns before the hit counter.

## Validation

`StoreContactRequest`: name 2–50, email max 100, subject 5–100, message 10–1000, phone optional max 40, website optional, service slug optional (`^[a-z0-9]+(?:-[a-z0-9]+)*$`). Failures: HTTP **400**, first error as `text/plain` (legacy strings). Unknown published slugs are ignored (`service_id` stays null).

## Authentication

Filament session login. Passwords hashed by Laravel (`Hash`). `cms:create-user` enforces min 12 / max 72 characters. Remember token column exists.

## Authorization

Policies: articles, categories and tags for `canManageContent()` (admin + editor); services, requests and users admin-only. `UserResource::canViewAny()` and `shouldRegisterNavigation()` both return true only for admins, so the Users screen is visible to admins and hidden from editors. Guest `/admin` redirected. Public users may read published services and create requests; they cannot create/edit/publish services or change prices.

## Password hashing

Standard Laravel hasher (bcrypt/argon as configured). No plaintext passwords in the repository. **Never commit `.env`.**

## Security headers (`App\Http\Middleware\SecurityHeaders`)

| Header | Value |
|--------|--------|
| `X-Content-Type-Options` | `nosniff` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `X-Frame-Options` | `SAMEORIGIN` |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` **only if** `$request->secure()` |
| Cache-Control | `no-store` on the session-bearing homepage, `/admin`, `/livewire`, `/forms`, and all POST |
| Article caching | `private, max-age=0, must-revalidate`; HTML depends on the language preference cookie |

Not a full CSP. Clickjacking protection is SAMEORIGIN, not DENY.

The middleware is global, including Filament login pages and guest redirects. `TRUSTED_PROXIES` is a comma-separated list of actual proxy IPs/CIDRs, empty by default. Forwarded client IP, scheme and port are accepted only from that list; forwarded host is ignored. Set the actual addresses before caching production configuration if a reverse proxy is present. Do not use `*`.

Article HTML passes through `ArticleHtmlSanitizer` at display time, including existing database records. Safe code blocks retain exact whitespace; executable tags, event handlers and unsafe URL schemes are removed. FA/EN attributes remain available. Homepage structured URLs are validated at display time without rewriting stored editorial content. The requests widget authorizes both mount and subsequent Livewire hydration. Demoting the final administrator is rejected inside a transaction.

## Environment

| Key | Production requirement |
|-----|------------------------|
| `APP_DEBUG` | **false** |
| `APP_ENV` | `production` |
| `APP_KEY` | unique, generated on the server |
| `APP_URL` | `https://meetaj.ir` |
| `SESSION_SECURE_COOKIE` | `true` (requires HTTPS) |

Local `php artisan about` on 2026-09-16 showed **debug ENABLED**. That is a local `.env` setting, not production.

`.env`, `.env.testing`, and secrets must stay out of git (see `.gitignore`).

## Upload validation

Article featured image: JPEG/PNG/WebP, max **5120** KB (Filament field). Do not treat this as a full malware scan.

Testimonial avatars use the same formats, max **2048** KB. Both fields use `SafeImageUpload`: random filenames with MIME-derived extensions and authorization of existing file paths. Livewire temporary uploads already block PHP extensions through Laravel validation; the new naming rule also prevents other client-supplied extensions from reaching public storage. Rich-editor attachments use Laravel's MIME-derived hashed storage filenames.

## Private routes

`/admin/*`, `/livewire/*`, `/forms/*` are excluded from the service worker and from `robots.txt` Allow. Sitemap omits them.

## Mail

If `CONTACT_NOTIFICATION_EMAIL` is set and SMTP throws, the contact **row is kept** and the error is logged. Mail failure is not a 500 to the visitor.

## What was not done

- Penetration testing
- An automated dependency audit as a continuing release gate (a one-time OSV check of 135 locked packages was completed on 2026-10-08 with no advisories remaining after updates)
- Production HTTPS / HSTS observation on meetaj.ir
- WAF / DirectAdmin ModSecurity tuning
