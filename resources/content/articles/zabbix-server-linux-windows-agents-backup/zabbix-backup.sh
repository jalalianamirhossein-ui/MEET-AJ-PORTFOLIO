#!/usr/bin/env bash
# Ubuntu 24.04 / PostgreSQL 16. Run as root through the supplied systemd unit.
# EnvironmentFile supplies settings; this script never sources executable config.
set -Eeuo pipefail
umask 077
export PATH=/usr/sbin:/usr/bin:/sbin:/bin
BACKUP_ROOT=${BACKUP_ROOT:-/backup/zabbix}
RETENTION_DAYS=${RETENTION_DAYS:-14}
MIN_FREE_MB=${MIN_FREE_MB:-4096}
SPACE_FACTOR=${SPACE_FACTOR:-2}
PGDATABASE=${PGDATABASE:-zabbix}
PGROLE=${PGROLE:-zabbix}
OFFSITE_REQUIRED=${OFFSITE_REQUIRED:-yes}
FAILURE_HOOK=${FAILURE_HOOK:-/usr/local/sbin/zabbix-backup-notify}
stage=''
log_file=''
log() { printf '%s %s\n' "$(date -u +%FT%TZ)" "$*"; }
finish() {
    local rc=$?
    trap - EXIT
    if (( rc != 0 )); then
        log "FAILED exit=$rc; incomplete backups retained for diagnosis; retention skipped"
        if [[ -n "$log_file" && -x "$FAILURE_HOOK" ]]; then
            timeout 30 "$FAILURE_HOOK" "$rc" "$log_file" || log 'Failure notification hook failed'
        fi
    fi
    exit "$rc"
}
trap finish EXIT
[[ $EUID -eq 0 ]] || { log 'Root is required'; exit 77; }
[[ "$BACKUP_ROOT" =~ ^/backup/[a-zA-Z0-9/_-]+$ && "$BACKUP_ROOT" != *'..'* ]] || exit 64
for number in "$RETENTION_DAYS" "$MIN_FREE_MB" "$SPACE_FACTOR"; do
    [[ "$number" =~ ^[1-9][0-9]*$ ]] || exit 64
done
[[ "$PGDATABASE" =~ ^[a-zA-Z0-9_]+$ && "$PGROLE" =~ ^[a-zA-Z0-9_]+$ ]] || exit 64
[[ "$OFFSITE_REQUIRED" == yes || "$OFFSITE_REQUIRED" == no ]] || exit 64
for command in flock runuser pg_dump pg_restore psql tar gzip sha256sum df du find timeout realpath mountpoint; do
    command -v "$command" >/dev/null || { log "Missing command: $command"; exit 69; }
done
install -d -o root -g root -m 0700 /var/lib/zabbix-backup
exec 9>/var/lib/zabbix-backup/backup.lock
flock -n 9 || { log 'Another backup is running'; exit 75; }
# A dedicated mounted backup volume prevents filling the OS root filesystem.
mountpoint -q /backup || { log '/backup must be a mounted backup volume'; exit 73; }
install -d -m 0700 "$BACKUP_ROOT" /var/log/zabbix-backup
[[ "$(realpath -e "$BACKUP_ROOT")" == "$BACKUP_ROOT" ]] || exit 64
[[ "$(stat -c %u "$BACKUP_ROOT")" == 0 && "$(stat -c %a "$BACKUP_ROOT")" == 700 ]] || exit 77
stamp=$(date -u +%Y%m%dT%H%M%SZ)
log_file="/var/log/zabbix-backup/$stamp.log"
exec > >(tee -a "$log_file") 2>&1
log 'Backup started'
if [[ "$OFFSITE_REQUIRED" == yes ]]; then
    command -v restic >/dev/null || exit 69
    : "${RESTIC_REPOSITORY:?Set an encrypted off-site restic repository}"
    : "${RESTIC_PASSWORD_FILE:?Set a protected restic password file}"
    [[ -f "$RESTIC_PASSWORD_FILE" && ! -L "$RESTIC_PASSWORD_FILE" ]] || exit 77
    [[ "$(stat -c %u "$RESTIC_PASSWORD_FILE")" == 0 && "$(stat -c %a "$RESTIC_PASSWORD_FILE")" == 600 ]] || exit 77
fi
# These directories contain secrets. Do not publish this archive or put it in Git.
paths=(etc/zabbix etc/nginx etc/php/8.3/fpm etc/postgresql/16/main etc/ssl/zabbix
       etc/zabbix-backup etc/systemd/system/zabbix-backup.service
       etc/systemd/system/zabbix-backup.timer etc/systemd/system/zabbix-backup-failure.service
       usr/local/sbin/zabbix-backup.sh)
