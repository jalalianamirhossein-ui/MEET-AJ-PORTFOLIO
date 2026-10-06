# Maintenance scripts

Reviewed 2026-10-06. Run scripts from the repository root. PHP tools require PHP 8.4 and Composer dependencies; Python builders require Python 3. Node is optional for documentation/frontend checks. The site needs no frontend build.

| Tools | Purpose |
|---|---|
| `validate-environment.php`, `dump-session-config.php` | Environment/session diagnostics |
| `write-production-env.php` | New environment writer used by deployment setup; preserve existing `.env` and APP_KEY |
| `refresh-project.sh` | Local/deployment refresh; inspect its optional destructive reset branch before use |
| `verify-originals.php` | Historical hash comparison, not a current release regression test |
| `update-article-order.php` | Synchronize editorial order; preserves content and publication dates |
| `install-pbr-article.php` | Preview ping-triggered PBR import; `--apply` backs up and updates local content |
| `update-sql-backup-article.php` | Preview SQL article update; `--apply` saves a revision before changing the row |
| `prepare-enterprise-runbooks.py`, `prepare-english-runbooks.py`, `upgrade-enterprise-articles.py`, `build-bilingual-articles.py`, `article_comparisons.py` | Historical enterprise editorial pipeline; read inventories before rebuilding |
| `verify-enterprise-articles.py` | Enterprise source validation against its archived inputs |
| `check-documentation.cjs` | Check local Markdown file targets; `--write-index` refreshes the complete inventory |

SQL English generator input is in `resources/content/articles/sql-server-automatic-backup-job/english-source.txt`. Article-specific builders and executable examples belong in [content packages](../resources/content/README.md). Bulk pipelines do not automatically acquire newer packages.

```bash
node scripts/check-documentation.cjs
node scripts/check-documentation.cjs --write-index
node --test tests/Frontend/scroll-reveal.test.cjs
```

These checks validate file targets and frontend behavior, not external websites or Markdown heading anchors. For database replacement, preview the import, back up and review CMS edits. Current source/database differences are documented in [project status](../docs/current/PROJECT-STATUS.md).
