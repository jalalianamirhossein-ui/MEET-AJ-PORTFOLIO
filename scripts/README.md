# Maintenance scripts

Article source ownership and safe validation commands: [article structure maintenance](../docs/current/ARTICLE-STRUCTURE.md).

Reviewed 2026-10-08. Run scripts from the repository root. PHP tools require PHP 8.4 and Composer dependencies; Python builders require Python 3. Node is optional for documentation/frontend checks. The site needs no frontend build.

| Tools | Purpose |
|---|---|
| `validate-environment.php`, `dump-session-config.php` | Environment/session diagnostics |
| `write-production-env.php` | New environment writer used by deployment setup; preserve existing `.env` and APP_KEY |
| `refresh-project.sh` | Local/deployment refresh; inspect its optional destructive reset branch before use |
| `verify-originals.php` | Historical hash comparison, not a current release regression test |
| `update-article-order.php` | Synchronize editorial order; preserves content and publication dates |
| `install-pbr-article.php` | Preview ping-triggered PBR import; `--apply` backs up and updates local content |
| `update-sql-backup-article.php` | Preview SQL article update; `--apply` saves a revision before changing the row |
| `audit-article-content.php` | Review every stored article in both locales for duplicate headings/IDs/references, broken contents links, empty sections, unanswered FAQs and prose that does not match its requested language; writes a JSON report in `storage/app` and exits nonzero for structural or localization problems |
| `audit-article-structure.py`, `article_structure.py` | Audit/repair all source articles using the reviewed section policy; check languages, review labels, IDs, anchors and hierarchy without a database |
| `check-article-regeneration.py` | Run article builders and the SQL loader twice in an isolated copy; compare maintained headings/code/image URLs and check normalization/regeneration stability |
| `check-article-browser.cjs` | Check exported final Blade pages for all 41 reviewed articles in EN/FA desktop/mobile Chrome, including visible technical examples and actual copy handlers; requires Playwright and local preview exports; writes `storage/app/enterprise-browser.json` |
| `article_technical_content.py` | Compile reviewed bilingual technical inputs into existing authoritative sections; also check complete procedure artifacts against reviewed contracts |
| `upgrade-enterprise-articles.py`, `build-bilingual-articles.py` | Compatibility entry points for the maintained technical compiler; the archived bulk replacement/positional translation pipeline is retired |
| `prepare-enterprise-runbooks.py`, `prepare-english-runbooks.py`, `article_comparisons.py` | Historical enterprise editorial material, not current article generation inputs |
| `verify-enterprise-articles.py` | Enterprise source validation against its archived inputs |
| `check-documentation.cjs` | Check local Markdown file targets; `--write-index` refreshes the complete inventory |
| `check-repository-security.cjs` | Check tracked/nonignored files, case collisions and high-confidence secret patterns; `--history` also scans available Git history without printing matches |

SQL English generator input is in `resources/content/articles/sql-server-automatic-backup-job/english-source.txt`. Article-specific builders and executable examples belong in [content packages](../resources/content/README.md). Bulk pipelines do not automatically acquire newer packages.

```bash
node scripts/check-documentation.cjs
node scripts/check-documentation.cjs --write-index
node --test tests/Frontend/scroll-reveal.test.cjs tests/Frontend/service-worker-security.test.cjs tests/Frontend/swiper-security.test.cjs
node scripts/check-repository-security.cjs --history
```

These checks validate file targets and frontend behavior, not external websites or Markdown heading anchors. For database replacement, preview the import, back up and review CMS edits. Current source/database differences are documented in [project status](../docs/current/PROJECT-STATUS.md).

Image organization (2026-10-06): [banner, article-body and upload folder guide](../docs/current/IMAGES.md). Run `node scripts/check-images.cjs` after publishing images.
