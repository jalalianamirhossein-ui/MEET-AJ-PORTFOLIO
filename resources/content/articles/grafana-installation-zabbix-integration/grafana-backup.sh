#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
[[ $EUID -eq 0 ]] || { echo 'Run as root' >&2; exit 1; }
: "${BACKUP_ROOT:=/srv/grafana-backups}"
: "${GRAFANA_DB_TYPE:=sqlite3}"
[[ $BACKUP_ROOT == /srv/grafana-backups ]] || { echo 'Review script before changing backup root' >&2; exit 1; }
mountpoint -q "$BACKUP_ROOT" || { echo 'Backup volume is not mounted' >&2; exit 1; }
install -d -m 0700 /run/grafana-backup
exec 9>/run/grafana-backup/lock
flock -n 9 || { echo 'Backup already running' >&2; exit 1; }
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=$(mktemp -d "$BACKUP_ROOT/.partial.XXXXXX")
stopped=0
cleanup() {
    rc=$?
    trap - EXIT
    if ((stopped)); then
        if ! systemctl start grafana-server; then echo 'CRITICAL: Grafana restart failed' >&2; rc=1; fi
    fi
    if ((rc)); then echo "FAILED; incomplete backup retained at $work" >&2; fi
    exit "$rc"
}
trap cleanup EXIT
systemctl is-active --quiet grafana-server || { echo 'Grafana must be healthy before backup' >&2; exit 1; }
needed=$(du -sk /etc/grafana /var/lib/grafana | awk '{sum+=$1} END {print sum*2+1048576}')
available=$(df -Pk "$BACKUP_ROOT" | awk 'NR==2 {print $4}')
((available > needed)) || { echo 'Insufficient backup capacity' >&2; exit 1; }
# Short maintenance window keeps database, provisioning and plugin files aligned.
stopped=1
systemctl stop grafana-server
if [[ $GRAFANA_DB_TYPE == sqlite3 ]]; then
    sqlite3 /var/lib/grafana/grafana.db ".backup '$work/grafana.db'"
    [[ $(sqlite3 "$work/grafana.db" 'PRAGMA integrity_check;') == ok ]]
elif [[ $GRAFANA_DB_TYPE == postgres ]]; then
    pg_dump --format=custom --no-owner --no-acl --file="$work/grafana.pgdump"
    pg_restore --file=/dev/null "$work/grafana.pgdump"
else
    echo 'Supported database types: sqlite3 or postgres' >&2; exit 1
fi
dpkg-query -W -f='${Package} ${Version}\n' grafana > "$work/grafana-version.txt"
find /var/lib/grafana/plugins -name plugin.json -exec python3 -c \
    'import json,sys; p=json.load(open(sys.argv[1])); print(p["id"],p.get("info",{}).get("version","unknown"))' {} \; > "$work/plugin-inventory.txt"
paths=(etc/grafana var/lib/grafana/plugins)
for path in etc/default/grafana-server etc/systemd/system/grafana-server.service.d var/lib/grafana/dashboards etc/nginx etc/letsencrypt; do
    [[ ! -e /$path ]] || paths+=("$path")
done
# This archive contains credentials, secret keys and TLS private keys: root-only.
tar -C / -czf "$work/config-and-plugins.tar.gz" "${paths[@]}"
tar -tzf "$work/config-and-plugins.tar.gz" > /dev/null
systemctl start grafana-server
stopped=0
systemctl is-active --quiet grafana-server
(cd "$work"; sha256sum ./* > SHA256SUMS; sha256sum -c SHA256SUMS)
final="$BACKUP_ROOT/$stamp"
[[ ! -e $final ]] || { echo 'Backup timestamp collision' >&2; exit 1; }
mv -- "$work" "$final"
work=$final
if [[ -n ${RESTIC_REPOSITORY:-} ]]; then
    : "${RESTIC_PASSWORD_FILE:?Configure protected restic password file}"
    restic backup --tag grafana "$final"
fi
echo "Backup complete: $final"
# Retention intentionally separate: expire only after confirmed off-site recovery tests.
