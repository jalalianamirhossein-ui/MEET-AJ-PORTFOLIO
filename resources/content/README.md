# Article content packages

Reviewed 2026-10-06. Packages under `articles/{slug}/` keep editorial inputs, generators and runnable examples together. Maintained import HTML lives separately in `resources/legacy/articles/`.

| Package | Ownership |
|---|---|
| [Linux Security Auditor](articles/linux-security-auditor-bash/README.md) | PHP builder, Bash source and validation notes |
| [Ping-triggered PBR](articles/mikrotik-ping-triggered-policy-routing/README.md) | Python builder, RouterOS example and metadata |
| [SQL Server backups](articles/sql-server-automatic-backup-job/README.md) | SQL/PowerShell examples, metadata, PHP builder and English prose input |
| [MikroTik PBR Client](articles/mikrotik-pbr-client/README.md) | Public installer source and private source archive |

Builders change source HTML; imports change database rows. Review CMS edits before applying replacements. Bulk enterprise generators use historical inventories and need explicit extension for newer packages. Publication copies only the selected Bash script and MSI to their existing public URLs; source archives remain outside `public/`.

See [source ownership](../README.md), [directory map](../../docs/current/PROJECT-STRUCTURE.md) and [current verification](../../docs/current/PROJECT-STATUS.md).
