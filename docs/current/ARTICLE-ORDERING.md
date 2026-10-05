# Article ordering

The homepage, article library and matching search/tag results share
`Article::inDisplayOrder()` and `config/article-order.php`.

1. Reviewed Enterprise articles follow the `enterprise` list in its exact order:
   MikroTik PBR, Linux Auditor, NetBox, Oxidized, NGINX, Linux security, MikroTik
   ECMP, SQL Server backup, vSphere switching and MikroTik OpenVPN.
2. New articles absent from either configured list appear next, newest first.
3. Existing basic guides follow the `guides` list in its original order.

New slugs never cause an article-set mismatch. Missing configured slugs are
skipped. Drafts and future publications remain subject to existing visibility
rules. Priority does not make private content public. Languages are ordered
independently when numeric `sort_order` values are synchronized.

To place a new Enterprise article first, add its slug at the beginning of the
`enterprise` array. Put a basic guide in `guides` when it should stay below new
articles. Classification is an editorial decision, not guessed from keywords.
Do not list a slug in both arrays. If neither is edited, new articles still work.

After deploying the updated files to an existing server:

```bash
php artisan optimize:clear
php artisan articles:import-legacy --update-existing
php scripts/update-article-order.php
php artisan optimize
```

Import now synchronizes numeric order automatically; the standalone script is
useful after changing editorial priority or adding articles through the CMS.
It updates **only `sort_order`**, preserves publication dates/content/SEO and
`updated_at`, and is safe to repeat. It verifies unrelated fields inside a
transaction. The previous script changed dates randomly and rejected new slugs;
both behaviors have been removed. Dates already changed by that old script can
only be restored from known publication records or a backup.

Configured editorial priority controls public placement. Publication dates sort
unclassified articles; `sort_order` and ID resolve remaining ties. The admin's
numeric ordering is synchronized by the script and does not override a configured
priority. Inspect the printed list after synchronization; no fixed article count
is required.
