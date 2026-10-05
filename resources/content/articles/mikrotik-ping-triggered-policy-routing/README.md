# MikroTik Ping-Triggered Policy Routing

Maintained Persian and native English RouterOS v7 runbook. `build.py` generates
the bilingual legacy HTML, metadata and shared `complete-example.rsc`.
The site uses its existing language switch and one canonical article URL.

```powershell
python resources/content/articles/mikrotik-ping-triggered-policy-routing/build.py
php scripts/install-pbr-article.php
php scripts/install-pbr-article.php --apply
php artisan view:clear
```

The installer backs up local content before importing only this article. The
homepage, library and search follow `config/article-order.php`, which currently
places it first and the former featured Linux article second overall. Adding new
articles does not require changing this installer. The importer preserves
publication date and status on updates. Review CMS edits before re-importing.
No remote server is changed by these commands.

The `.rsc` is an additive educational example, not an idempotent installer or a
replacement firewall. Read prerequisites, stable FastTrack exclusions and rule
placement before applying. It assumes existing LAN/main routing and a working
example L2TP tunnel; it contains no credentials, live network values or service
domains. Optional NAT must match the chosen network architecture.

Technical references were checked against official MikroTik documentation on
2026-10-05. Website tests verify localization, command preservation, FAQ/SEO and
ordering; no RouterOS device is connected, so runtime execution, tunnel health,
permissions and throughput must be validated on the target v7 release in a lab.
Do not feed this new package to the older all-article regeneration scripts unless
their runbook inventories are extended to include it.
