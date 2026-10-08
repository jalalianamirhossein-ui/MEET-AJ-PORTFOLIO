# Vendored frontend dependencies

Reviewed 2026-10-08. These files are copied by `site:publish-assets`; there is no npm manifest or build. Composer audit does not cover this directory. Version labels below are taken from distributed headers/source, and unknown labels are stated explicitly.

| Directory | Version evidence | Role |
|---|---|---|
| `bootstrap` | 5.3.3 | Layout and UI components |
| `bootstrap-icons` | 1.11.3 | Icons |
| `swiper` | **12.1.2** | Testimonials slider |
| `imagesloaded` | 5.0.0 | Image-aware layout |
| `isotope-layout` | 3.0.6 | Article filtering/layout |
| `waypoints` | 4.0.1 | Scroll hooks |
| `glightbox` | 3.3.0 (`_version` in source) | Lightbox |
| `purecounter` | 1.5.0 (source header) | Counters |
| `aos`, `typed.js`, `php-email-form` | No reliable release label established in this review | Animation, typing effect, legacy form helper |

## Swiper security update

11.1.9 was in the affected range of [GHSA-hmx5-qpq5-p643](https://github.com/nolimits4web/swiper/security/advisories/GHSA-hmx5-qpq5-p643), CWE-1321. Upstream rates this advisory Critical. Application-specific attacker control of slider configuration was not demonstrated. Version 12.1.2 is the published patched release; crossing one major version was necessary to use that fix without maintaining a private security fork.

Source: official npm distribution `swiper@12.1.2`, obtained with `npm pack --ignore-scripts`. Archive SHA-512 integrity was verified before extraction:

```text
sha512-4gILrI3vXZqoZh71I1PALqukCFgk+gpOwe1tOvz5uE9kHtl2gTDzmYflYCwWvR4LOvCrJi6UEEU+gnuW5BtkgQ==
```

The distributed bundle JS, CSS, source map and MIT license are kept together. Preserve these public paths. `main.js` sets `navigation.addIcons=false` to retain existing Bootstrap icons. Isolated JS regression checks modified Array.prototype behavior; headless Chrome fixture tests verified EN/FA initialization and desktop/mobile layouts.

Before any future vendor update: identify its upstream release, review advisories and license, verify archive integrity, replace matching JS/CSS/maps together, publish assets, and test the consuming feature. Preserve a dependency/version inventory; unknown older packages need a broader provenance/advisory review. A clean Composer advisory result is not evidence that all frontend dependencies are safe.
