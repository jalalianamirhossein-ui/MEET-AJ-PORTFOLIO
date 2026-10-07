#!/usr/bin/env bash
set -euo pipefail
umask 077
if sudo test -e /etc/redis/users.acl; then
  echo "Existing ACL file: back up and review it before editing." >&2
  exit 1
fi
acl_tmp=$(mktemp)
trap 'rm -f "$acl_tmp"; unset acl_secret' EXIT
printf 'user default off resetpass resetkeys resetchannels -@all\n' > "$acl_tmp"
for acl_user in admin app repl monitor; do
  read -r -s -p "${acl_user} secret (64+ hex characters): " acl_secret
  printf '\n'
  [[ "$acl_secret" =~ ^[a-fA-F0-9]{64,}$ ]] || { echo "Invalid secret" >&2; exit 1; }
  acl_hash=$(printf '%s' "$acl_secret" | sha256sum | cut -d ' ' -f1)
  case "$acl_user" in
    admin) acl_rules='~* resetchannels +@all' ;;
    app) acl_rules='~cache:* ~session:* resetchannels -@all +ping +hello +select +client|setname +client|setinfo +acl|whoami +get +set +mget +mset +del +unlink +exists +expire +pexpire +ttl +pttl +incr +incrby +decr +decrby' ;;
    repl) acl_rules='resetkeys resetchannels -@all +ping +replconf +psync' ;;
    monitor) acl_rules='resetkeys resetchannels -@all +ping +info +role +slowlog|get +latency|latest +acl|whoami' ;;
  esac
  printf 'user %s on #%s %s\n' "$acl_user" "$acl_hash" "$acl_rules" >> "$acl_tmp"
  unset acl_secret acl_hash
done
sudo install -o root -g redis -m 640 "$acl_tmp" /etc/redis/users.acl
echo "ACL file installed; secrets remain in your secret manager."
