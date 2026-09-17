> **HISTORICAL / SUPERSEDED.**
> **Original date:** 2026-09-17 (before the master design audit of the same day).
> **Original purpose:** QA of the service detail template at overlay `v=1310`.
> **Superseded by:** [../qa/VISUAL-QA.md](../qa/VISUAL-QA.md) at overlay `v=1314`, and [../current/SERVICES.md](../current/SERVICES.md).
> Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Service detail UI QA — Meet AJ

**Date:** 2026-09-17  
**Template:** `resources/views/services/show.blade.php`  
**Overlay:** `visual-upgrade.css?v=1310` (loaded **after** `lang-toggle.css`)  
**URLs:** canonical `/services/{slug}` only. Static `resources/views/services/{slug}.blade.php` is unused.

## Shared landing story (existing CMS content)

SERVICE HERO → OVERVIEW → WHAT YOU GET → PROCESS → PRICING → SLA → ADD-ONS → FAQ → REQUEST SERVICE

Not an Article layout. Same tokens: H2 bar, `#2563eb`, 12px buttons, `#f4f7fb` canvas, 56rem reading width, 6.5rem LTR inset for the floating language control.

Quote `#contactForm` is `hidden` until **Request a Quote**. Fields: Name, Email, Phone, Service, Subject, Project Details. CSRF + honeypot preserved.

FAQ: accordion buttons, `aria-expanded` / `aria-hidden` on answers.

## Per service (rendered)

| Slug | Hero screenshot | Price shown | Notes |
|------|-----------------|-------------|--------|
| network-design | 1280 + 375 | AED 4,900 | Process/SLA/FAQ/form also rendered |
| system-administration | 1280 | AED 3,900 | Same chrome |
| devops-automation | 1280 | AED 6,900 | Five process steps |
| monitoring-security | 1280 | AED 4,200 | Same chrome |
| virtualization-solutions | 1280 | AED 5,900 | Same chrome |
| technical-consulting | 1280 | AED 2,500 | Same chrome |

No invented prices. `/services` index route does not exist (catalog is `/#services`) — 404 by design.

## Issues this pass

1. Stylesheet order put overlay before language CSS — **fixed**.
2. EN switcher nearly overlapped process `01` — **inset increased**.
3. Screenshot canvases are narrower than emulated 1280; CDP overflow 0 is the overflow source of truth.
4. 375: Back + EN share the top edge; title stacks; CTAs full width — **PASS**.

## Forms

Request a Quote reveals labeled fields; Service select pre-filled; Subject pre-filled. Submit remains `Submit Request`. Backend contract unchanged.
