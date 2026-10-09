#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
export ORACLE_BASE=/opt/oracle
export ORACLE_HOME=/opt/oracle/product/26ai/dbhome_1
export ORACLE_SID=ORCLCDB
export PATH="$ORACLE_HOME/bin:/usr/sbin:/usr/bin:/sbin:/bin"
export TNS_ADMIN="$ORACLE_HOME/network/admin"
export NLS_LANG=AMERICAN_AMERICA.AL32UTF8
base=/backup/oracle
mode=${1:-}
encryption=${RMAN_ENCRYPTION:-ON}
minimum_kib=${MIN_FREE_KIB:-10485760}
allow_cleanup=${ALLOW_CLEANUP:-no}
case "$mode" in level0|level1|archivelog|retention) ;; *) echo 'Usage: oracle-rman-backup.sh level0|level1|archivelog|retention' >&2; exit 64;; esac
case "$encryption" in ON|OFF) ;; *) echo 'RMAN_ENCRYPTION must be ON or OFF' >&2; exit 64;; esac
[[ "$minimum_kib" =~ ^[0-9]+$ ]] || exit 64
[[ $(id -un) == oracle ]] || { echo 'Run as oracle' >&2; exit 77; }
mountpoint -q "$base" || { echo 'Backup mount missing' >&2; exit 73; }
[[ -x "$ORACLE_HOME/bin/rman" && -d "$base/pieces" && -d "$base/logs" ]] || exit 73
exec 9>"$base/.rman.lock"
flock -n 9 || { echo 'Another RMAN job is running' >&2; exit 75; }
available_kib=$(df -Pk "$base" | awk 'NR==2 {print $4}')
[[ "$available_kib" =~ ^[0-9]+$ ]] || exit 73
if [[ "$mode" != retention && "$available_kib" -lt "$minimum_kib" ]]; then
  echo 'Backup free-space threshold failed' >&2; exit 73
fi
if [[ "$mode" == retention && "$allow_cleanup" != yes ]]; then
  echo 'Cleanup requires verified off-host recovery chain and ALLOW_CLEANUP=yes' >&2; exit 78
fi
cmd=$(mktemp "$base/logs/command.XXXXXX")
runlog=$(mktemp "$base/logs/rman.XXXXXX")
trap 'rm -f -- "$cmd" "$runlog"' EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
{
  printf 'SET ECHO OFF;\n'
  if [[ "$mode" != retention ]]; then
    printf 'SET ENCRYPTION %s;\n' "$encryption"
  fi
  case "$mode" in
    level0|level1)
      level=${mode#level}
      printf "BACKUP INCREMENTAL LEVEL %s DATABASE FORMAT '%s/pieces/%%d_%%T_%%U.bkp' TAG 'AJ_L%s' PLUS ARCHIVELOG FORMAT '%s/pieces/%%d_%%T_%%U.arc';\n" "$level" "$base" "$level" "$base"
      printf "BACKUP CURRENT CONTROLFILE FORMAT '%s/pieces/%%d_%%T_%%U.ctl';\n" "$base"
      printf "BACKUP SPFILE FORMAT '%s/pieces/%%d_%%T_%%U.spf';\n" "$base"
      printf "RESTORE DATABASE VALIDATE;\nRESTORE ARCHIVELOG FROM TIME 'SYSDATE-1' VALIDATE;\n"
      ;;
    archivelog)
      printf 'SQL "ALTER SYSTEM ARCHIVE LOG CURRENT";\n'
      printf "BACKUP ARCHIVELOG ALL NOT BACKED UP 2 TIMES TO DEVICE TYPE DISK FORMAT '%s/pieces/%%d_%%T_%%U.arc' TAG 'AJ_ARCH';\n" "$base"
      ;;
    retention)
      printf 'CROSSCHECK BACKUP;\nREPORT OBSOLETE;\nDELETE NOPROMPT OBSOLETE;\n'
      ;;
  esac
  printf 'EXIT;\n'
} >"$cmd"
rc=0
"$ORACLE_HOME/bin/rman" target / cmdfile="$cmd" log="$runlog" || rc=$?
{
  printf '\n===== %s mode=%s rc=%s =====\n' "$(date --iso-8601=seconds)" "$mode" "$rc"
  cat "$runlog"
} >>"$base/logs/backup.log"
# RMAN warnings require attention too; fail conservatively for these diagnostics.
if (( rc != 0 )) || grep -Eq 'RMAN-[0-9]{5}|ORA-[0-9]{5}' "$runlog"; then
  logger -p user.err -t oracle-rman "FAILED mode=$mode sid=$ORACLE_SID rc=$rc" || true
  echo "RMAN job failed; inspect $base/logs/backup.log" >&2
  exit 1
fi
logger -p user.notice -t oracle-rman "SUCCESS mode=$mode sid=$ORACLE_SID" || true
printf 'RMAN job completed: %s\n' "$mode"
