# Requests (contact and quotes) — Meet AJ

**Authority:** AUTHORITATIVE description of the inbound request pipeline.
**Verified:** 2026-09-17 against `app/Http/Controllers/ContactController.php`, `app/Http/Requests/StoreContactRequest.php`, `app/Http/Middleware/AcceptLegacyCsrfToken.php`, `app/Models/Request.php`, `app/Filament/Resources/RequestResource.php`, `routes/web.php`, and PHPUnit (`PublicSiteTest`, `RequestWorkflowTest`).
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

The `requests` table currently holds **0 rows** on this workstation.

## Endpoints

The original static site's PHP endpoint paths are preserved so the existing front-end JavaScript keeps working unchanged.

| Method | URL | Name | Behaviour |
|--------|-----|------|-----------|
| GET | `/forms/get-csrf-token.php` | `contact.token` | Starts the session and returns JSON `{ token, success }` with `Cache-Control: no-store` |
| POST | `/forms/contact.php` | `contact.store` | Validates, rate-limits and stores the submission; returns plain text |

`POST /forms/contact.php` carries the route middleware `throttle:30,1`.

## Submission flow

1. The browser fetches a CSRF token from `/forms/get-csrf-token.php`.
2. The form posts `csrf_token` (the legacy field name) plus the visitor fields. `AcceptLegacyCsrfToken` maps that field onto Laravel's expected token, so CSRF protection is real, not bypassed.
3. If the honeypot field `website` is filled, the controller returns `OK` immediately and **stores nothing**.
4. The application rate limiter keyed `contact:{ip}` allows **5 attempts per 3600 seconds**; the sixth returns HTTP **429** with a plain-text message.
5. `StoreContactRequest` validates the payload. Failures return HTTP **400** with the first error as plain text, matching the original contract.
6. When an optional `service` slug is present and matches a **published English** service, `service_id` is set; an unknown slug is ignored and the row is saved with `service_id = NULL`.
7. The row is created with `status = new` and the response is plain `OK`.
8. If `CONTACT_NOTIFICATION_EMAIL` is configured, a notification mail is attempted. A mail failure is logged and the stored row is kept — the visitor still receives `OK`.

## Validation rules

| Field | Rule |
|-------|------|
| `name` | required, 2–50 characters |
| `email` | required, valid email, max 100 |
| `subject` | required, 5–100 characters |
| `message` | required, 10–1000 characters |
| `phone` | optional, max 40 |
| `website` | optional honeypot, must stay empty |
| `service` | optional slug matching `^[a-z0-9]+(?:-[a-z0-9]+)*$` |

## Stored data

`requests` columns: `name`, `email`, `phone`, `subject`, `message`, `status`, `service_id`, `internal_notes`, timestamps. CSRF tokens, passwords and the honeypot value are never stored. Schema detail: [DATABASE.md](DATABASE.md).

## Status workflow

`Request::STATUSES` defines seven values, with `new` as the model default:

`new` → `contacted` → `in_discussion` → `quoted` → `approved` → `completed`, plus `cancelled` as a terminal state.

Nothing enforces a linear transition; an admin can select any status. Migration `2026_09_17_000009` remapped the earlier `in_progress`, `resolved` and `spam` values onto this workflow.

## Internal notes

`internal_notes` is admin-only. It is listed in the model's `$hidden` array, so it never appears in JSON serialization, never reaches the public site, and is not included in the notification mail.

## Admin handling

Filament **Communications → Requests**, admin only. Inbound fields are read-only; only `status` and `internal_notes` can be changed. The screen offers status/service/date filters, a “new” count badge in the sidebar, coloured status badges, and bulk “mark completed” / “mark cancelled” actions. Creating a request from Filament is denied by policy. Detail: [ADMIN.md](ADMIN.md).

## Mail

Mail is optional (`config('cms.mail_is_optional')` is true). Locally the mailer is `log`. `ContactReceivedMail` is sent only when `CONTACT_NOTIFICATION_EMAIL` is set.

| Item | Status |
|------|--------|
| Token endpoint, persistence, honeypot, 400, 429 | PASS (PHPUnit) |
| Service linking from a service page | PASS (PHPUnit) |
| Mail transport failure keeps the row | PASS (PHPUnit) |
| Real SMTP delivery on DirectAdmin | BLOCKED · NOT TESTED |
