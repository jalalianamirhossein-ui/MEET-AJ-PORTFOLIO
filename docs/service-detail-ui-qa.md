# Service detail UI QA

**Date:** 2026-09-16  
**Template:** `resources/views/services/show.blade.php`  
**CSS:** `visual-upgrade.css` service-landing block + `services.css`

## Structure (all six)

Hero (icon via kicker, title, description, price, primary/secondary CTA) → Overview → What is included → Pricing (existing amount, unit, IRR note, exclusions) → Deliverables → Process (imported steps only) → SLA → Add-ons → FAQ accordion → “Need this service?” CTA → form.

Process steps are **not** the example Discovery/Planning list. They are the imported `timeline-item` rows (e.g. Network: شناخت / طراحی / ساخت و کانفیگ / آزمون و تحویل; DevOps: ارزیابی / کانتینریزاسیون / CI/CD / IaC / تحویل).

## Per-page fetch (same origin, FA session)

| Slug | h1 | hidden form | php-email-form | csrf_token | JSON-LD | canonical | data-fa count |
|------|----|-------------|----------------|------------|---------|-----------|---------------|
| network-design | yes | yes | yes | yes | yes | yes | 82 |
| system-administration | yes | yes | yes | yes | yes | yes | 83 |
| devops-automation | yes | yes | yes | yes | yes | yes | 85 |
| monitoring-security | yes | yes | yes | yes | yes | yes | 83 |
| virtualization-solutions | yes | yes | yes | yes | yes | yes | 85 |
| technical-consulting | yes | yes | yes | yes | yes | yes | 84 |

None shipped `class="contact-form show"`.

## Interaction

- Network Design @ 1440: back control at x=16, language switcher at x=1352 (no overlap after v1119). Form `display:none` until CTA; after click `display:block`, class `show is-open`, focus on name, height 768, overflow 0.
- DevOps @ 375 and 1920: overflow 0; price ۶,۹۰۰ درهم from imported data.

## Form contract (unchanged)

Fields: name, email, phone, service `<select>` of catalog, subject, message, honeypot `website`, `csrf_token`. POST `/forms/contact.php` after GET `/forms/get-csrf-token.php`. PHPUnit `test_service_request_stores_service_id` still PASS.

## Result

**PASS · LOCAL TESTED** for landing structure, hidden form, EN/FA attributes, existing prices/process/FAQ. Live submit of a new quote in the browser was **not** repeated (PHPUnit covers persistence).
