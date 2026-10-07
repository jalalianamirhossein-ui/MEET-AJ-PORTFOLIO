# MongoDB Community production runbook

Reviewed 2026-10-07. Editorial source: `scripts/build-mongodb-article.py`. Generated import source: `resources/legacy/articles/mongodb-installation-configuration-production-deployment.html`. Standalone reading copies: `article.en.md` and `article.fa.md`. Both languages use the site's existing localization controls, RTL and unchanged LTR command blocks.

Run the builder with Python, then deploy the sources/assets and run the publication/image-path migrations. The publication migration uses the normal non-overwriting importer and preserves subsequent CMS edits. Following the image organization update, the main banner is in `resources/assets/img/articles/banners/` and the four article diagrams are in `resources/assets/img/articles/content/`, published under the corresponding `/assets/img/articles/` URLs. The image-path migration changes only MongoDB image references and preserves other CMS content.

Version decisions: current stable branch 9.0; released patch 9.0.2 in the official release notes, 9.0.3 marked upcoming. Ubuntu 22.04/24.04 and RHEL-compatible 8/9 use official Community 9.0 repositories. Debian 12 uses supported Community 8.0 because 9.x requires Debian 13. No Enterprise or third-party database repositories are used.

The current MongoDB site hides distribution-specific install steps behind a composable selector. Repository templates were checked in the official MongoDB documentation repository (`mongodb/docs`, `main`, `content/manual/manual/source/includes/deploy/`). The key URL `https://pgp.mongodb.com/server-9.asc` returned HTTP 200. Direct official repository metadata checks from the authoring network returned HTTP 403. This limitation is disclosed in both article languages; live APT/DNF availability and deployment behavior remain target-server checks.

Five images were generated separately with the built-in ImageGen tool. `image-prompts.json` records the complete prompt set; final PNG assets are normalized to the requested 1000×1000 banner and 1920×1080 diagrams without changing the generated design. Do not rerun image generation to rebuild text.

Technical review includes package-user/path differences, localhost-only bootstrap, explicit TLS+SCRAM behavior, CA/SAN requirements, shared keyfile distribution before initiation, X.509 production recommendation, effective cluster roles, no password-in-URI backup commands, full-dump/oplog constraints, isolated restore and version-dependent THP guidance. Linux deployment commands were not executed on this Windows website workstation.

