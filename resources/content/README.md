# Article content packages

Reviewed 2026-10-06. Packages under `articles/{slug}/` keep editorial inputs, generators and runnable examples together. Maintained import HTML lives separately in `resources/legacy/articles/`.

| Package | Ownership |
|---|---|
| [Linux Security Auditor](articles/linux-security-auditor-bash/README.md) | PHP builder, Bash source and validation notes |
| [Ping-triggered PBR](articles/mikrotik-ping-triggered-policy-routing/README.md) | Python builder, RouterOS example and metadata |
| [SQL Server backups](articles/sql-server-automatic-backup-job/README.md) | SQL/PowerShell examples, metadata, PHP builder and English prose input |
| [MikroTik PBR Client](articles/mikrotik-pbr-client/README.md) | Public installer source and private source archive |

Builders change source HTML; imports change database rows. Review CMS edits before applying replacements. `article-technical-content.json` contains reviewed bilingual technical additions compiled by `scripts/article_technical_content.py` into the maintained HTML. The old bulk conversion entry points now use that compiler, preserving modern package content. `article-technical-contracts.json` describes complete reviewed procedure examples for source and rendered-output validation; hashes must be updated only after actual technical review. See [article maintenance](../../docs/current/ARTICLE-STRUCTURE.md) for migration safeguards and the 41-article audit. Publication copies only the selected Bash script and MSI to their existing public URLs; source archives remain outside `public/`.

See [source ownership](../README.md), [directory map](../../docs/current/PROJECT-STRUCTURE.md) and [current verification](../../docs/current/PROJECT-STATUS.md).

Image organization (2026-10-06): [banner, article-body and upload folder guide](../../docs/current/IMAGES.md). Run `node scripts/check-images.cjs` after publishing images.
