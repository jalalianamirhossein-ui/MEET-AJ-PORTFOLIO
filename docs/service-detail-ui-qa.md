# Service detail UI QA

**Date:** 2026-09-17  
**Canonical route:** `/services/{slug}` via `resources/views/services/show.blade.php`  
**Static** `resources/views/services/{slug}.blade.php` files are unused leftovers.

## DNA vs landing

Service pages use Article tokens (type, color, 12px buttons, H2 bar, 56rem story width) without copying TOC/code-block layout.

## Pages

| Slug | Price (existing) | Form until CTA | Visual |
|------|------------------|----------------|--------|
| network-design | AED 4,900 | hidden | LOCAL TESTED 1440/375 |
| system-administration | AED 3,900 | hidden | LOCAL TESTED 1024 |
| monitoring-security | AED 4,200 | hidden | LOCAL TESTED structure |
| virtualization-solutions | AED 5,900 | hidden | LOCAL TESTED structure |
| technical-consulting | AED 2,500 | hidden | LOCAL TESTED structure |
| devops-automation | AED 6,900 | hidden | LOCAL TESTED 320/768/1920 |

## Story order (existing content)

Hero → Overview → Included → Pricing → Deliverables → Process → SLA → Add-ons → FAQ → CTA → Form.

## Card policy

Hero wash, lists, process, SLA, FAQ are not cards. The quote form is the grouping card.

## Contracts unchanged

CSRF, honeypot, field names, `/forms/contact.php`, prices, slugs, JSON-LD.
