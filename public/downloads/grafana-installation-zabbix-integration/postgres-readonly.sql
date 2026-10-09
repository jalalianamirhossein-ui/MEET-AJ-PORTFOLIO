-- Execute in the Zabbix PostgreSQL database as an administrator.
-- Set a unique password interactively with psql: \password grafana_zabbix_ro
CREATE ROLE grafana_zabbix_ro LOGIN;
GRANT CONNECT ON DATABASE zabbix TO grafana_zabbix_ro;
GRANT USAGE ON SCHEMA public TO grafana_zabbix_ro;
GRANT SELECT ON public.history, public.history_uint, public.trends, public.trends_uint TO grafana_zabbix_ro;
ALTER ROLE grafana_zabbix_ro SET default_transaction_read_only = on;
ALTER ROLE grafana_zabbix_ro SET statement_timeout = '60s';
-- Narrow pg_hba.conf entry (trusted server cert required):
-- hostssl zabbix grafana_zabbix_ro 192.0.2.20/32 scram-sha-256
