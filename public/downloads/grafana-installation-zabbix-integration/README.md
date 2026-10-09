# Grafana and Zabbix enterprise monitoring package

English / فارسی. Ubuntu Server 24.04 LTS, Grafana OSS stable, existing Zabbix 7.0 LTS.

These are authored configuration templates and valid importable dashboard definitions. They are not fake live exports and contain no metric samples or actual credentials. Refer to [English guide](article.en.md) and [راهنمای فارسی](article.fa.md) for all commands, diagrams, acceptance criteria and official references.

Deployment order / ترتیب استقرار:

1. Verify official versions, DNS, time, APT key and API path / نسخه رسمی، DNS، ساعت، کلید APT و مسیر API.
2. Install Grafana, preserve its unique encryption secret, apply loopback-only config / نصب گرافانا، حفظ Secret یکتا و Config مربوط به Loopback.
3. Bootstrap Nginx certificate, install HTTPS proxy and test renewal / Bootstrap صدور Certificate و Proxy HTTPS و آزمون تمدید.
4. Install signed app, enable it, create read-only Zabbix owner/token / نصب App امضاشده و فعال‌سازی، ساخت صاحب حساب و Token محدود.
5. Fill protected environment locally; apply app/data-source provisioning / تکمیل محیط محدود محلی و Provisioning.
6. Import Linux/Windows JSON, select real discovered items; configure Event Log active item / Import JSON و آیتم واقعی؛ تنظیم آیتم Active مربوط به Event Log.
7. Optional NOC: import trapper template, provision summary host/PSK, configure collector scope and timer, then import NOC JSON / NOC اختیاری: Template، میزبان Summary/PSK، محدوده Collector و Timer سپس JSON.
8. Install backup script/service/timer with mounted storage and encrypted off-site copies / نصب Script و Service و Timer بکاپ با Volume و Copy رمز‌شده Off-site.
9. Complete isolated recovery and [security checklist](security-checklist.md) / بازیابی ایزوله و چک‌لیست امنیت.

Data source UID: zabbix-enterprise. If using another data source, change every datasource reference. Use dashboard-provider.yaml with reviewed JSON under /var/lib/grafana/dashboards. Application selectors are replaced with Item tags on Zabbix 7.0. The interface variable returns full inbound item names. NOC counts require noc-collector.py and zabbix-noc-template.json; Environment scope is independent of narrowed performance filters. No-data/stale values must never be converted to zero.

Runtime acceptance needs a real authorized Zabbix API, live OS items, Grafana rendering, approved notification transport and a restored database. Static checks cannot establish these. Read [restore instructions](restore-runbook.md). Edited credential files and backup archives must never be copied into public downloads or Git.