for optional in etc/letsencrypt usr/lib/zabbix/alertscripts usr/lib/zabbix/externalscripts usr/local/lib/zabbix usr/local/sbin/zabbix-backup-notify etc/logrotate.d/zabbix-backup; do
    [[ ! -e "/$optional" ]] || paths+=("$optional")
done
for path in "${paths[@]}"; do [[ -e "/$path" ]] || { log "Missing required path: /$path"; exit 66; }; done
db_bytes=$(runuser -u postgres -- psql -X -At -v ON_ERROR_STOP=1 -d "$PGDATABASE" -c 'SELECT pg_database_size(current_database())')
[[ "$db_bytes" =~ ^[0-9]+$ ]] || exit 65
config_bytes=$(du -scb "${paths[@]/#//}" | tail -n 1 | cut -f 1)
required_mb=$(( (db_bytes * SPACE_FACTOR + config_bytes) / 1048576 + MIN_FREE_MB ))
free_mb=$(df -Pm "$BACKUP_ROOT" | awk 'NR==2 {print $4}')
(( free_mb >= required_mb )) || { log "Insufficient space: free=${free_mb}MiB required=${required_mb}MiB"; exit 73; }
stage=$(mktemp -d "$BACKUP_ROOT/.incomplete-$stamp-XXXXXX")
# The database role owns the schema but has no superuser/createdb/createrole rights.
# pg_dump's MVCC snapshot is consistent while ordinary monitoring writes continue.
runuser -u postgres -- pg_dump -h /var/run/postgresql --role="$PGROLE" \
    -d "$PGDATABASE" --format=custom --compress=6 --no-owner --no-acl > "$stage/zabbix.dump"
# tar exits nonzero if selected files change. Schedule configuration changes separately.
tar --acls --xattrs --numeric-owner -czf "$stage/configuration.tar.gz" -C / "${paths[@]}"
dpkg-query -W -f='${Package}\t${Version}\n' 'zabbix*' 'postgresql*' 'nginx*' 'php8.3*' > "$stage/packages.tsv"
pg_restore --list "$stage/zabbix.dump" > "$stage/database-toc.txt"
# Decode all archive data, not just its table of contents; still not a restore test.
pg_restore --file=/dev/null "$stage/zabbix.dump"
gzip -t "$stage/configuration.tar.gz"
tar -tzf "$stage/configuration.tar.gz" > "$stage/configuration-files.txt"
[[ -s "$stage/zabbix.dump" && -s "$stage/configuration.tar.gz" ]] || exit 65
(
    cd "$stage"
    sha256sum zabbix.dump configuration.tar.gz packages.tsv database-toc.txt configuration-files.txt > SHA256SUMS
    sha256sum --check SHA256SUMS
)
date -u +%FT%TZ > "$stage/VERIFIED"
final="$BACKUP_ROOT/$stamp"
[[ ! -e "$final" ]] || exit 73
mv -- "$stage" "$final"
stage=''
if [[ "$OFFSITE_REQUIRED" == yes ]]; then
    # Configure transport authentication separately (SSH agent or restic credentials).
    # Bounded operation fails the job instead of leaving retention running on timeout.
    timeout 6h restic backup --tag zabbix -- "$final"
    timeout 1h restic check
    date -u +%FT%TZ > "$final/OFFSITE_OK"
fi
date -u +%FT%TZ > "$final/SUCCESS"
# Delete only completed, real timestamp directories AFTER a verified successful backup.
# Exact cutoff uses directory names; altered directory mtimes cannot shorten retention.
cutoff=$(date -u -d "$RETENTION_DAYS days ago" +%Y%m%dT%H%M%SZ)
while IFS= read -r -d '' old; do
    name=${old##*/}
    [[ "$name" =~ ^[0-9]{8}T[0-9]{6}Z$ && "$name" < "$cutoff" ]] || continue
    [[ "$old" != "$final" && -f "$old/SUCCESS" && ! -L "$old" ]] || continue
    [[ "$(realpath -e "$old")" == "$BACKUP_ROOT/$name" ]] || exit 64
    rm -rf --one-file-system -- "$old"
done < <(find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -print0)
date -u +%FT%TZ > "$BACKUP_ROOT/last-success.txt"
# Log retention is independent of backup retention and runs only on success.
find /var/log/zabbix-backup -maxdepth 1 -type f -name '*.log' -mtime +90 -delete
log "SUCCESS backup=$final offsite=$OFFSITE_REQUIRED"
