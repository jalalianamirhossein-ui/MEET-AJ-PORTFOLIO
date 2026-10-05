#!/usr/bin/env bash
# Linux Security Auditor. Bash 4.4+, GNU/Linux. No remediation, installs or network probes.
# Local posture assessment; not CIS certification, vulnerability scan or incident clearance.
set -Eeuo pipefail
ORIGINAL_LOCALE=${LC_ALL:-${LC_CTYPE:-${LANG:-C}}}
export PATH=/usr/sbin:/usr/bin:/sbin:/bin LC_ALL=C
export SYSTEMD_PAGER=cat SYSTEMD_COLORS=0 PAGER=cat
umask 077
VERSION=2.0.0
FULL=0 DEEP=0 DEMO=0 COLOR=auto OUT='' SSH_CONTEXT='' SSH_CONFIG=''
SCAN_SECONDS=30 CMD_SECONDS=12 TMP='' START=0
HOST='' OS='' KERNEL='' AUDIT_TIME='' IP_INFO='unavailable' ENVIRONMENT='unknown'
SCORE=0 RAW_SCORE=0 COVERAGE=0 CAP=100 VERDICT='' TOTAL=0
DISK_USE='' INODE_USE='' MEMORY_USE=''
declare -a IDS=() CATS=() STATES=() SEVS=() WEIGHTS=() TITLES=() EVIDENCES=() FIXES=() REFS=()
declare -a CAT_ORDER=(Authentication SSH Patching Firewall Network Filesystem Persistence Kernel Logging Isolation Recovery)
declare -A CAT_WEIGHT=([Authentication]=14 [SSH]=12 [Patching]=14 [Firewall]=12 [Network]=8 [Filesystem]=10 [Persistence]=8 [Kernel]=7 [Logging]=5 [Isolation]=5 [Recovery]=5)
declare -A CAT_DEN=() CAT_EARN=() CAT_KNOWN=() CAT_SCORE=() CAT_COVER=() COUNTS=()
RESET='' BOLD='' RED='' GREEN='' YELLOW='' CYAN='' GRAY='' ORANGE=''

die() { printf 'Error: %s\n' "$*" >&2; exit 3; }
have() { command -v "$1" >/dev/null 2>&1; }
utf8_safe() {
  if have iconv; then iconv -f UTF-8 -t UTF-8 -c 2>/dev/null || true
  else tr -cd '\011\012\015\040-\176'; fi
}
clean() {
  local s=$1
  s=${s//$'\n'/ }; s=${s//$'\r'/ }; s=${s//$'\t'/ }
  while [[ $s =~ [[:cntrl:]] ]]; do s=${s//"${BASH_REMATCH[0]}"/}; done
  if (( ${#s}>1400 )); then printf '%s' "${s:0:1400}" | utf8_safe; else printf '%s' "$s"; fi
}
run() { timeout --signal=TERM --kill-after=2s "${CMD_SECONDS}s" "$@"; }
snapshot() {
  local name=$1; shift
  if ! have "$1"; then : > "$TMP/$name"; printf '127' > "$TMP/$name.rc"; return; fi
  local rc=0
  run "$@" > "$TMP/$name" 2> "$TMP/$name.err" || rc=$?
  printf '%s' "$rc" > "$TMP/$name.rc"
}
ok() { [[ -f $TMP/$1.rc && $(<"$TMP/$1.rc") == 0 ]]; }
snippet() { head -n "${2:-6}" "$TMP/$1" 2>/dev/null | cut -c1-240; }
inventory_text() {
  # Preserve line breaks, remove terminal controls, bound total bytes and lines.
  safe_inventory "$1" | tr -d '\000-\010\013\014\016-\037\177' | head -c 48000 | utf8_safe
}
unknown() { emit "$1" "$2" UNKNOWN "${3:-MEDIUM}" "${4:-1}" "$5" "${6:-Tool missing, access denied or command timed out}" "$7"; }
value_check() {
  local id=$1 cat=$2 title=$3 got=$4 expected=$5 sev=$6 fix=$7 weight=${8:-1}
  if [[ -z $got ]]; then unknown "$id" "$cat" "$sev" "$weight" "$title" 'Value unavailable' "$fix"
  elif [[ $got == "$expected" ]]; then emit "$id" "$cat" PASS "$sev" "$weight" "$title" "Observed: $got; expected: $expected" "$fix"
  else emit "$id" "$cat" FAIL "$sev" "$weight" "$title" "Observed: $got; expected: $expected" "$fix"; fi
}
count_check() {
  local id=$1 cat=$2 title=$3 number=$4 sev=$5 evidence=$6 fix=$7 weight=${8:-1}
  [[ $number =~ ^[0-9]+$ ]] || { unknown "$id" "$cat" "$sev" "$weight" "$title" 'Count unavailable' "$fix"; return; }
  if (( number == 0 )); then emit "$id" "$cat" PASS "$sev" "$weight" "$title" 'No matching items in the stated scope' "$fix"
  else emit "$id" "$cat" FAIL "$sev" "$weight" "$title" "Count=$number; $evidence" "$fix"; fi
}
perm_check() {
  local id=$1 cat=$2 path=$3 mask=$4 sev=$5 weight=${6:-1} title=${7:-"Ownership and mode: $3"}
  local info mode uid bits group owner_name group_name
  if [[ ! -e $path && $EUID -ne 0 && ( $path == /root/* || $path == /etc/shadow* || $path == /etc/gshadow* ) ]]; then unknown "$id" "$cat" "$sev" "$weight" "$title" 'Requires root privileges to establish path state' 'Rerun as root'; return; fi
  if [[ ! -e $path ]]; then emit "$id" "$cat" NA "$sev" "$weight" "$title" 'Path absent' 'Review if this path should exist'; return; fi
  info=$(run stat -Lc '%a %u %g %U %G' -- "$path" 2>/dev/null) || { unknown "$id" "$cat" "$sev" "$weight" "$title" 'stat failed' 'Inspect path permissions'; return; }
  read -r mode uid group owner_name group_name <<< "$info"; bits=$((8#$mode))
  if (( (bits & mask) != 0 || uid != 0 )); then emit "$id" "$cat" FAIL "$sev" "$weight" "$title" "mode=$mode uid=$uid gid=$group owner=$owner_name group=$group_name" 'Restore trusted ownership; restrict access using distro defaults and service requirements'
  else emit "$id" "$cat" PASS "$sev" "$weight" "$title" "mode=$mode uid=$uid gid=$group owner=$owner_name group=$group_name" 'Maintain distro-specific secure permissions'; fi
}
progress() { [[ -t 2 ]] && printf '\r\033[2K%sChecking %-18s%s' "$CYAN" "$1" "$RESET" >&2; return 0; }

audit_identity() {
  progress 'System identity'
  HOST=$(clean "$(hostname 2>/dev/null || printf unknown)"); KERNEL=$(clean "$(uname -r)")
  OS=$(awk -F= '$1=="PRETTY_NAME" {gsub(/^"|"$/,"",$2); print $2}' /etc/os-release 2>/dev/null)
  OS=$(clean "${OS:-Unknown Linux}"); AUDIT_TIME=$(date -u +'%Y-%m-%dT%H:%M:%SZ')
  snapshot virt systemd-detect-virt; ENVIRONMENT=$(snippet virt 1); ENVIRONMENT=${ENVIRONMENT:-unknown}
  snapshot ip ip -brief address; ok ip && IP_INFO=$(clean "$(snippet ip 8)")
  if (( EUID == 0 )); then emit SYS-01 Authentication PASS HIGH 2 'Privileged audit coverage' 'Running as root' 'Use sudo for complete local visibility'
  else unknown SYS-01 Authentication HIGH 2 'Privileged audit coverage' 'Not root: shadow, firewall and process details may be unreadable' 'Rerun with sudo'; fi
  emit SYS-02 Recovery INFO LOW 0 'Audit boundaries' "environment=$ENVIRONMENT; filesystem scan timeout=${SCAN_SECONDS}s; deep=$DEEP" 'External firewall, CVEs, backup restore and incident response require independent validation'
}

audit_accounts() {
  progress 'Users and PAM'
  local n ev uid_min now user hash last min max warn inactive expire rest interactive=0 aging=0 expired=0 weak=0 empty=0 service_shell=0 locked=0
  if [[ ! -r /etc/passwd ]]; then unknown AUTH-01 Authentication HIGH 3 'Account inventory' 'passwd unreadable' 'Inspect NSS and local account access'; return; fi
  n=$(awk -F: '$3==0 && $1!="root" {n++} END {print n+0}' /etc/passwd)
  ev=$(awk -F: '$3==0 && $1!="root" {print $1}' /etc/passwd)
  count_check AUTH-01 Authentication 'Additional local UID 0 accounts' "$n" CRITICAL "$ev" 'Investigate duplicate administrative accounts immediately' 3
  n=$(awk -F: '{if(seen[$3]++)n++} END {print n+0}' /etc/passwd)
  count_check AUTH-02 Authentication 'Duplicate local UIDs' "$n" HIGH 'Local passwd entries' 'Assign unique identities unless explicitly documented'
  n=$(awk -F: '{if(seen[$3]++)n++} END {print n+0}' /etc/group)
  count_check AUTH-03 Authentication 'Duplicate local GIDs' "$n" MEDIUM 'Local group entries' 'Review duplicate group identities'
  uid_min=$(awk '$1=="UID_MIN"{print $2}' /etc/login.defs 2>/dev/null); uid_min=${uid_min:-1000}
  [[ $uid_min =~ ^[0-9]+$ ]] || uid_min=1000
  service_shell=$(awk -F: -v m="$uid_min" '$3>0 && $3<m && $7!~/(nologin|false)$/ {n++} END{print n+0}' /etc/passwd)
  if (( service_shell > 0 )); then emit AUTH-04 Authentication WARN MEDIUM 1 'Service accounts with interactive shells' "Count=$service_shell; exceptions may be legitimate" 'Review necessity, credentials and login restrictions'
  else emit AUTH-04 Authentication PASS MEDIUM 1 'Service accounts with interactive shells' 'None in local passwd' 'Keep service identities restricted'; fi
  if [[ -r /etc/shadow ]]; then
    now=$(( $(date +%s) / 86400 ))
    while IFS=: read -r user hash last min max warn inactive expire rest; do
      [[ -z $hash ]] && empty=$((empty+1 ))
      if [[ $hash == '!'* || $hash == '*'* ]]; then locked=$((locked+1)); continue; fi
      [[ -z $hash ]] && continue
      # Hashes never leave this loop; values are never emitted, stored or cracked.
      case "$hash" in '$y$'*|'$6$'*|'$2a$'*|'$2b$'*|'$2y$'*) ;; *) weak=$((weak+1 ));; esac
      if awk -F: -v u="$user" '$1==u && $7!~/(nologin|false)$/ {found=1} END {exit !found}' /etc/passwd; then
        interactive=$((interactive+1 ))
        [[ ! $max =~ ^[0-9]+$ || $max -gt 365 ]] && aging=$((aging+1 ))
        if [[ $expire =~ ^[0-9]+$ ]] && (( expire>0 && expire<now )); then expired=$((expired+1 )); fi
      fi
    done < /etc/shadow
    unset hash
    emit AUTH-14 Authentication INFO LOW 0 'Local password lock inventory' "Locked password fields=$locked; a locked password does not block SSH keys or every PAM path" 'Validate complete account access when decommissioning users'
    count_check AUTH-05 Authentication 'Accounts with empty password fields' "$empty" CRITICAL 'Local shadow; PAM may impose further restrictions' 'Lock or replace empty credentials; inspect every authentication path' 3
    count_check AUTH-06 Authentication 'Legacy or unrecognized active password hashes' "$weak" HIGH 'Algorithm classification only; no hash values disclosed' 'Use supported password hashing; migrate through a controlled credential reset' 2
    if (( aging > 0 )); then emit AUTH-07 Authentication WARN LOW 1 'Password aging policy review' "$aging of $interactive password-enabled interactive accounts have max age >365 days or unset" 'Apply organizational policy; rotation alone does not establish password strength'
    else emit AUTH-07 Authentication PASS LOW 1 'Password aging policy review' "Reviewed $interactive password-enabled interactive accounts" 'Prefer MFA, breached-password screening and controlled lifecycle'; fi
    if (( expired > 0 )); then emit AUTH-08 Authentication WARN LOW 1 'Expired interactive accounts' "Count=$expired; expired accounts may already be blocked" 'Remove obsolete accounts through an approved lifecycle'
    else emit AUTH-08 Authentication PASS LOW 1 'Expired interactive accounts' 'None detected in local shadow' 'Maintain account lifecycle'; fi
  else
    unknown AUTH-05 Authentication CRITICAL 3 'Accounts with empty password fields' 'shadow unreadable' 'Rerun as root'
    unknown AUTH-06 Authentication HIGH 2 'Password hash algorithm classification' 'shadow unreadable' 'Rerun as root'
    unknown AUTH-07 Authentication LOW 1 'Password aging policy review' 'shadow unreadable' 'Rerun as root'
    unknown AUTH-08 Authentication LOW 1 'Expired interactive accounts' 'shadow unreadable' 'Rerun as root'
  fi
  # Do not claim that grep reconstructs the complete PAM authentication stack.
  n=$(find /etc/pam.d -maxdepth 1 -type f -exec grep -El '^[[:space:]]*password[[:space:]].*pam_(pwquality|passwdqc)\.so' {} + 2>/dev/null | wc -l)
  if (( n>0 )); then emit AUTH-09 Authentication WARN LOW 1 'Password quality module references' "$n PAM files reference a quality module; effective options not validated" 'Validate PAM includes, enforce_for_root and organization-specific quality settings'
  else emit AUTH-09 Authentication WARN MEDIUM 1 'Password quality module references' 'No active pwquality/passwdqc reference found' 'Review effective password creation policy and external identity provider'; fi
  snapshot sudo visudo -c
  if ok sudo; then emit AUTH-10 Authentication PASS HIGH 2 'sudoers syntax' 'visudo validation successful' 'Preserve validated sudoers policy'
  else unknown AUTH-10 Authentication HIGH 2 'sudoers syntax' 'visudo unavailable or validation failed' 'Run visudo -c and investigate diagnostics'; fi
  if [[ -e /etc/sudoers || -d /etc/sudoers.d ]]; then
    scan_pattern sudonopass "$SCAN_SECONDS" '^[[:space:]]*[^#[:space:]].*(NOPASSWD|!authenticate)' /etc/sudoers /etc/sudoers.d
    scan_result sudonopass AUTH-11 Authentication 'Passwordless sudo policy review' MEDIUM 'Validate precise command allowlists and intended automation accounts; effective sudo -l remains necessary' 1 WARN
  else emit AUTH-11 Authentication NA MEDIUM 1 'Passwordless sudo policy review' 'No local sudo policy path detected' 'Validate other privilege delegation mechanisms'; fi
  ev=$(awk -F: '$1~/^(sudo|wheel|docker|lxd|disk)$/ {print $1 ": " $4}' /etc/group)
  emit AUTH-12 Authentication INFO HIGH 0 'Privileged group membership' "$ev" 'Review root-equivalent groups and external/NSS users; this is local inventory'
  unknown AUTH-13 Authentication MEDIUM 1 'MFA and external identity enforcement' 'Cannot establish SSSD/LDAP/MFA effective policy from local passwd alone' 'Validate identity provider and administrative login workflows'
  n=$(awk -F: '{if(seen[$1]++)n++} END {print n+0}' /etc/passwd)
  count_check AUTH-15 Authentication 'Duplicate local login names' "$n" HIGH 'Local passwd entries' 'Resolve ambiguous local identities'
  snapshot logins last -n 20 -F
  emit AUTH-16 Authentication INFO LOW 0 'Recent login inventory' 'Bounded wtmp login history in export when available; records may be incomplete or altered' 'Correlate inactive accounts and unusual logins with identity provider and SIEM evidence'
}

audit_ssh() {
  progress SSH
  local sshd_path='' key expected severity id title val n=0 sshstate=OBSERVED begin=${#IDS[@]}
  local -a args=(-T)
  sshd_path=$(command -v sshd || true)
  if [[ -z $sshd_path && ! -d /etc/ssh/sshd_config.d && ! -f /etc/ssh/sshd_config ]]; then
    sshstate=NA
  fi
  [[ -n $SSH_CONTEXT ]] && args+=(-C "$SSH_CONTEXT")
  [[ -n $SSH_CONFIG ]] && args+=(-f "$SSH_CONFIG")
  snapshot ssh "${sshd_path:-sshd}" "${args[@]}"
  if ! ok ssh && [[ $sshstate != NA ]]; then sshstate=UNKNOWN; fi
  while IFS='|' read -r id key expected severity title; do
    val=$(awk -v k="$key" '$1==k {print $2;exit}' "$TMP/ssh")
    value_check "$id" SSH "$title" "$val" "$expected" "$severity" 'Validate intended SSH policy; test access before changing authentication'
  done <<'SSH_CHECKS'
SSH-01|permitrootlogin|no|HIGH|SSH root login disabled (selected scope)
SSH-02|passwordauthentication|no|MEDIUM|SSH password authentication disabled (selected scope)
SSH-03|permitemptypasswords|no|CRITICAL|SSH empty passwords prohibited (selected scope)
SSH-04|pubkeyauthentication|yes|MEDIUM|SSH public key authentication enabled
SSH-05|x11forwarding|no|LOW|SSH X11 forwarding policy
SSH-06|gatewayports|no|MEDIUM|SSH remote forwarding bind policy
SSH-07|hostbasedauthentication|no|HIGH|SSH host-based authentication disabled
SSH-08|ignorerhosts|yes|HIGH|SSH ignores rhosts
SSH-09|permituserenvironment|no|MEDIUM|SSH user environment injection disabled
SSH-15|allowtcpforwarding|no|MEDIUM|SSH TCP forwarding restriction baseline
SSH-16|allowagentforwarding|no|LOW|SSH agent forwarding restriction baseline
SSH_CHECKS
  val=$(awk '$1=="maxauthtries" {print $2}' "$TMP/ssh")
  if [[ $val =~ ^[0-9]+$ ]] && (( val<=4 )); then emit SSH-10 SSH PASS MEDIUM 1 'SSH authentication attempt limit' "maxauthtries=$val" 'Keep an appropriate authentication attempt limit'
  elif [[ $val =~ ^[0-9]+$ ]]; then emit SSH-10 SSH WARN MEDIUM 1 'SSH authentication attempt limit' "maxauthtries=$val; target<=4" 'Review rate limiting and authentication attempts'
  else unknown SSH-10 SSH MEDIUM 1 'SSH authentication attempt limit' 'Unavailable' 'Inspect effective sshd policy'; fi
  val=$(awk '$1~/^(ciphers|macs|kexalgorithms)$/ {print}' "$TMP/ssh")
  if [[ $val =~ (3des|arcfour|diffie-hellman-group1-sha1|hmac-md5|ssh-rsa|cbc) ]]; then emit SSH-11 SSH WARN MEDIUM 1 'SSH legacy cryptography candidates' 'Legacy algorithm pattern in selected configuration' 'Review effective algorithm sets against approved compatibility requirements'
  else emit SSH-11 SSH PASS MEDIUM 1 'SSH legacy cryptography candidates' 'No listed legacy pattern in effective algorithm settings' 'Retain vendor-supported cryptographic defaults'; fi
  emit SSH-17 SSH INFO LOW 0 'SSH access and MFA policy indicators' "$(awk '$1~/^(allowusers|allowgroups|authenticationmethods|kbdinteractiveauthentication|usepam|maxsessions|logingracetime|clientaliveinterval|clientalivecountmax)$/ {print}' "$TMP/ssh")" 'Validate AllowUsers/Groups and AuthenticationMethods with PAM/MFA; keyboard-interactive may implement MFA and must not be disabled blindly'
  if [[ -z $SSH_CONTEXT ]]; then unknown SSH-12 SSH HIGH 2 'SSH Match and daemon scope coverage' 'Global defaults only; Match rules, custom daemon flags and other sshd instances may differ' 'Repeat with --ssh-context for each allowed admin identity/address and --sshd-config when needed'
  else emit SSH-12 SSH WARN LOW 1 'SSH Match and daemon scope coverage' "Selected context: $SSH_CONTEXT; other contexts and daemon flags not validated" 'Audit each permitted identity, source range and actual daemon configuration'; fi
  perm_check SSH-13 SSH "${SSH_CONFIG:-/etc/ssh/sshd_config}" 0022 HIGH
  if [[ -r /etc/shadow ]]; then
    val=$(awk '$1=="permitrootlogin" {print $2}' "$TMP/ssh")
    expected=$(awk '$1=="passwordauthentication" {print $2}' "$TMP/ssh")
    if [[ $val == yes && $expected == yes ]] && awk -F: '$1=="root" && $2!~/^[!*]/ && length($2)>0 {found=1} END{exit !found}' /etc/shadow; then
      emit SSH-14 SSH FAIL CRITICAL 3 'Root password login combination' 'Selected SSH scope permits root/password and root has an active local hash; network reachability unverified' 'Prefer restricted administrative identities and MFA/key access'
    else emit SSH-14 SSH PASS HIGH 3 'Root password login combination' 'Combination not present in selected scope' 'Validate alternate PAM and Match paths'; fi
  else unknown SSH-14 SSH HIGH 3 'Root password login combination' 'Shadow unreadable' 'Rerun with sudo'; fi
  if [[ $sshstate != OBSERVED ]]; then
    for ((id=begin;id<${#IDS[@]};id++)); do
      STATES[id]=$sshstate; EVIDENCES[id]='Effective OpenSSH configuration unavailable; no failure inferred from missing tooling'; ACTUALS[id]=${EVIDENCES[id]}
    done
  fi
  return 0
}

audit_apt() {
  progress Patching
  local n val newest now age id
  if [[ $DISTRO_FAMILY == Debian ]] && have apt-get && have dpkg; then
    snapshot aptsim apt-get -s -o Debug::NoLocking=1 -o Dir::Cache::pkgcache='' -o Dir::Cache::srcpkgcache='' upgrade
    if ok aptsim; then
      n=$(awk '$1=="Inst" {n++} END{print n+0}' "$TMP/aptsim")
      if (( n>0 )); then emit PATCH-01 Patching WARN MEDIUM 2 'Pending package upgrades (cached metadata)' "Upgrade candidates=$n; simulation only; security classification separate" 'Refresh package metadata in your change workflow and review upgrades'
      else emit PATCH-01 Patching PASS MEDIUM 2 'Pending package upgrades (cached metadata)' 'No upgrade candidates in cache; freshness checked separately' 'Maintain fresh security indexes'; fi
      n=$(awk '$1=="Inst" && /[Ss]ecurity/ {n++} END{print n+0}' "$TMP/aptsim")
      if (( n>0 )); then emit PATCH-02 Patching FAIL HIGH 3 'Security-pocket upgrade candidates' "Detected=$n from simulated upgrade origin text; no CVE severity inference" 'Prioritize vendor security updates through change management'
      else unknown PATCH-02 Patching HIGH 3 'Security-pocket upgrade candidates' 'No security-origin candidates in simulation; absence is not a full security status verdict' 'Use distro security tooling/OVAL with current repository data'; fi
    else
      unknown PATCH-01 Patching HIGH 2 'Pending package upgrades' 'APT simulation failed/timed out' 'Inspect APT health and cached metadata'
      unknown PATCH-02 Patching HIGH 3 'Security upgrades' 'APT simulation unavailable' 'Inspect vendor security status'; fi
    newest=$(find /var/lib/apt/lists -maxdepth 1 -type f -name '*Packages*' -printf '%T@\n' 2>/dev/null | sort -nr | head -n1); newest=${newest%%.*}
    now=$(date +%s)
    if [[ $newest =~ ^[0-9]+$ ]]; then
      age=$(( (now-newest)/86400 ))
      if (( age<0 )); then unknown PATCH-03 Patching MEDIUM 3 'APT index freshness' 'Future timestamp detected' 'Validate clock and metadata timestamps'
      elif (( age>7 )); then emit PATCH-03 Patching FAIL HIGH 3 'APT index freshness' "Newest package index age=$age days; timestamp is a heuristic" 'Refresh indexes before deciding patch status'
      else emit PATCH-03 Patching WARN LOW 3 'APT index freshness' "Newest package index age=$age days; individual repository freshness not proven" 'Verify successful update of every configured security repository'; fi
    else unknown PATCH-03 Patching HIGH 3 'APT index freshness' 'No package indexes found' 'Verify APT security repositories and refresh indexes outside this audit'; fi
    snapshot dpkg_audit dpkg --audit
    if ok dpkg_audit; then
      n=$(wc -c < "$TMP/dpkg_audit")
      count_check PATCH-04 Patching 'Incomplete package transactions' "$n" HIGH 'dpkg --audit returned diagnostics; review them locally' 'Repair package state through an approved maintenance task'
    else unknown PATCH-04 Patching HIGH 1 'Package database health' 'dpkg --audit unavailable' 'Inspect package database'; fi
    snapshot holds apt-mark showhold
    if ok holds; then
      n=$(wc -l < "$TMP/holds")
      if (( n>0 )); then emit PATCH-05 Patching WARN MEDIUM 1 'Held packages' "Count=$n; $(snippet holds)" 'Check whether holds block security fixes'
      else emit PATCH-05 Patching PASS MEDIUM 1 'Held packages' 'No package holds' 'Review any future pinning and holds'; fi
    else unknown PATCH-05 Patching MEDIUM 1 'Held packages' 'apt-mark failed' 'Inspect package holds'; fi
    snapshot aptconf apt-config dump
    if ok aptconf; then
      val=$(awk '$1=="APT::Periodic::Unattended-Upgrade" {gsub(/[";]/,"",$2); print $2}' "$TMP/aptconf")
      if [[ $val =~ ^[1-9][0-9]*$ ]]; then emit PATCH-06 Patching WARN LOW 2 'Automatic upgrade configuration' "Periodic unattended interval=$val days; execution and origins still require verification" 'Validate allowed security origins, timer execution and maintenance policy'
      else emit PATCH-06 Patching WARN MEDIUM 2 'Automatic upgrade configuration' 'Periodic unattended upgrades not enabled in effective apt-config' 'Use a verified automatic or centrally managed patch workflow'; fi
      n=$(awk 'tolower($0)~/allow(insecure|unauthenticated|downgradetoinsecure)/ && /"true"/ {n++} END{print n+0}' "$TMP/aptconf")
      count_check PATCH-07 Patching 'APT insecure trust overrides' "$n" HIGH 'Effective APT configuration flags' 'Remove unsafe repository signature bypasses after validating repository keys' 2
    else unknown PATCH-06 Patching MEDIUM 2 'Automatic upgrades' 'apt-config unavailable' 'Review patch orchestration'; fi
    scan_files sourcefiles "$SCAN_SECONDS" /etc/apt -maxdepth 3 -type f \( -name '*.list' -o -name '*.sources' \) -print0
    local -a sourcepaths=()
    mapfile -d '' -t sourcepaths < "$TMP/sourcefiles"
    if ok sourcefiles && (( ${#sourcepaths[@]}>0 )); then
      scan_pattern sourcetrust "$SCAN_SECONDS" '^[[:space:]]*(Trusted:[[:space:]]*yes|[^#[:space:]].*trusted[[:space:]]*=[[:space:]]*yes)' "${sourcepaths[@]}"
      scan_result sourcetrust PATCH-08 Patching 'Explicitly trusted APT sources' HIGH 'Require authenticated repository metadata; check whether each matching source is enabled' 1 WARN
    else unknown PATCH-08 Patching HIGH 1 'Explicitly trusted APT sources' 'Source enumeration incomplete or no source files found' 'Validate effective repository authentication'; fi
    emit PATCH-09 Patching INFO LOW 0 'Package and upgrade history' "history.log mtime: $(stat -c %y /var/log/apt/history.log 2>/dev/null || printf unavailable)" 'A log modification time does not prove a successful security upgrade'
  elif have rpm; then
    emit PATCH-00 Patching INFO LOW 0 'RPM platform detected' 'Core checks run; RPM update/CVE classification is not implemented in this version' 'Use vendor DNF/YUM security tools'
    unknown PATCH-01 Patching HIGH 5 'RPM patch coverage' 'Ubuntu/Debian APT module only' 'Validate updates with vendor tooling'
  else unknown PATCH-01 Patching HIGH 5 'Package update coverage' 'Unsupported package manager' 'Use distribution-specific patch tooling'; fi

}

audit_firewall_network() {
  progress 'Firewall / network'
  local n val family name st hasfw=0
  snapshot ufw ufw status verbose
  snapshot nft nft list ruleset
  snapshot ipt iptables-save
  snapshot ip6t ip6tables-save
  for name in ufw nft ipt ip6t; do
    if ok "$name"; then
      if [[ $name == ufw ]] && grep -q 'Status: active' "$TMP/ufw"; then hasfw=1; fi
      if [[ $name == nft ]] && grep -q 'hook ' "$TMP/nft"; then hasfw=1; fi
      if [[ $name == ipt || $name == ip6t ]] && grep -q '^-A ' "$TMP/$name"; then hasfw=1; fi
      emit "FW-INVENTORY-$name" Firewall INFO LOW 0 "Firewall snapshot: $name" "Readable locally; lines=$(wc -l < "$TMP/$name")" 'Full rules are included in private report inventory; evaluate rule order, sets, jumps and NAT'
    fi
  done
  if (( hasfw )); then emit FW-01 Firewall WARN LOW 2 'Host filtering rules detected' 'Rules/configuration exist; effective packet verdicts not established' 'Validate INPUT, FORWARD, OUTPUT, NAT and upstream enforcement'
  elif ok nft || ok ipt; then emit FW-01 Firewall WARN HIGH 2 'Host filtering rules not established' 'No filtering rule indicator detected in readable backends' 'Validate intended host firewall and upstream controls'
  else unknown FW-01 Firewall HIGH 2 'Host firewall visibility' 'No readable netfilter backend' 'Rerun as root with appropriate distro firewall inspection tools'; fi
  if ok ufw && grep -q 'Status: active' "$TMP/ufw"; then
    if grep -Eq 'Default: (deny|reject) \(incoming\)' "$TMP/ufw"; then emit FW-02 Firewall PASS MEDIUM 2 'UFW configured incoming default' 'Active UFW reports deny/reject incoming; other packet paths are separate' 'Validate actual packet reachability and container traffic'
    else emit FW-02 Firewall FAIL HIGH 2 'UFW configured incoming default' 'Active UFW default not deny/reject incoming' 'Review default policy and explicit least-privilege allow rules'; fi
    n=$(grep -Ec 'ALLOW IN[[:space:]]+Anywhere' "$TMP/ufw" || true)
    if (( n>0 )); then emit FW-03 Firewall WARN MEDIUM 2 'UFW source-wide allow candidates' "Count=$n; rule order and other restrictions unverified" 'Review broad source rules; do not infer internet reachability solely from this output'
    else emit FW-03 Firewall PASS MEDIUM 2 'UFW source-wide allow candidates' 'No simple Anywhere ALLOW IN pattern' 'Validate advanced rules and other backends'; fi
  else
    unknown FW-02 Firewall HIGH 2 'Default ingress verdict' 'Non-UFW or inactive UFW; nftables/iptables chains not reduced to a verdict' 'Evaluate full ordered ruleset including custom chains'
    unknown FW-03 Firewall MEDIUM 2 'Source restriction coverage' 'Requires full ruleset interpretation' 'Review IPv4/IPv6 source ranges and rule order'; fi
  unknown FW-04 Firewall HIGH 2 'IPv6 enforcement parity' 'IPv6 rules inventoried when readable; semantic equivalence not proven' 'Test IPv6 policy independently on every reachable address'
  unknown FW-05 Firewall HIGH 2 'External exposure and upstream firewall' 'No external probe; cloud security groups, load balancers and NAT not queried' 'Perform authorized external validation and review cloud/upstream policy'
  unknown FW-06 Firewall MEDIUM 1 'Outbound egress policy' 'Connections/rules inventoried; intended destination allowlist unavailable' 'Correlate approved egress destinations with process ownership and firewall logs'
  snapshot sockets ss -H -lntup
  snapshot connections ss -H -ntup state established
  snapshot routes ip route show table all
  if ok sockets; then
    emit NET-01 Network INFO LOW 0 'Listening TCP/UDP sockets' "$(snippet sockets 12)" 'Report inventory has up to 200 lines; wildcard binding is not proof of internet access'
    n=$(listener_count legacy "$TMP/sockets")
    count_check NET-02 Network 'Legacy service listener candidates on non-loopback addresses' "$n" HIGH 'Detected by local port number; verify protocol/process before remediation' 'Restrict or replace unnecessary legacy services'
    n=$(listener_count database "$TMP/sockets")
    if (( n>0 )); then emit NET-03 Network WARN HIGH 3 'Database/cache listener candidates on non-loopback addresses' "Count=$n; source reachability, TLS and authentication unverified" 'Validate intended database network, access controls and service authentication'
    else emit NET-03 Network PASS HIGH 3 'Database/cache listener candidates on non-loopback addresses' 'No matching default-port candidates; custom ports may exist' 'Review socket inventory against service inventory'; fi
    n=$(awk '$5~/^(0\.0\.0\.0:|\[?::\]?:|\*:)/{n++} END{print n+0}' "$TMP/sockets")
    if (( n>0 )); then emit NET-04 Network WARN LOW 1 'Wildcard listening sockets' "Count=$n; may be intentional" 'Validate each listener and bind scope'
    else emit NET-04 Network PASS LOW 1 'Wildcard listening sockets' 'No wildcard bind pattern detected' 'Review explicit non-loopback addresses as well'; fi
  else
    unknown NET-01 Network HIGH 3 'Listening socket visibility' 'ss unavailable/failed' 'Install inspection tooling separately and rerun'; fi
  snapshot links ip -details link show
  if ok links; then
    n=$(grep -Ec '<[^>]*PROMISC' "$TMP/links" || true)
    if (( n>0 )); then emit NET-05 Network WARN LOW 1 'Promiscuous interfaces' "Count=$n; bridges and monitoring may require this" 'Validate against the host networking role'
    else emit NET-05 Network PASS LOW 1 'Promiscuous interfaces' 'None reported' 'Maintain approved network configuration'; fi
  else unknown NET-05 Network LOW 1 'Interface flags' 'ip unavailable' 'Inspect interface configuration'; fi
  emit NET-06 Network INFO LOW 0 'Resolver configuration' "resolv.conf mode/link: $(stat -c '%a %F' /etc/resolv.conf 2>/dev/null)" 'Validate intended resolver, split DNS and DNSSEC policy separately'
}

scan_files() {
  local name=$1 seconds=$2; shift 2
  local rc=0 p type skipped=0
  local -a roots=() expr=() depth=()
  while (($#)) && [[ $1 != -* && $1 != '(' && $1 != '!' ]]; do
    p=$1; shift
    if [[ $p == /proc ]]; then roots+=("$p"); continue; fi
    type=$(run findmnt -n -o FSTYPE -T "$p" 2>/dev/null || true)
    case "$type" in ext2|ext3|ext4|xfs|btrfs|zfs|f2fs|overlay|tmpfs|ramfs) roots+=("$p");; *) skipped=1;; esac
  done
  expr=("$@")
  if [[ $MODE == standard && $name =~ ^(ww|orphan|suid|large|secrets|extra_) ]]; then depth=(-maxdepth 6); fi
  if [[ $MODE == quick && $name =~ ^(ww|orphan|suid|large|secrets|extra_) ]] || ((${#roots[@]}==0)); then
    : > "$TMP/$name"; : > "$TMP/$name.err"; printf 125 > "$TMP/$name.rc"; return 0
  fi
  timeout --signal=TERM --kill-after=2s "${seconds}s" find "${roots[@]}" "${depth[@]}" "${expr[@]}" > "$TMP/$name" 2> "$TMP/$name.err" || rc=$?
  ((skipped && rc==0)) && rc=125
  printf '%s' "$rc" > "$TMP/$name.rc"
}
listener_count() {
  local kind=$1 file=$2
  awk -v kind="$kind" '
    $5~/^(127\.|\[?::1\]?:|\[?::ffff:127\.)/ {next}
    kind=="legacy" && $5~/:(21|23|69|111|512|513|514)$/ {n++}
    kind=="database" && $5~/:(3306|5432|6379|27017|9200|11211)$/ {n++}
    END {print n+0}' "$file"
}
scan_result() {
  local name=$1 id=$2 cat=$3 title=$4 sev=$5 fix=$6 weight=${7:-1} kind=${8:-FAIL}
  local count=0 paths='' p
  while IFS= read -r -d '' p; do count=$((count+1 )); (( count<=5 )) && paths+="$(clean "$p") [$(stat -c 'mode=%a uid=%u gid=%g' -- "$p" 2>/dev/null || printf metadata-unavailable)]; "; done < "$TMP/$name"
  if (( count>0 )); then emit "$id" "$cat" "$kind" "$sev" "$weight" "$title" "Matches=$count; $paths; scan_complete=$(ok "$name" && printf yes || printf no)" "$fix"
  elif ok "$name"; then emit "$id" "$cat" PASS "$sev" "$weight" "$title" 'No matches in bounded scan scope; not a forensic clearance' "$fix"
  else unknown "$id" "$cat" "$sev" "$weight" "$title" 'Scan incomplete, timed out or permission errors; no-match result is inconclusive' "$fix"; fi
  if ((count>0)) && ! ok "$name"; then
    unknown "$id-COVERAGE" "$cat" "$sev" "$weight" "$title: remaining scope" 'Partial matches observed, but complete scope was not examined' 'Rerun with adequate permissions/time budget or inspect excluded paths separately'
  fi
}
audit_filesystem() {
  progress Filesystem
  local path mask id n max mounts roots=() fstype target line home uid shell user gid gecos pass
  snapshot df df -P -l
  snapshot dfi df -Pi -l
  for id in df dfi; do
    if ok "$id"; then
      max=$(awk 'NR>1 {gsub(/%/,"",$5); if($5+0>m)m=$5+0} END{print m+0}' "$TMP/$id")
      [[ $id == df ]] && DISK_USE=$max
      [[ $id == dfi ]] && INODE_USE=$max
      if (( max>DISK_CRITICAL )); then emit "FS-$id" Filesystem FAIL CRITICAL 2 "Local ${id/dfi/inode} capacity" "Maximum usage=$max%; full inventory in export" 'Resolve capacity pressure without deleting unknown production data'
      elif (( max>DISK_HIGH )); then emit "FS-$id" Filesystem FAIL HIGH 2 "Local capacity" "Maximum usage=$max%" "Resolve capacity pressure"
      elif (( max>DISK_WARN )); then emit "FS-$id" Filesystem WARN MEDIUM 2 "Local ${id/dfi/inode} capacity" "Maximum usage=$max%" 'Plan capacity cleanup/expansion'
      else emit "FS-$id" Filesystem PASS MEDIUM 2 "Local ${id/dfi/inode} capacity" "Maximum usage=$max%" 'Maintain capacity monitoring'; fi
    else unknown "FS-$id" Filesystem MEDIUM 2 'Filesystem capacity' 'df failed' 'Inspect mounts and capacity'; fi
  done
  perm_check FS-01 Filesystem /etc/passwd 0022 HIGH
  perm_check FS-02 Filesystem /etc/group 0022 HIGH
  perm_check FS-03 Filesystem /etc/shadow 0037 HIGH 2
  perm_check FS-04 Filesystem /etc/gshadow 0037 HIGH 2
  perm_check FS-05 Filesystem /etc/sudoers 0027 HIGH 2
  perm_check FS-06 Filesystem /etc/crontab 0022 HIGH
  perm_check FS-07 Filesystem /boot/grub/grub.cfg 0022 HIGH
  perm_check FS-08 Filesystem /root 0077 HIGH
  for path in /tmp /var/tmp /dev/shm; do
    if [[ -d $path ]]; then
      line=$(stat -Lc %a "$path" 2>/dev/null)
      if [[ $line =~ ^[0-7]+$ ]] && (( (8#$line & 0002) != 0 && (8#$line & 01000)==0 )); then
        emit "FS-STICKY-$path" Filesystem FAIL HIGH 1 "Shared directory sticky bit: $path" "mode=$line" 'Validate owner and restore sticky bit on intended shared writable directories'
      else emit "FS-STICKY-$path" Filesystem PASS MEDIUM 1 "Shared directory sticky bit: $path" "mode=$line" 'Preserve shared directory protections'; fi
      snapshot mountopts findmnt -n -o OPTIONS -T "$path"
      if ok mountopts; then
        line=$(<"$TMP/mountopts")
        for mask in nodev nosuid noexec; do
          if [[ ,$line, == *,$mask,* ]]; then emit "FS-MOUNT-$path-$mask" Filesystem PASS LOW 1 "Mount policy $path: $mask" 'Flag present on containing mount' 'Retain approved mount restrictions'
          else emit "FS-MOUNT-$path-$mask" Filesystem WARN LOW 1 "Mount policy $path: $mask" 'Flag absent on containing mount; shared root mount may be intentional' 'Evaluate workload compatibility before applying mount restrictions'; fi
        done
      else unknown "FS-MOUNT-$path" Filesystem LOW 3 "Mount options: $path" 'findmnt unavailable' 'Inspect containing mount options'; fi
    fi
  done
  for path in /etc /usr/local /opt /home /root /var/spool/cron; do [[ -d $path ]] && roots+=("$path"); done
  if (( DEEP )); then
    roots=()
    if have findmnt; then
      while read -r target fstype; do
        case "$fstype" in ext2|ext3|ext4|xfs|btrfs|zfs|f2fs|overlay)
          [[ $target == *\\* ]] && continue
          [[ -d $target ]] && roots+=("$target");; esac
      done < <(run findmnt -rn -o TARGET,FSTYPE 2>/dev/null)
    fi
    (( ${#roots[@]}>0 )) || roots=(/)
  fi
  emit FS-SCOPE Filesystem INFO LOW 0 'Filesystem scan scope' "Candidate roots: ${roots[*]}; only local supported filesystems; standard max depth=6; no symlink traversal; each scan has a ${SCAN_SECONDS}s timeout" 'Use --deep during an approved low-load window; timeout findings remain UNKNOWN'
  scan_files ww "$SCAN_SECONDS" "${roots[@]}" -xdev \( -path /proc -o -path /sys -o -path /dev -o -path "$TMP" \) -prune -o -type f -perm -0002 -print0
  scan_result ww FS-09 Filesystem 'World-writable regular files' HIGH 'Review owner, application need and least privilege' 2
  scan_files orphan "$SCAN_SECONDS" "${roots[@]}" -xdev \( -path /proc -o -path /sys -o -path /dev -o -path "$TMP" \) -prune -o \( -nouser -o -nogroup \) -print0
  scan_result orphan FS-10 Filesystem 'Files with unmapped owner/group' MEDIUM 'Validate deleted identities, mounted UID mapping and ownership' 1 WARN
  scan_files suid "$SCAN_SECONDS" "${roots[@]}" -xdev \( -path /proc -o -path /sys -o -path /dev -o -path "$TMP" \) -prune -o -type f \( -perm -4000 -o -perm -2000 \) -print0
  scan_result suid FS-11 Filesystem 'SUID/SGID inventory requiring baseline review' MEDIUM 'Compare with vendor/package baseline; SUID presence alone is not a vulnerability' 1 WARN
  scan_files large "$SCAN_SECONDS" "${roots[@]}" -xdev \( -path /proc -o -path /sys -o -path /dev -o -path "$TMP" \) -prune -o -type f -size +1G -print0
  scan_result large FS-12 Filesystem 'Files larger than 1 GiB in scan scope' LOW 'Review capacity use; database and backup files may be expected' 0 INFO
  # File-name and metadata checks only. Contents and secret values are never printed.
  scan_files secrets "$SCAN_SECONDS" "${roots[@]}" -xdev \( -path /proc -o -path /sys -o -path /dev -o -path "$TMP" \) -prune -o -type f \( -name '.env' -o -name '.env.*' -o -name 'id_rsa' -o -name 'id_ed25519' -o -name '*.key' -o -name '.pgpass' -o -name '.my.cnf' \) \( -perm -0004 -o -perm -0002 \) -print0
  scan_result secrets FS-13 Filesystem 'World-readable/writable sensitive-name candidates' MEDIUM 'Verify whether files contain secrets; examples/public keys may be harmless. Restrict and rotate genuinely exposed credentials' 3 WARN
  if [[ -d /etc/ssh ]]; then
    scan_files hostkeys "$SCAN_SECONDS" /etc/ssh -xdev -type f -name 'ssh_host_*_key' -perm /0077 -print0
    scan_result hostkeys FS-14 Filesystem 'SSH host private key permissions' HIGH 'Restrict SSH host private keys to the approved privileged reader' 2
  fi
  local key_bad=0 key_seen=0 key_unknown=0 mode owner
  while IFS=: read -r user pass uid gid gecos home shell; do
    [[ $shell =~ (nologin|false)$ || ! $uid =~ ^[0-9]+$ || ! $home == /* ]] && continue
    for path in "$home" "$home/.ssh" "$home/.ssh/authorized_keys"; do
      if [[ -e $path ]]; then
        key_seen=$((key_seen+1 )); line=$(stat -Lc '%a %u' -- "$path" 2>/dev/null) || { key_unknown=$((key_unknown+1 )); continue; }
        read -r mode owner <<< "$line"
        if (( (8#$mode & 0022)!=0 || (owner!=uid && owner!=0) )); then key_bad=$((key_bad+1 )); fi
      elif [[ ! -x $home ]]; then key_unknown=$((key_unknown+1 )); fi
    done
  done < /etc/passwd
  if (( key_bad>0 )); then emit FS-15 Filesystem FAIL HIGH 2 'User SSH authorization path ownership/write permissions' "Insecure paths=$key_bad; reviewed=$key_seen; inaccessible=$key_unknown" 'Inspect home/.ssh/authorized_keys ownership and trusted write access'
  elif (( key_unknown>0 )); then unknown FS-15 Filesystem HIGH 2 'User SSH authorization permissions' "Inaccessible home paths=$key_unknown" 'Rerun with sudo and review custom AuthorizedKeysFile/Command'
  else emit FS-15 Filesystem PASS HIGH 2 'User SSH authorization path ownership/write permissions' "Reviewed existing paths=$key_seen; custom SSH key locations and key identity unverified" 'Review key fingerprints, source restrictions and custom authorization providers'; fi
  if [[ $MODE == deep ]]; then snapshot folderusage du -x -h --max-depth=1 /var /home /opt /srv
  else : > "$TMP/folderusage"; printf 125 > "$TMP/folderusage.rc"; fi
  if ok folderusage; then emit FS-16 Filesystem INFO LOW 0 'Folder size inventory' "$(snippet folderusage 8)" 'Full bounded inventory in export; directory size alone is not a security defect'
  else unknown FS-16 Filesystem LOW 0 'Folder size inventory' 'Bounded du incomplete/unavailable; partial data may appear in export' 'Use a longer controlled capacity assessment during low load'; fi
}

audit_persistence() {
  progress 'Cron / persistence'
  local -a roots=()
  local path n
  for path in /etc/cron.d /etc/cron.daily /etc/cron.hourly /etc/cron.weekly /etc/cron.monthly /var/spool/cron /etc/systemd/system /usr/local/lib/systemd/system; do [[ -d $path ]] && roots+=("$path"); done
  if (( ${#roots[@]} )); then
    scan_files persistperm "$SCAN_SECONDS" "${roots[@]}" -xdev \( -type f -o -type d \) -perm /0022 -print0
    scan_result persistperm PERSIST-01 Persistence 'Group/world-writable cron and local systemd paths' HIGH 'Review executable persistence paths and trusted administrative ownership' 3
    scan_files persistowner "$SCAN_SECONDS" "${roots[@]}" -xdev ! -uid 0 ! -path '/var/spool/cron*' -print0
    scan_result persistowner PERSIST-02 Persistence 'Non-root-owned system persistence paths' HIGH 'Investigate unexpected ownership; symlinks and intentional delegation need context' 2 WARN
  else unknown PERSIST-01 Persistence HIGH 3 'Persistence path permissions' 'No expected cron/systemd paths found' 'Inspect alternate init/scheduler systems'; fi
  perm_check PERSIST-03 Persistence /etc/rc.local 0022 HIGH
  perm_check PERSIST-04 Persistence /etc/ld.so.preload 0022 HIGH 2
  if [[ -s /etc/ld.so.preload ]]; then emit PERSIST-05 Persistence WARN HIGH 2 'Dynamic linker preload configured' 'Nonempty /etc/ld.so.preload; values withheld' 'Verify every preload library against an approved package/baseline'
  else emit PERSIST-05 Persistence PASS HIGH 2 'Dynamic linker preload configured' 'No nonempty system preload file' 'Maintain integrity monitoring'; fi
  snapshot timers systemctl list-timers --all --no-pager
  snapshot services systemctl list-units --type=service --state=running --no-pager --no-legend
  snapshot enabled systemctl list-unit-files --type=service --state=enabled --no-pager --no-legend
  snapshot failed systemctl --failed --no-pager --no-legend
  if ok failed; then
    n=$(awk 'NF{n++} END{print n+0}' "$TMP/failed")
    if (( n>0 )); then emit PERSIST-06 Persistence WARN LOW 1 'Failed systemd units' "Count=$n; $(awk '{print $1}' "$TMP/failed" | head -n6)" 'Investigate failed security services and workload impact'
    else emit PERSIST-06 Persistence PASS LOW 1 'Failed systemd units' 'No failed units reported' 'Maintain service monitoring'; fi
  else unknown PERSIST-06 Persistence LOW 1 'Systemd unit state' 'No usable systemd manager' 'Inspect this host/container init system'; fi
  emit PERSIST-07 Persistence INFO LOW 0 'Cron inventory privacy' 'System and per-user spool paths scanned; cron command bodies are withheld because they may contain secrets' 'Review crontab -l for each identity and scripts called by root without executing them'
  local -a cronroots=()
  for path in /etc/crontab /etc/cron.d /etc/cron.daily /etc/cron.hourly /etc/cron.weekly /etc/cron.monthly /var/spool/cron; do [[ -e $path ]] && cronroots+=("$path"); done
  if (( ${#cronroots[@]} )); then
    scan_pattern cronreboot "$SCAN_SECONDS" '^[[:space:]]*@reboot[[:space:]]' "${cronroots[@]}"
    scan_result cronreboot PERSIST-12 Persistence 'Cron files containing reboot jobs' LOW 'Validate scheduled startup jobs against change records; command contents withheld' 0 INFO
    scan_pattern cronpipe "$SCAN_SECONDS" '^[[:space:]]*[^#[:space:]].*(curl|wget).*\|[[:space:]]*(/[^ ]*/)?(ba)?sh([[:space:]]|$)' "${cronroots[@]}"
    scan_result cronpipe PERSIST-13 Persistence 'Cron download-to-shell command candidates' MEDIUM 'Review script provenance and integrity; a pattern is not a maliciousness verdict' 2 WARN
  fi
  scan_pattern startup "$SCAN_SECONDS" '^[[:space:]]*((LD_PRELOAD|curl.*\|.*sh|wget.*\|.*sh)|[^#[:space:]].*(LD_PRELOAD|curl.*\|.*sh|wget.*\|.*sh))' /etc/profile /etc/profile.d /etc/bash.bashrc
  scan_result startup PERSIST-14 Persistence 'Shell startup injection candidates (system scope)' MEDIUM 'Review approved startup environment changes; user dotfiles require baseline comparison' 1 WARN
  unknown PERSIST-08 Persistence HIGH 2 'Unauthorized jobs, timers and shell startup changes' 'No approved baseline; a job name or recent mtime cannot establish malicious persistence' 'Compare cron, system/user units, shell startup and authorized_keys with change records'
  local -a tempdirs=()
  for path in /tmp /var/tmp /dev/shm; do [[ -d $path ]] && tempdirs+=("$path"); done
  if (( ${#tempdirs[@]} )); then
    scan_files tempexec "$SCAN_SECONDS" "${tempdirs[@]}" -xdev -path "$TMP" -prune -o -type f -perm /0111 -print0
    scan_result tempexec PERSIST-09 Persistence 'Executable files in temporary storage' MEDIUM 'Verify provenance; temporary executable files can be legitimate' 1 WARN
  fi
  scan_files deletedproc "$SCAN_SECONDS" /proc -maxdepth 2 -type l -name exe -lname '* (deleted)' -print0
  scan_result deletedproc PERSIST-10 Persistence 'Deleted but running process executable links' MEDIUM 'Correlate with package updates and process ownership; not proof of malware' 1 WARN
  unknown PERSIST-11 Persistence HIGH 2 'Package/file integrity and threat clearance' 'No trusted offline baseline; compromised hosts may falsify local tools' 'Compare signed packages and independent AIDE/EDR telemetry; investigate suspicious signals externally'
  snapshot sandbox systemd-analyze security --no-pager
  emit PERSIST-15 Persistence INFO LOW 0 'Systemd service sandbox exposure inventory' 'Bounded systemd-analyze summary in export when supported; exposure score is not a vulnerability verdict' 'Review sandbox directives in context of each service without disrupting its required privileges'

}

scan_pattern() {
  local name=$1 seconds=$2 pattern=$3 rc=0 p type skipped=0; shift 3
  local -a paths=()
  for p in "$@"; do
    [[ -e $p ]] || continue
    type=$(run findmnt -n -o FSTYPE -T "$p" 2>/dev/null || true)
    case "$type" in ext2|ext3|ext4|xfs|btrfs|zfs|f2fs|overlay|tmpfs|ramfs|proc) paths+=("$p");; *) skipped=1;; esac
  done
  : > "$TMP/$name"; : > "$TMP/$name.err"
  if (( ${#paths[@]}==0 )); then printf 2 > "$TMP/$name.rc"; return 0; fi
  # grep returns 1 for successful no-match, never execute file contents.
  timeout --signal=TERM --kill-after=2s "${seconds}s" find "${paths[@]}" -xdev -maxdepth 6 -type f -size -1048576c ! -name '*.key' ! -name '*.pem' ! -name 'id_rsa*' ! -name 'id_ed25519*' ! -name 'ssh_host_*_key' \
    -exec bash -c 'p=$1; shift; grep -IlEZ -- "$p" "$@"; r=$?; [ "$r" -le 1 ]' audit-pattern "$pattern" {} + > "$TMP/$name" 2> "$TMP/$name.err" || rc=$?
  ((skipped && rc==0)) && rc=125
  printf '%s' "$rc" > "$TMP/$name.rc"
}

audit_kernel() {
  progress 'Kernel hardening'
  local id key expected sev val title
  while IFS='|' read -r id key expected sev title; do
    val=$(run sysctl -n "$key" 2>/dev/null || true)
    value_check "$id" Kernel "$title" "$val" "$expected" "$sev" 'Validate host role and workload before applying the corresponding sysctl'
  done <<'SYSCTL_CHECKS'
KERN-01|kernel.randomize_va_space|2|HIGH|Address space randomization
KERN-02|kernel.kptr_restrict|2|MEDIUM|Kernel pointer disclosure restriction
KERN-03|kernel.dmesg_restrict|1|MEDIUM|Kernel log access restriction
KERN-04|kernel.yama.ptrace_scope|1|MEDIUM|ptrace baseline (stricter values reviewed separately)
KERN-05|fs.protected_hardlinks|1|MEDIUM|Protected hardlinks
KERN-06|fs.protected_symlinks|1|MEDIUM|Protected symlinks
KERN-07|net.ipv4.tcp_syncookies|1|MEDIUM|TCP SYN cookies
KERN-08|net.ipv4.conf.all.accept_redirects|0|MEDIUM|IPv4 all accept redirects
KERN-09|net.ipv4.conf.default.accept_redirects|0|MEDIUM|IPv4 default accept redirects
KERN-10|net.ipv4.conf.all.send_redirects|0|MEDIUM|IPv4 all send redirects
KERN-11|net.ipv4.conf.default.send_redirects|0|MEDIUM|IPv4 default send redirects
KERN-12|net.ipv4.conf.all.accept_source_route|0|MEDIUM|IPv4 all source routing
KERN-13|net.ipv4.conf.default.accept_source_route|0|MEDIUM|IPv4 default source routing
KERN-14|net.ipv6.conf.all.accept_redirects|0|MEDIUM|IPv6 all accept redirects
KERN-15|net.ipv6.conf.default.accept_redirects|0|MEDIUM|IPv6 default accept redirects
KERN-16|net.ipv6.conf.all.accept_source_route|0|MEDIUM|IPv6 all source routing
KERN-17|fs.suid_dumpable|0|HIGH|Privileged process core dump policy
SYSCTL_CHECKS
  val=$(run sysctl -n kernel.yama.ptrace_scope 2>/dev/null || true)
  # More restrictive ptrace settings satisfy the baseline.
  if [[ $val =~ ^[23]$ ]]; then
    for ((id=0;id<${#IDS[@]};id++)); do
      if [[ ${IDS[id]} == KERNEL-004 ]]; then STATES[id]=PASS; EVIDENCES[id]="Observed: $val; satisfies restrictive ptrace baseline >=1"; fi
    done
  fi
  val=$(run sysctl -n net.ipv4.conf.all.rp_filter 2>/dev/null || true)
  if [[ $val == 1 || $val == 2 ]]; then emit KERN-18 Kernel PASS MEDIUM 1 'IPv4 reverse path filtering baseline' "all.rp_filter=$val; per-interface policy and asymmetric routing separate" 'Validate effective per-interface routing behavior'
  elif [[ $val == 0 ]]; then emit KERN-18 Kernel WARN MEDIUM 1 'IPv4 reverse path filtering baseline' 'all.rp_filter=0; interface values may still enforce filtering' 'Review per-interface values against routing design'
  else unknown KERN-18 Kernel MEDIUM 1 'IPv4 reverse path filtering' 'sysctl unavailable' 'Inspect all/default/interface sysctls'; fi
  val=$(run sysctl -n net.ipv4.ip_forward 2>/dev/null || true)
  if [[ $val == 1 ]]; then emit KERN-19 Kernel WARN LOW 1 'IPv4 forwarding enabled' 'May be required by routers, VPNs and containers' 'Validate FORWARD/NAT policy and approved host role'
  elif [[ $val == 0 ]]; then emit KERN-19 Kernel PASS LOW 1 'IPv4 forwarding policy' 'Forwarding disabled' 'Enable only when required by host role'
  else unknown KERN-19 Kernel LOW 1 'IPv4 forwarding policy' 'Unavailable' 'Inspect forwarding configuration'; fi
  emit KERN-20 Kernel INFO LOW 0 'Kernel lockdown' "$(cat /sys/kernel/security/lockdown 2>/dev/null || printf unavailable)" 'Assess lockdown alongside boot trust and platform support'
}

audit_logging_isolation() {
  progress 'Logging / isolation'
  local val n
  snapshot auditservice systemctl is-active auditd
  if ok auditservice; then emit LOG-01 Logging WARN LOW 2 'auditd active' 'Service active; rule completeness and event delivery unverified' 'Validate auditctl -l and event retention without replacing rules'
  else unknown LOG-01 Logging MEDIUM 2 'Audit event coverage' 'auditd not confirmed active; alternative EDR/auditing may exist' 'Validate active audit pipeline and required rules'; fi
  snapshot journalservice systemctl is-active systemd-journald
  if ok journalservice; then emit LOG-02 Logging PASS MEDIUM 1 'System journal service' 'systemd-journald active' 'Verify retention, disk quotas and event delivery'
  else unknown LOG-02 Logging MEDIUM 1 'System logging service' 'Journald not confirmed; alternate logging may exist' 'Validate the logging architecture'; fi
  if [[ -d /var/log/journal ]]; then emit LOG-03 Logging WARN LOW 1 'Persistent journal directory' 'Directory exists; effective Storage configuration not verified' 'Verify persistent retention with effective journald configuration'
  else emit LOG-03 Logging WARN MEDIUM 1 'Persistent journal directory' 'Directory absent; other persistent/remote sinks may exist' 'Ensure logs survive reboot via approved local or remote sinks'; fi
  perm_check LOG-04 Logging /var/log 0022 HIGH
  unknown LOG-05 Logging HIGH 2 'Remote SIEM delivery and tampering assessment' 'No event-delivery test or log baseline; local logs alone cannot prove completeness' 'Verify external receipt, retention and alerts for audit/log service interruption'
  snapshot timesync timedatectl show -p NTPSynchronized --value
  if ok timesync; then value_check LOG-06 Logging 'Time synchronization' "$(snippet timesync 1)" yes MEDIUM 'Validate NTP source and chrony/timesync monitoring'
  else unknown LOG-06 Logging MEDIUM 1 'Time synchronization' 'timedatectl unavailable/failed' 'Validate actual chrony/NTP tracking and clock offset'; fi
  snapshot boot mokutil --sb-state
  if ok boot && grep -q 'SecureBoot enabled' "$TMP/boot"; then emit ISO-02 Isolation PASS LOW 1 'UEFI Secure Boot' 'mokutil reports enabled' 'Maintain firmware trust and signing policy'
  else unknown ISO-02 Isolation LOW 1 'UEFI Secure Boot' 'Not confirmed or non-UEFI/container platform' 'Validate platform applicability and boot trust'; fi
  unknown ISO-03 Isolation LOW 1 'Disk encryption policy' 'Encrypted device markers alone do not prove encryption of all sensitive data' 'Validate LUKS/cloud volume encryption, key custody and backups'

}

audit_recovery() {
  progress 'Recovery / services'
  local n
  if ok services; then
    n=$(grep -Eic '(^|[[:space:]])(telnet|rsh|rlogin|tftp|vsftpd|proftpd)' "$TMP/services" || true)
    if (( n>0 )); then emit REC-01 Recovery WARN MEDIUM 1 'Legacy service unit candidates' "Count=$n; protocol/configuration unverified" 'Validate need, encrypted access and segmentation'
    else emit REC-01 Recovery PASS MEDIUM 1 'Legacy service unit candidates' 'No listed legacy running unit patterns' 'Review socket activation and alternate daemons'; fi
  else unknown REC-01 Recovery MEDIUM 1 'Service inventory' 'systemd service inventory unavailable' 'Inspect actual process and init manager inventory'; fi
  unknown REC-02 Recovery HIGH 3 'Verified backup and restore capability' 'No backup configuration supplied; filename presence cannot prove recoverability' 'Verify successful backup, independent encrypted copy, immutable retention and a recent restore test'
  unknown REC-03 Recovery HIGH 2 'EDR, web/database TLS and application security' 'Generic host script cannot prove workload authentication, negotiated TLS, agent health or app vulnerabilities' 'Run service-specific audits and verify security telemetry centrally'
  emit REC-05 Recovery INFO LOW 0 'Compliance reporting' 'CIS-aligned coverage only; no official benchmark certification; no malware clearance' 'Use the exact licensed CIS profile/vendor USG and independent threat investigation where required'
  snapshot memory free -m
  if ok memory; then MEMORY_USE=$(awk '$1=="Mem:" && $2>0{printf "%d",100*($2-$7)/$2}' "$TMP/memory"); fi
  snapshot uptime uptime
  snapshot processes ps -eo pid,ppid,user,comm,%cpu,%mem --sort=-%cpu
  emit REC-06 Recovery INFO LOW 0 'System health inventory' "$(snippet uptime 1); $(snippet memory 3)" 'Correlate sustained CPU/memory/disk pressure with normal workload; high load is not proof of mining'
}


severity_color() { case "$1" in CRITICAL) printf '%s' "$RED";; HIGH) printf '%s' "$ORANGE";; MEDIUM) printf '%s' "$YELLOW";; LOW) printf '%s' "$CYAN";; *) printf '%s' "$GRAY";; esac; }
points_color() {
  local score=$1 coverage=${2:-100}
  if ((coverage<60)); then printf '%s' "$GRAY"
  elif ((score>=85)); then printf '%s' "$GREEN"
  elif ((score>=65)); then printf '%s' "$YELLOW"
  else printf '%s' "$ORANGE"; fi
}
overall_class() {
  if (( ${COUNTS[CRITICAL]:-0}>0 )); then printf CRITICAL
  elif (( ${COUNTS[HIGH]:-0}>0 )); then printf HIGH
  elif (( COVERAGE<80 )); then printf UNKNOWN
  elif (( SCORE>=85 )); then printf PASS
  else printf MEDIUM; fi
}
dashboard() {
  local cols=80 cat i sev state shown=0 width=20 scorecolor
  if [[ -t 1 ]] && have tput; then cols=$(tput cols 2>/dev/null || printf 80); fi
  [[ $cols =~ ^[0-9]+$ ]] || cols=80; ((cols>100)) && cols=100; ((cols<40)) && cols=40
  ((cols<68)) && width=10
  printf '\n%sENTERPRISE LINUX SECURITY ASSESSMENT%s  %sv%s%s\n' "$BOLD$CYAN" "$RESET" "$GRAY" "$VERSION" "$RESET"
  ((DEMO)) && printf '%sDEMO / SYNTHETIC DATA - NOT A HOST AUDIT%s\n' "$YELLOW" "$RESET"
  rule "$cols"
  printf 'Host    %s\nOS      %s\nKernel  %s\nAudit   %s (UTC)\n' "$HOST" "$OS" "$KERNEL" "$AUDIT_TIME"
  printf 'Family  %s | Package manager %s | Version %s\n' "$DISTRO_FAMILY" "$PACKAGE_MANAGER" "$DISTRO_VERSION"
  printf 'Arch    %s | Virtualization %s | Uptime %ss\n' "$ARCH" "$ENVIRONMENT" "$UPTIME_SECONDS"
  printf 'MAC     %s | Firewall %s\n' "$MAC_SUMMARY" "$FIREWALL_SUMMARY"
  printf 'AUDIT MODE: %s / %s / %s | Primary IP %s\n' "$AUDIT_MODE" "$MODE" "$PROFILE" "$PRIMARY_IP"
  printf 'Network %s\n' "$IP_INFO"
  rule "$cols"
  printf '%sVERIFIED POSTURE POINTS%s\n' "$BOLD" "$RESET"
  scorecolor=$(points_color "$SCORE" "$COVERAGE")
  (( ${COUNTS[CRITICAL]:-0}>0 )) && scorecolor=$RED
  (( ${COUNTS[HIGH]:-0}>0 && ${COUNTS[CRITICAL]:-0}==0 )) && scorecolor=$ORANGE
  printf '%s%s%s  %s%d / 100%s\n' "$scorecolor" "$(bar "$SCORE" 28)" "$RESET" "$BOLD$scorecolor" "$SCORE" "$RESET"
  printf '%s%s%s\n' "$BOLD$scorecolor" "$VERDICT" "$RESET"
  printf 'Evidence coverage: %s%d%%%s | Score before cap: %d | Cap: %d\n' "$BOLD" "$COVERAGE" "$RESET" "$RAW_SCORE" "$CAP"
  printf '%sCRITICAL %d%s   %sHIGH %d%s   %sMEDIUM %d%s   LOW %d\n' "$RED" "${COUNTS[CRITICAL]:-0}" "$RESET" "$ORANGE" "${COUNTS[HIGH]:-0}" "$RESET" "$YELLOW" "${COUNTS[MEDIUM]:-0}" "$RESET" "${COUNTS[LOW]:-0}"
  printf 'PASS %d | WARNING %d | FINDINGS %d | UNKNOWN %d | NOT_APPLICABLE %d | INFO %d\n' "${COUNTS[PASS]:-0}" "${COUNTS[WARN]:-0}" "${COUNTS[FAIL]:-0}" "${COUNTS[UNKNOWN]:-0}" "${COUNTS[NA]:-0}" "${COUNTS[INFO]:-0}"
  rule "$cols"
  printf '%-16s %-*s  Points  Coverage\n' Area "$width" ''
  for cat in "${CAT_ORDER[@]}"; do
    if (( ${CAT_SCORE[$cat]:--1}<0 )); then printf '%-16s %sN/A%s\n' "$cat" "$GRAY" "$RESET"
    else printf '%-16s %s%s%s  %3d%%    %3d%%\n' "$cat" "$(points_color "${CAT_SCORE[$cat]}" "${CAT_COVER[$cat]}")" "$(bar "${CAT_SCORE[$cat]}" "$width")" "$RESET" "${CAT_SCORE[$cat]}" "${CAT_COVER[$cat]}"; fi
  done
  rule "$cols"
  printf '%sSYSTEM HEALTH%s\n' "$BOLD" "$RESET"
  printf 'Disk max %s%% | Inode max %s%% | Memory pressure %s%%\n' "${DISK_USE:-unknown}" "${INODE_USE:-unknown}" "${MEMORY_USE:-unknown}"
  rule "$cols"
  printf '%sPRIORITY FINDINGS%s\n' "$BOLD" "$RESET"
  for sev in CRITICAL HIGH MEDIUM LOW; do
    for ((i=0;i<TOTAL;i++)); do
      state=${STATES[i]}
      [[ ( $state == FAIL || $state == WARN ) && ${SEVS[i]} == "$sev" ]] || continue
      ((shown>=8 && FULL==0)) && continue
      printf '%s[%s/%s]%s %s %s\n' "$(severity_color "$sev")" "$sev" "$(public_status "$state" "$sev")" "$RESET" "${IDS[i]}" "${TITLES[i]}"
      printf '  Evidence: %s\n  Action:   %s\n' "${EVIDENCES[i]}" "${FIXES[i]}"
      shown=$((shown+1))
    done
  done
  ((shown==0)) && printf 'No scored warnings/failures detected. Review UNKNOWN controls.\n'
  printf '\n%sASSESSMENT GAPS%s\n' "$BOLD" "$RESET"; shown=0
  for ((i=0;i<TOTAL;i++)); do
    [[ ${STATES[i]} == UNKNOWN ]] || continue
    ((shown>=5 && FULL==0)) && continue
    printf '%s[UNKNOWN]%s %s: %s\n' "$GRAY" "$RESET" "${IDS[i]}" "${TITLES[i]}"; shown=$((shown+1))
  done
  if ((FULL)); then
    printf '\n%sALL CONTROLS%s\n' "$BOLD" "$RESET"
    for ((i=0;i<TOTAL;i++)); do
      printf '  Level: %s | Benchmark: %s (section unverified)\n  Expected: %s\n  Actual: %s\n  Risk: %s\n  Evaluation: %s\n' "${LEVELS[i]}" "${BENCHMARKS[i]}" "${EXPECTEDS[i]}" "${ACTUALS[i]}" "${RISKS[i]}" "${EVALUATIONS[i]}"
      printf '\n[%s] %s / %s / %s\n  %s\n  Evidence: %s\n  Action: %s\n  Reference: %s\n' "$(public_status "${STATES[i]}" "${SEVS[i]}")" "${IDS[i]}" "${CATS[i]}" "${SEVS[i]}" "${TITLES[i]}" "${EVIDENCES[i]}" "${FIXES[i]}" "${REFS[i]}"
    done
  fi
  rule "$cols"
  printf 'CIS-aligned selected controls: %s%% (coverage %s%%) | Attack surface: %s%%\n' "$COMPLIANCE" "$COMPLIANCE_COVERAGE" "$ATTACK_SCORE"
  printf 'Attack evidence coverage: %s%% | Threat evidence coverage: %s%%\n' "$ATTACK_COVERAGE" "$THREAT_COVERAGE"
  printf 'Threat indicators: %s | Threat score: %s (higher is better; no clearance)\n' "$THREAT_COUNT" "$THREAT_SCORE"
  printf 'UNKNOWN excluded from score, retained in coverage; NA/INFO excluded. No official CIS certification.\n'
  printf 'Read-only assessment. Elapsed: %ds. No fixes or upgrades executed.\n' "$(( $(date +%s)-START ))"
  [[ -n $OUT ]] && printf 'Private reports: %s\n' "$OUT"
  return 0
}

json_string() {
  local s=$1
  s=${s//\\/\\\\}; s=${s//\"/\\\"}; s=${s//$'\n'/\\n}; s=${s//$'\r'/\\r}; s=${s//$'\t'/\\t}; while [[ $s =~ [[:cntrl:]] ]]; do s=${s//"${BASH_REMATCH[0]}"/}; done
  printf '"%s"' "$s"
}
html_escape() { local s=$1; s=${s//&/\&amp;}; s=${s//</\&lt;}; s=${s//>/\&gt;}; s=${s//\"/\&quot;}; s=${s//\'/\&#39;}; printf '%s' "$s"; }
export_html() {
  local i cat name
  cat <<'HTML_HEAD'
<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="Content-Security-Policy" content="default-src 'none'; style-src 'unsafe-inline'; img-src 'none'; base-uri 'none'; form-action 'none'">
<title>Linux Security Audit</title><style>
:root{color-scheme:dark;--bg:#0b1120;--panel:#121d30;--line:#263851;--text:#edf3ff;--muted:#9eafc9;--cyan:#55d5ea}*{box-sizing:border-box}body{overflow-wrap:anywhere;margin:0;background:var(--bg);color:var(--text);font:15px/1.55 system-ui,sans-serif}main{max-width:1180px;margin:40px auto;padding:0 24px}header{border-bottom:1px solid var(--line);padding-bottom:22px}.eyebrow{color:var(--cyan);letter-spacing:.15em;font-size:12px}h1{font-size:30px;margin:8px 0}h2{font-size:19px;margin:28px 0 14px}p{color:var(--muted)}.grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}.card{background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:24px;margin-top:20px}.score{font-size:66px;line-height:1.1;font-weight:750;color:var(--cyan)}.score small{font-size:24px;color:var(--muted)}.pills{display:flex;gap:10px;flex-wrap:wrap}.pill,.badge{border-radius:7px;padding:5px 10px;background:#20314b;font-size:12px;font-weight:700}.CRITICAL{color:#ff8596;background:#482035}.HIGH{color:#ffb578;background:#442d20}.MEDIUM,.WARN{color:#ffd877;background:#3d341f}.PASS{color:#88e6c0;background:#163a33}.UNKNOWN,.NA,.INFO{color:#b9c6da;background:#243149}.FAIL{color:#ff8596;background:#482035}table{width:100%;border-collapse:collapse}th{text-align:left;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.05em}td,th{padding:11px;border-bottom:1px solid var(--line)}progress{width:100%;height:10px;accent-color:var(--cyan)}.area{min-width:140px}.risk{border-left:3px solid #ffb578;margin:12px 0;padding:14px 18px;background:#172237;border-radius:4px}.risk h3{font-size:15px;margin:8px 0}.risk p{margin:7px 0;font-size:13px}.label{color:#edf3ff}details{background:var(--panel);border:1px solid var(--line);border-radius:10px;margin:9px 0;padding:12px 16px}summary{cursor:pointer}.evidence{white-space:pre-wrap;overflow-wrap:anywhere;color:var(--muted)}.notice{border-left:3px solid var(--cyan);padding:12px 18px;background:#12263a}footer{border-top:1px solid var(--line);margin-top:30px;padding-top:16px;font-size:12px;color:var(--muted)}@media(max-width:720px){.grid{grid-template-columns:1fr}main{padding:0 14px;margin:20px auto}td,th{padding:8px 4px;font-size:12px}.score{font-size:54px}.barcol{display:none}}@media print{body{background:white;color:#182238}p,.evidence,footer{color:#41516a}.card,details,.risk{background:#f1f5fa;break-inside:avoid}.grid{display:block}details{display:block}main{margin:0}header{border-color:#777}}
</style><main><header><div class="eyebrow">INFRASTRUCTURE / SECURITY POSTURE</div><h1>Linux Security Audit</h1>
HTML_HEAD
  ((DEMO)) && printf '<div class="notice">DEMO / SYNTHETIC DATA. This is a design preview, not a host assessment.</div>'
  printf '<p>%s &nbsp; / &nbsp; %s<br>Kernel %s &nbsp; / &nbsp; %s UTC</p></header>' "$(html_escape "$HOST")" "$(html_escape "$OS")" "$(html_escape "$KERNEL")" "$(html_escape "$AUDIT_TIME")"
  printf '<div class="grid"><section class="card"><div class="eyebrow">VERIFIED POSTURE POINTS</div><div class="score %s" style="background:none">%d <small>/ 100</small></div><h2>%s</h2><progress value="%d" max="100"></progress><p>Evidence coverage <strong>%d%%</strong> · Before cap %d · Risk cap %d</p></section>' "$(overall_class)" "$SCORE" "$(html_escape "$VERDICT")" "$SCORE" "$COVERAGE" "$RAW_SCORE" "$CAP"
  printf '<section class="card"><div class="eyebrow">FINDINGS REQUIRING REVIEW</div><h2>Priority counts</h2><div class="pills">'
  for cat in CRITICAL HIGH MEDIUM LOW; do printf '<span class="pill %s">%s %d</span>' "$cat" "$cat" "${COUNTS[$cat]:-0}"; done
  printf '</div><p>%d controls · %d passed · %d unknown</p><p>CIS-aligned compliance shown below<br>Threat clearance: not determined</p></section></div>' "$TOTAL" "${COUNTS[PASS]:-0}" "${COUNTS[UNKNOWN]:-0}"
  printf '<h2>Executive overview</h2><section class="card"><table><tr><th>Security area</th><th class="barcol">Verified points</th><th>Points</th><th>Coverage</th></tr>'
  for cat in "${CAT_ORDER[@]}"; do
    printf '<tr><td class="area">%s</td>' "$cat"
    if (( ${CAT_SCORE[$cat]}<0 )); then printf '<td class="barcol">—</td><td>N/A</td><td>—</td>'
    else printf '<td class="barcol"><progress value="%d" max="100"></progress></td><td>%d%%</td><td>%d%%</td>' "${CAT_SCORE[$cat]}" "${CAT_SCORE[$cat]}" "${CAT_COVER[$cat]}"; fi
    printf '</tr>'
  done
  printf '</table></section><h2>System health</h2><section class="card"><div class="pills"><span class="pill">Disk max %s%%</span><span class="pill">Inode max %s%%</span><span class="pill">Memory pressure %s%%</span></div><p>Capacity readings are operational signals, not proof of compromise.</p></section><h2>Prioritized actions</h2>' "${DISK_USE:-unknown}" "${INODE_USE:-unknown}" "${MEMORY_USE:-unknown}"
  for cat in CRITICAL HIGH MEDIUM LOW; do
    for ((i=0;i<TOTAL;i++)); do
      [[ ( ${STATES[i]} == FAIL || ${STATES[i]} == WARN ) && ${SEVS[i]} == "$cat" ]] || continue
      printf '<article class="risk"><span class="badge %s">%s / %s</span> <span class="eyebrow">%s</span><h3>%s</h3><p class="evidence"><span class="label">Evidence:</span> %s</p><p class="evidence"><span class="label">Action:</span> %s</p></article>' "$cat" "$cat" "${STATES[i]}" "${IDS[i]}" "$(html_escape "${TITLES[i]}")" "$(html_escape "${EVIDENCES[i]}")" "$(html_escape "${FIXES[i]}")"
    done
  done
  printf '<h2>Assessment context</h2><p>Family %s · Version %s · Architecture %s · Virtualization %s · Mode %s · Profile %s<br>MAC %s · Firewall %s<br>CIS-aligned score %s%% · Benchmark coverage %s%% · Attack surface %s%% · Threat indicators %s</p>' "$(html_escape "$DISTRO_FAMILY")" "$(html_escape "$DISTRO_VERSION")" "$(html_escape "$ARCH")" "$(html_escape "$ENVIRONMENT")" "$AUDIT_MODE" "$PROFILE" "$(html_escape "$MAC_SUMMARY")" "$(html_escape "$FIREWALL_SUMMARY")" "$COMPLIANCE" "$COMPLIANCE_COVERAGE" "$ATTACK_SCORE" "$THREAT_COUNT"
  printf '<h2>Findings index</h2><table><tr><th>ID</th><th>Control</th><th>Status</th><th>Severity</th></tr>'
  for ((i=0;i<TOTAL;i++)); do
    printf '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>' "${IDS[i]}" "$(html_escape "${TITLES[i]}")" "$(public_status "${STATES[i]}" "${SEVS[i]}")" "${SEVS[i]}"
  done
  printf '</table>'
  printf '<h2>All controls / technical evidence</h2>'
  for ((i=0;i<TOTAL;i++)); do
    printf '<details open><summary><span class="badge %s">%s</span> %s · %s</summary>' "${STATES[i]}" "$(public_status "${STATES[i]}" "${SEVS[i]}")" "${IDS[i]}" "$(html_escape "${TITLES[i]}")"
    html_metadata "$i"
    printf '<p class="evidence"><strong>Evidence:</strong> %s<br><strong>Recommendation:</strong> %s</p></details>' "$(html_escape "${EVIDENCES[i]}")" "$(html_escape "${FIXES[i]}")"
  done
  printf '<h2>Bounded local inventory</h2>'
  for name in ip routes sockets connections ufw nft ipt ip6t services enabled timers processes df dfi memory folderusage sandbox logins routes6 firewalld; do
    [[ -f $TMP/$name ]] || continue
    printf '<details><summary>%s · command status %s</summary><pre class="evidence">%s</pre></details>' "$name" "$(cat "$TMP/$name.rc" 2>/dev/null || printf unknown)" "$(html_escape "$(inventory_text "$name")")"
  done
  printf '<h2>Interpretation</h2><div class="notice">PASS earns full weight, WARN half and FAIL zero. UNKNOWN is excluded from the score but reduces evidence coverage. NA and INFO are excluded. Controls use severity weights. Critical caps are configurable (defaults 85/75/65 for 1/2/3+ findings). Compliance measures only selected CIS-aligned controls; unverified sections remain null. Low coverage means incomplete evidence, not proof that the server is vulnerable. These are local posture points, not a hacking probability or a certified CIS score.</div><footer>Read-only host assessment · No upgrades, remediation or external scanning. Private report: contains host, account, path and network metadata. Validate service-specific, cloud, CVE, restore and threat findings independently.</footer></main></html>\n'
}

demo() {
  HOST=WEB-PROD-01; OS='Ubuntu 24.04 LTS (synthetic)'; KERNEL='DEMO-KERNEL'; AUDIT_TIME='2026-10-05T00:00:00Z'; IP_INFO='ens160 172.16.180.20/24 (synthetic)'
  DISK_USE=82; INODE_USE=31; MEMORY_USE=61
  local cat i
  for cat in "${CAT_ORDER[@]}"; do
    for ((i=1;i<=7;i++)); do emit "DEMO-$cat-$i" "$cat" PASS LOW 1 "$cat baseline control $i" 'Synthetic passed control' 'Maintain verified policy'; done
  done
  emit DEMO-AUTH Authentication FAIL CRITICAL 3 'World-writable privileged authentication path' 'Synthetic example only' 'Review trusted ownership and investigate unauthorized changes'
  emit DEMO-PATCH Patching FAIL HIGH 2 'Security-pocket updates pending' '4 candidates (synthetic)' 'Apply approved vendor security updates'
  emit DEMO-NET Network WARN HIGH 2 'Database listener on a non-loopback address' '172.16.180.20:3306 (synthetic); external reachability unknown' 'Validate firewall and database authentication'
  emit DEMO-CRON Persistence WARN MEDIUM 1 'Unapproved root cron job candidate' 'Baseline comparison needed (synthetic)' 'Validate script provenance against change records'
  unknown DEMO-CLOUD Firewall HIGH 2 'Cloud/upstream policy' 'Not queried (synthetic)' 'Review upstream controls'
  unknown DEMO-BACKUP Recovery HIGH 2 'Restore evidence' 'Not supplied (synthetic)' 'Verify a recent restore test'
}

safe_output_parent() {
  local parent=${OUT%/*} mode uid data
  [[ -z $parent ]] && parent=/
  while :; do
    [[ ! -L $parent && -d $parent ]] || die 'Output parent must exist and contain no symlink components'
    if (( EUID==0 )); then
      data=$(stat -c '%a %u' -- "$parent") || die 'Cannot inspect report parent'
      read -r mode uid <<< "$data"
      # Root-owned sticky temp parents are safe against replacement of our owned directory.
      if (( uid!=0 || ((8#$mode & 0022)!=0 && (8#$mode & 01000)==0) )); then
        die 'Root exports need trusted root-owned parent directories; use a new /root/report-name path'
      fi
    fi
    [[ $parent == / ]] && break
    parent=${parent%/*}; [[ -z $parent ]] && parent=/
  done
}

# ---- Enterprise engine: no network fetches, mutations or arbitrary evaluation ----
MODE=standard PROFILE=enterprise SELF_TEST=0 JSON_PATH='' HTML_PATH=''
DISTRO_ID=unknown DISTRO_VERSION=unknown DISTRO_FAMILY=Unknown PACKAGE_MANAGER=unknown
ARCH=unknown PRIMARY_IP=unknown UPTIME_SECONDS=0 AUDIT_MODE=LIMITED
MAC_SUMMARY=unknown FIREWALL_SUMMARY=unknown TARGET_SUPPORT=unsupported
COMPLIANCE=0 COMPLIANCE_COVERAGE=0 ATTACK_SCORE=0 ATTACK_COVERAGE=0 THREAT_COVERAGE=0 THREAT_SCORE=100 THREAT_COUNT=0
CAP_ONE=85 CAP_TWO=75 CAP_THREE=65 DISK_WARN=80 DISK_HIGH=90 DISK_CRITICAL=95
UNICODE=0 CURRENT_EXPECTED='' CURRENT_LEVEL='' CURRENT_RISK='' CURRENT_EVALUATION=''
declare -a ASSESSMENT_TYPES=()
declare -a LEVELS=() BENCHMARKS=() EXPECTEDS=() ACTUALS=() DESCRIPTIONS=() RISKS=() EVALUATIONS=() LEGACY_IDS=()
declare -A ID_INDEX=()
CAT_ORDER=(System Authentication PAM Sudo SSH Network DNS Firewall Patching Filesystem Permissions Persistence Systemd Kernel MAC Logging Audit Cryptography Containers Threat Recovery Resources Cloud)
for _cat in "${CAT_ORDER[@]}"; do CAT_WEIGHT[$_cat]=1; done
unset _cat
command_exists() { have "$@"; }
safe_run() { run "$@"; }
canonical_id() {
  local id=$1 prefix num
  if [[ $id == *-COVERAGE ]]; then
    local base; base=$(canonical_id "${id%-COVERAGE}")
    if [[ $base =~ ^([A-Z]+)-([0-9]+)$ ]]; then printf '%s-%03d' "${BASH_REMATCH[1]}" "$((10#${BASH_REMATCH[2]}+700))"; return; fi
  fi
  case "$id" in
    KERN-*) id=KERNEL-${id#KERN-};; ISO-*) id=BOOT-${id#ISO-};; REC-*) id=BACKUP-${id#REC-};;
    FW-INVENTORY-ufw) id=FW-090;; FW-INVENTORY-nft) id=FW-091;; FW-INVENTORY-ipt) id=FW-092;; FW-INVENTORY-ip6t) id=FW-093;;
    FS-df) id=RESOURCE-001;; FS-dfi) id=RESOURCE-002;; FS-SCOPE) id=FS-090;;
    FS-STICKY-/tmp) id=FS-091;; FS-STICKY-/var/tmp) id=FS-092;; FS-STICKY-/dev/shm) id=FS-093;;
    FS-MOUNT-/tmp-nodev) id=FS-094;; FS-MOUNT-/tmp-nosuid) id=FS-095;; FS-MOUNT-/tmp-noexec) id=FS-096;;
    FS-MOUNT-/var/tmp-nodev) id=FS-097;; FS-MOUNT-/var/tmp-nosuid) id=FS-098;; FS-MOUNT-/var/tmp-noexec) id=FS-099;;
    FS-MOUNT-/dev/shm-nodev) id=FS-100;; FS-MOUNT-/dev/shm-nosuid) id=FS-101;; FS-MOUNT-/dev/shm-noexec) id=FS-102;;
    FS-MOUNT-/tmp) id=FS-103;; FS-MOUNT-/var/tmp) id=FS-104;; FS-MOUNT-/dev/shm) id=FS-105;;
  esac
  if [[ $id =~ ^([A-Z]+)-([0-9]+)$ ]]; then prefix=${BASH_REMATCH[1]}; num=${BASH_REMATCH[2]}; printf '%s-%03d' "$prefix" "$((10#$num))"
  else printf '%s' "$id"; fi
}
public_status() {
  case "$1" in FAIL) case "$2" in CRITICAL|HIGH) printf '%s' "$2";; *) printf WARNING;; esac;; WARN) printf WARNING;; NA) printf NOT_APPLICABLE;; *) printf '%s' "$1";; esac
}
emit() {
  local legacy=$1 id cat=$2 state=$3 sev=$4 weight=$5 title=$6 evidence=$7 fix=$8 ref=${9:-'Local Linux hardening policy'} level bench risk expected evalstr assessment_type=automated_check
  id=$(canonical_id "$legacy")
  case "$id" in SYS-*) cat=System;; PAM-*) cat=PAM;; SUDO-*) cat=Sudo;; PERM-*) cat=Permissions;; MAC-*) cat=MAC;; AUDIT-*) cat=Audit;; SYSTEMD-*) cat=Systemd;; CRYPTO-*|TLS-*) cat=Cryptography;; DNS-*) cat=DNS;; DOCKER-*|PODMAN-*) cat=Containers;; PROC-*|MAL-*) cat=Threat;; CLOUD-*) cat=Cloud;; RESOURCE-*) cat=Resources;; BOOT-*) cat=Kernel;; esac
  case "$state" in WARNING) state=WARN;; HIGH|CRITICAL) sev=$state; state=FAIL;; NOT_APPLICABLE) state=NA;; esac
  case "$state" in PASS|WARN|FAIL|UNKNOWN|NA|INFO) ;; *) die "Invalid status for $id: $state";; esac
  case "$sev" in CRITICAL|HIGH|MEDIUM|LOW|INFO) ;; *) die "Invalid severity for $id: $sev";; esac
  level=${CURRENT_LEVEL:-'Enterprise Extended'}
  if [[ -z $CURRENT_LEVEL ]]; then
    case "$id" in AUTH-00[12356]|SSH-00[134789]|SSH-010|FS-00[1-8]|KERNEL-00[1-7]|KERNEL-017) level='CIS Level 1';;
      SSH-015|SSH-016|FS-09[4-9]|FS-10[012]) level='CIS Level 2';;
      PERSIST-009|PERSIST-010|PERSIST-013|PERSIST-014|MAL-*|PROC-*) level='Threat Indicator';; esac
  fi
  [[ $state == INFO && $level != 'Threat Indicator' ]] && level=Informational
  bench='Enterprise local policy'; [[ $level == 'CIS Level '* ]] && bench=CIS-aligned
  if [[ $PROFILE == cis-l1 && $level != 'CIS Level 1' && $level != Informational ]] || [[ $PROFILE == cis-l2 && $level != 'CIS Level '* && $level != Informational ]]; then
    state=NA; evidence="Not selected by profile=$PROFILE; evaluation retained in full profile"
  fi
  [[ $PROFILE == enterprise && $level == 'CIS Level 2' ]] && { state=NA; evidence='Level 2 control excluded by enterprise profile; use --profile full or cis-l2'; }
  expected=${CURRENT_EXPECTED:-'No adverse indicator in the documented local scope; review recommendation'}
  [[ $evidence == *'expected: '* ]] && expected=${evidence##*expected: }
  risk=${CURRENT_RISK:-"Failure to meet this control may weaken ${cat} safeguards; applicability and workload exceptions require review."}
  evalstr=${CURRENT_EVALUATION:-"${FUNCNAME[1]:-emit}: bounded local inspection; read-only"}
  # Weight is severity-based, while explicitly informational weight=0 stays unscored.
  if (( weight>0 )); then case "$sev" in CRITICAL) weight=10;; HIGH) weight=6;; MEDIUM) weight=3;; LOW) weight=1;; *) weight=0;; esac; fi
  if [[ -v ID_INDEX[$id] ]]; then die "Duplicate check ID: $id"; fi
  ID_INDEX[$id]=${#IDS[@]}
  IDS+=("$id"); LEGACY_IDS+=("$legacy"); CATS+=("$cat"); STATES+=("$state"); SEVS+=("$sev"); WEIGHTS+=("$weight")
  TITLES+=("$(clean "$title")"); EVIDENCES+=("$(clean "$evidence")"); FIXES+=("$(clean "$fix")"); REFS+=("$(clean "$ref")")
  [[ $level == Informational ]] && assessment_type=inventory
  case "$id" in
    SYS-001|SYS-002|SYS-100|SYS-101|SYS-102|AUTH-012|AUTH-014|AUTH-016|SSH-017|SSH-109|SSH-110|NET-001|NET-006|NET-10[1-6]|DNS-101|DNS-102|FW-09[0-3]|FW-110|FW-111|FW-112|FW-113|FW-115|PATCH-009|PATCH-013|PATCH-103|FS-012|FS-016|FS-090|FS-2[0-9]5|FS-2[1-4]0|FS-250|FS-260|FS-261|PAM-11[0-8]|PAM-12[0-4]|SUDO-11[0-3]|MAC-003|MAC-005|MAC-011|MAC-012|MAC-014|PERSIST-007|PERSIST-012|PERSIST-015|PERSIST-106|PERSIST-107|SYSTEMD-101|SYSTEMD-102|SYSTEMD-104|SYSTEMD-105|KERNEL-020|BOOT-101|LOG-104|LOG-105|LOG-107|TIME-101|RESOURCE-10[1-3]|PROC-11[0-2]|CLOUD-101|BACKUP-006|BACKUP-101|BACKUP-102|CRYPTO-101|DOCKER-113|PODMAN-113|DOCKER-116|PODMAN-116) assessment_type=inventory;;
    AUTH-013|SSH-012|FW-004|FW-005|FW-006|NET-107|NET-108|PATCH-011|PATCH-012|PATCH-109|PATCH-110|PERSIST-008|PERSIST-011|LOG-005|LOG-108|BOOT-003|BOOT-104|BACKUP-002|BACKUP-003|BACKUP-005|CLOUD-102|TLS-105|DOCKER-117|PODMAN-117) assessment_type=manual_evidence_required;;
  esac
  [[ $legacy == *-COVERAGE ]] && assessment_type=coverage
  ASSESSMENT_TYPES+=("$assessment_type")
  LEVELS+=("$level"); BENCHMARKS+=("$bench"); EXPECTEDS+=("$(clean "$expected")"); ACTUALS+=("$(clean "$evidence")")
  DESCRIPTIONS+=("$(clean "$title. Evaluates the stated scope only; exclusions and missing evidence remain explicit.")")
  RISKS+=("$(clean "$risk")"); EVALUATIONS+=("$(clean "$evalstr")")
}
control() {
  local id=$1 cat=$2 state=$3 sev=$4 title=$5 actual=$6 expected=$7 fix=$8 level=${9:-'Enterprise Extended'}
  CURRENT_EXPECTED=$expected CURRENT_LEVEL=$level CURRENT_EVALUATION="${FUNCNAME[1]:-control}: $expected"
  emit "$id" "$cat" "$state" "$sev" 1 "$title" "$actual" "$fix"
  CURRENT_EXPECTED='' CURRENT_LEVEL='' CURRENT_EVALUATION=''
}
# Only known numeric/key fields from os-release are consumed; never source/eval it.
os_field() {
  awk -v k="$2" 'index($0,k"=")==1 {v=substr($0,length(k)+2); if(v~/^".*"$/ || v~/^\047.*\047$/)v=substr(v,2,length(v)-2); print v; exit}' "$1" 2>/dev/null
}
detect_os() {
  local file=${1:-/etc/os-release} like major
  DISTRO_ID=$(os_field "$file" ID); DISTRO_ID=${DISTRO_ID:-unknown}
  DISTRO_VERSION=$(os_field "$file" VERSION_ID); DISTRO_VERSION=${DISTRO_VERSION:-unknown}
  like=$(os_field "$file" ID_LIKE); major=${DISTRO_VERSION%%.*}
  DISTRO_FAMILY=Unknown PACKAGE_MANAGER=unknown TARGET_SUPPORT=unsupported
  case "$DISTRO_ID" in ubuntu|debian) DISTRO_FAMILY=Debian; PACKAGE_MANAGER=APT;;
    rhel|rocky|almalinux|ol|centos) DISTRO_FAMILY=RHEL; PACKAGE_MANAGER=DNF;;
    opensuse-leap|sles|sles_sap) DISTRO_FAMILY=SUSE; PACKAGE_MANAGER=ZYPPER;;
    *) case " $like " in *' debian '*) DISTRO_FAMILY=Debian; PACKAGE_MANAGER=APT;; *' rhel '*|*' fedora '*) DISTRO_FAMILY=RHEL; PACKAGE_MANAGER=DNF;; *' suse '*|*' opensuse '*) DISTRO_FAMILY=SUSE; PACKAGE_MANAGER=ZYPPER;; esac;; esac
  case "$DISTRO_ID:$DISTRO_VERSION" in ubuntu:20.04|ubuntu:22.04|ubuntu:24.04|ubuntu:26.04|debian:11|debian:12|debian:13) TARGET_SUPPORT=partial;; esac
  case "$DISTRO_ID:$major" in rhel:[89]|rhel:10|rocky:[89]|rocky:10|almalinux:[89]|almalinux:10|ol:[89]|ol:10|sles:*|sles_sap:*|opensuse-leap:*) TARGET_SUPPORT=partial;;
    centos:9|centos:10) if [[ $(os_field "$file" NAME) == *Stream* ]]; then TARGET_SUPPORT=partial; fi;; esac
  if [[ $DISTRO_FAMILY == RHEL ]] && ! have dnf && have yum; then PACKAGE_MANAGER=YUM; fi
}
audit_enterprise_identity() {
  detect_os
  ARCH=$(uname -m); UPTIME_SECONDS=$(awk '{printf "%d",$1}' /proc/uptime 2>/dev/null || printf 0)
  ((EUID==0)) && AUDIT_MODE=ROOT || AUDIT_MODE=LIMITED
  snapshot primary ip -o route get 192.0.2.1 # routing lookup only; no packet is sent
  if ok primary; then PRIMARY_IP=$(awk '{for(i=1;i<NF;i++)if($i=="src"){print $(i+1);exit}}' "$TMP/primary"); fi
  PRIMARY_IP=${PRIMARY_IP:-unknown}
  control SYS-100 System INFO LOW 'Distribution adapter' "$DISTRO_ID $DISTRO_VERSION; family=$DISTRO_FAMILY; manager=$PACKAGE_MANAGER; validation=$TARGET_SUPPORT" 'Recognized target distribution; runtime evidence separately reported' 'Consult the version-specific validation matrix'
  control SYS-101 System INFO LOW 'CPU architecture and virtualization' "$ARCH; $ENVIRONMENT" 'Record actual execution context' 'Assess container host and guest separately'
  if [[ $TARGET_SUPPORT == unsupported ]]; then
    control SYS-102 System UNKNOWN HIGH 'Distribution support coverage' 'Release outside tested target matrix; generic checks only' 'Validated distribution/version adapter' 'Validate vendor commands and lifecycle before relying on this report'
  else control SYS-102 System INFO LOW 'Distribution support coverage' 'Adapter implemented; detection fixtures are not a full OS integration test' 'Read distribution validation matrix' 'Perform staging validation before broad rollout'; fi
}
colors() {
  RESET='' BOLD='' RED='' GREEN='' YELLOW='' CYAN='' GRAY='' ORANGE=''
  [[ ${ORIGINAL_LOCALE^^} == *UTF* ]] && UNICODE=1 || UNICODE=0
  [[ ${TERM:-dumb} == dumb ]] && UNICODE=0
  if [[ $COLOR == always || ( $COLOR == auto && -t 1 && ${TERM:-dumb} != dumb && ! -v NO_COLOR ) ]]; then
    local n=0; have tput && n=$(tput colors 2>/dev/null || printf 0)
    if [[ $COLOR == always ]] || ((n>=8)); then RESET=$'\e[0m'; BOLD=$'\e[1m'; RED=$'\e[31m'; GREEN=$'\e[32m'; YELLOW=$'\e[33m'; CYAN=$'\e[36m'; GRAY=$'\e[37m'; ORANGE=$YELLOW; ((n>=256)) && ORANGE=$'\e[38;5;208m'; fi
  fi
  return 0
}
bar() { local score=$1 width=${2:-24} i; ((score<0)) && score=0; for ((i=0;i<width;i++)); do if ((i<score*width/100)); then ((UNICODE)) && printf '█' || printf '#'; else ((UNICODE)) && printf '░' || printf '.'; fi; done; }
rule() { local i; printf '%s' "$GRAY"; for ((i=0;i<$1;i++)); do ((UNICODE)) && printf '─' || printf '-'; done; printf '%s\n' "$RESET"; }
# Redact comment/string/description fields in inventory at collection boundary.
safe_inventory() {
  local name=$1
  case "$name" in
    nft) sed -E 's/comment[[:space:]]+"[^"]*"/comment "[REDACTED]"/g' "$TMP/$name" | head -n200 | cut -c1-240;;
    ipt|ip6t) sed -E 's/--comment[[:space:]]+("[^"]*"|[^ ]+)/--comment "[REDACTED]"/g' "$TMP/$name" | head -n200 | cut -c1-240;;
    services|enabled|timers|failed) awk '{print $1}' "$TMP/$name" | head -n200;;
    *) snippet "$name" 200;; esac
}
calculate() {
  local i cat state w earned totalden=0 totalknown=0 totalearn=0 bden=0 bknown=0 bpass=0 aden=0 aall=0 aearn=0 tknown=0 tall=0 crit=0
  COUNTS=(); CAT_DEN=(); CAT_EARN=(); CAT_KNOWN=(); CAT_SCORE=(); CAT_COVER=()
  TOTAL=${#IDS[@]}; THREAT_COUNT=0 THREAT_SCORE=100 CAP=100
  for ((i=0;i<TOTAL;i++)); do
    cat=${CATS[i]}; state=${STATES[i]}; w=${WEIGHTS[i]}
    COUNTS[$state]=$(( ${COUNTS[$state]:-0}+1 ))
    if [[ $state == FAIL || $state == WARN ]]; then
      COUNTS[${SEVS[i]}]=$(( ${COUNTS[${SEVS[i]}]:-0}+1 ))
      [[ ${SEVS[i]} == CRITICAL ]] && crit=$((crit+1))
      if [[ ${LEVELS[i]} == 'Threat Indicator' ]]; then THREAT_COUNT=$((THREAT_COUNT+1)); THREAT_SCORE=$((THREAT_SCORE-w*2)); fi
    fi
    [[ $state == NA || $state == INFO ]] && continue
    totalden=$((totalden+w)); CAT_DEN[$cat]=$(( ${CAT_DEN[$cat]:-0}+w ))
    if [[ ${BENCHMARKS[i]} == CIS-aligned ]]; then bden=$((bden+1)); [[ $state != UNKNOWN ]] && bknown=$((bknown+1)); [[ $state == PASS ]] && bpass=$((bpass+1)); fi
    case "$cat" in Network|Firewall|SSH|Containers) aall=$((aall+w));; esac
    if [[ ${LEVELS[i]} == 'Threat Indicator' ]]; then tall=$((tall+w)); [[ $state != UNKNOWN ]] && tknown=$((tknown+w)); fi
    [[ $state == UNKNOWN ]] && continue
    totalknown=$((totalknown+w)); CAT_KNOWN[$cat]=$(( ${CAT_KNOWN[$cat]:-0}+w ))
    case "$state" in PASS) earned=$((w*2));; WARN) earned=$w;; *) earned=0;; esac
    totalearn=$((totalearn+earned)); CAT_EARN[$cat]=$(( ${CAT_EARN[$cat]:-0}+earned ))
    case "$cat" in Network|Firewall|SSH|Containers) aden=$((aden+w)); aearn=$((aearn+earned));; esac
  done
  for cat in "${CAT_ORDER[@]}"; do
    if (( ${CAT_KNOWN[$cat]:-0}>0 )); then CAT_SCORE[$cat]=$((100*${CAT_EARN[$cat]:-0}/(2*CAT_KNOWN[$cat]))); else CAT_SCORE[$cat]=-1; fi
    if (( ${CAT_DEN[$cat]:-0}>0 )); then CAT_COVER[$cat]=$((100*${CAT_KNOWN[$cat]:-0}/CAT_DEN[$cat])); else CAT_COVER[$cat]=0; fi
  done
  RAW_SCORE=0 COVERAGE=0 COMPLIANCE=0 COMPLIANCE_COVERAGE=0 ATTACK_SCORE=0
  ((totalknown>0)) && RAW_SCORE=$((100*totalearn/(2*totalknown)))
  ((totalden>0)) && COVERAGE=$((100*totalknown/totalden))
  ((bknown>0)) && COMPLIANCE=$((100*bpass/bknown))
  ((bden>0)) && COMPLIANCE_COVERAGE=$((100*bknown/bden))
  ATTACK_COVERAGE=0 THREAT_COVERAGE=0
  ((aall>0)) && ATTACK_COVERAGE=$((100*aden/aall))
  ((tall>0)) && THREAT_COVERAGE=$((100*tknown/tall))
  ((aden>0)) && ATTACK_SCORE=$((100*aearn/(2*aden)))
  case "$crit" in 0) CAP=100;; 1) CAP=$CAP_ONE;; 2) CAP=$CAP_TWO;; *) CAP=$CAP_THREE;; esac
  SCORE=$RAW_SCORE; ((SCORE>CAP)) && SCORE=$CAP; ((THREAT_SCORE<0)) && THREAT_SCORE=0
  if ((crit>0)); then VERDICT='CRITICAL ACTION REQUIRED'
  elif (( ${COUNTS[HIGH]:-0}>0 )); then VERDICT='HIGH RISK FINDINGS'
  elif ((COVERAGE<80)); then VERDICT='INCOMPLETE ASSESSMENT'
  elif ((SCORE>=85)); then VERDICT='STRONG OBSERVED BASELINE'
  else VERDICT='IMPROVEMENT REQUIRED'; fi
  # Unknown-only reports must never imply a measured zero or a clean threat score.
  ASSESSED_WEIGHT=$totalknown; COMPLIANCE_KNOWN=$bknown; ATTACK_KNOWN=$aden
  return 0
}
html_metadata() {
  local i=$1
  printf '<p class="evidence"><strong>%s</strong> · %s · %s<br>Expected: %s<br>Actual: %s<br>Risk: %s<br>Evaluation: %s</p>' "${IDS[i]}" "${LEVELS[i]}" "${BENCHMARKS[i]}" "$(html_escape "${EXPECTEDS[i]}")" "$(html_escape "${ACTUALS[i]}")" "$(html_escape "${RISKS[i]}")" "$(html_escape "${EVALUATIONS[i]}")"
}
export_json() {
  local i cat first=1 name state
  printf '{"schema_version":2,"tool_version":'; json_string "$VERSION"
  printf ',"demo":%s,"audit":{"timestamp":' "$([[ $DEMO == 1 ]] && printf true || printf false)"; json_string "$AUDIT_TIME"
  printf ',"mode":'; json_string "$MODE"; printf ',"privilege":'; json_string "$AUDIT_MODE"; printf ',"profile":'; json_string "$PROFILE"
  printf ',"read_only":true,"network_probes":false,"official_cis_certification":false},"host":{"hostname":'; json_string "$HOST"
  printf ',"os":'; json_string "$OS"; printf ',"distro_id":'; json_string "$DISTRO_ID"; printf ',"distro_version":'; json_string "$DISTRO_VERSION"
  printf ',"distro_family":'; json_string "$DISTRO_FAMILY"; printf ',"package_manager":'; json_string "$PACKAGE_MANAGER"; printf ',"kernel":'; json_string "$KERNEL"
  printf ',"architecture":'; json_string "$ARCH"; printf ',"virtualization":'; json_string "$ENVIRONMENT"; printf ',"primary_ip":'; json_string "$PRIMARY_IP"
  printf ',"uptime_seconds":%d,"mac":' "$UPTIME_SECONDS"; json_string "$MAC_SUMMARY"; printf ',"firewall":'; json_string "$FIREWALL_SUMMARY"; printf '}'
  # v1 compatibility aliases; canonical schema is audit/host/scores/summary/categories/findings.
  printf ',"hostname":'; json_string "$HOST"; printf ',"os":'; json_string "$OS"; printf ',"kernel":'; json_string "$KERNEL"
  printf ',"distro_family":'; json_string "$DISTRO_FAMILY"; printf ',"timestamp":'; json_string "$AUDIT_TIME"
  printf ',"score":%d,"raw_score":%d,"score_cap":%d,"coverage_percent":%d' "$SCORE" "$RAW_SCORE" "$CAP" "$COVERAGE"
  printf ',"scores":{"security":'; if (( ${ASSESSED_WEIGHT:-0}>0 )); then printf '%d' "$SCORE"; else printf null; fi
  printf ',"raw_security":%d,"critical_cap":%d,"coverage_percent":%d,"cis_aligned":' "$RAW_SCORE" "$CAP" "$COVERAGE"
  if (( ${COMPLIANCE_KNOWN:-0}>0 )); then printf '%d' "$COMPLIANCE"; else printf null; fi
  printf ',"cis_coverage_percent":%d,"official_cis":null,"attack_surface":' "$COMPLIANCE_COVERAGE"
  if (( ${ATTACK_KNOWN:-0}>0 )); then printf '%d' "$ATTACK_SCORE"; else printf null; fi
  printf ',"attack_coverage_percent":%d,"threat_coverage_percent":%d,"threat_score":%d,"threat_indicator_count":%d,"threat_clearance":null},"summary":{"total_checks":%d,"verdict":' "$ATTACK_COVERAGE" "$THREAT_COVERAGE" "$THREAT_SCORE" "$THREAT_COUNT" "$TOTAL"; json_string "$VERDICT"
  for state in CRITICAL HIGH MEDIUM LOW PASS WARN FAIL UNKNOWN NA INFO; do printf ','; json_string "$state"; printf ':%d' "${COUNTS[$state]:-0}"; done
  printf '},"categories":['
  for cat in "${CAT_ORDER[@]}"; do
    ((first)) || printf ','; first=0
    printf '{"name":'; json_string "$cat"; printf ',"score":'; if (( ${CAT_SCORE[$cat]:--1}>=0 )); then printf '%d' "${CAT_SCORE[$cat]}"; else printf null; fi
    printf ',"coverage":%d,"applicable_weight":%d,"assessed_weight":%d}' "${CAT_COVER[$cat]:-0}" "${CAT_DEN[$cat]:-0}" "${CAT_KNOWN[$cat]:-0}"
  done
  printf '],"findings":['
  for ((i=0;i<TOTAL;i++)); do
    ((i>0)) && printf ','
    printf '{"id":'; json_string "${IDS[i]}"; printf ',"check_id":'; json_string "${IDS[i]}"; printf ',"legacy_id":'; json_string "${LEGACY_IDS[i]}"
    printf ',"assessment_type":'; json_string "${ASSESSMENT_TYPES[i]}"; printf ',"title":'; json_string "${TITLES[i]}"; printf ',"category":'; json_string "${CATS[i]}"; printf ',"status":'; json_string "$(public_status "${STATES[i]}" "${SEVS[i]}")"
    printf ',"state":'; json_string "${STATES[i]}"; printf ',"severity":'; json_string "${SEVS[i]}"; printf ',"weight":%d,"benchmark":' "${WEIGHTS[i]}"; json_string "${BENCHMARKS[i]}"
    printf ',"benchmark_section":null,"benchmark_version":null,"mapping_verified":false,"level":'; json_string "${LEVELS[i]}"
    printf ',"applicable_os":'; json_string "$DISTRO_FAMILY"; printf ',"description":'; json_string "${DESCRIPTIONS[i]}"; printf ',"risk":'; json_string "${RISKS[i]}"
    printf ',"evaluation":'; json_string "${EVALUATIONS[i]}"; printf ',"expected":'; json_string "${EXPECTEDS[i]}"; printf ',"actual":'; json_string "${ACTUALS[i]}"; printf ',"expected_state":'; json_string "${EXPECTEDS[i]}"; printf ',"actual_state":'; json_string "${ACTUALS[i]}"
    printf ',"evidence":'; json_string "${EVIDENCES[i]}"; printf ',"recommendation":'; json_string "${FIXES[i]}"; printf '}'
  done
  printf '],"controls":[' # compact legacy projection
  for ((i=0;i<TOTAL;i++)); do
    ((i>0)) && printf ','; printf '{"id":'; json_string "${IDS[i]}"; printf ',"state":'; json_string "${STATES[i]}"; printf ',"title":'; json_string "${TITLES[i]}"; printf ',"evidence":'; json_string "${EVIDENCES[i]}"; printf '}'
  done
  printf '],"inventory":{'; first=1
  for name in ip routes routes6 sockets connections ufw nft ipt ip6t firewalld services enabled timers processes df dfi memory folderusage sandbox logins; do
    [[ -f $TMP/$name ]] || continue; ((first)) || printf ','; first=0; json_string "$name"; printf ':'; json_string "$(inventory_text "$name")"
  done
  printf '},"method":"Observed severity-weighted score: PASS=1, WARNING=0.5, HIGH/CRITICAL failures=0; UNKNOWN excluded from earned denominator and lowers coverage; INFO and NOT_APPLICABLE excluded. CIS-aligned score is pass/assessed selected aligned controls. No official benchmark claim."}\n'
}

# Adapter boundary. No package downloads, refreshes or transactions.
dnf_snapshot() {
  local name=$1; shift
  local pm=dnf; [[ $PACKAGE_MANAGER == YUM ]] && pm=yum
  snapshot "$name" "$pm" --cacheonly --noplugins --setopt="logdir=$TMP" --setopt="persistdir=$TMP/dnf-state" --setopt=logfilelevel=0 "$@"
}
audit_rpm_patches() {
  local rc n
  dnf_snapshot rpm_updates check-update
  rc=$(<"$TMP/rpm_updates.rc")
  if [[ $rc == 0 || $rc == 100 ]]; then
    n=$(awk '$1~/\.(x86_64|noarch|aarch64|i686|s390x|ppc64le)$/ && NF>=3{n++} END{print n+0}' "$TMP/rpm_updates")
    if [[ $rc == 100 && $n == 0 ]]; then control PATCH-001 Patching UNKNOWN MEDIUM 'Pending RPM upgrades' 'Updates indicated; unrecognized output format' 'Parseable cached package candidates' 'Review DNF output locally'
    else control PATCH-001 Patching "$([[ $n == 0 ]] && printf PASS || printf WARNING)" MEDIUM 'Pending RPM upgrades' "Cached upgrade candidates=$n; no refresh; exclusions/plugins may affect completeness" 'No pending approved updates in current cache' 'Refresh cache separately and review maintenance updates'; fi
  else control PATCH-001 Patching UNKNOWN HIGH 'Pending RPM upgrades' "Cache-only query failed; exit=$rc" 'Readable complete cache' 'Check repository metadata and package manager outside this audit'; fi
  dnf_snapshot rpm_security updateinfo list --security --available
  if ok rpm_security; then
    n=$(awk 'tolower($0)~/security/ {n++} END{print n+0}' "$TMP/rpm_security")
    if ((n>0)); then control PATCH-002 Patching HIGH HIGH 'Security advisories pending' "Security advisory/package rows=$n; not unique CVEs; cache-only" 'No pending applicable vendor security advisories' 'Prioritize validated vendor advisories'
    else control PATCH-002 Patching UNKNOWN HIGH 'Security advisories pending' 'No security rows returned; advisory availability/freshness unverified' 'Current complete updateinfo for all enabled repositories' 'Validate vendor advisory metadata; empty output is not proof of patch compliance'; fi
  else control PATCH-002 Patching UNKNOWN HIGH 'Security advisories pending' 'updateinfo unavailable or cache incomplete' 'Readable current updateinfo' 'Use vendor-supported security metadata'; fi
  dnf_snapshot rpm_repos repolist --enabled
  control PATCH-103 Patching "$(ok rpm_repos && printf INFO || printf UNKNOWN)" LOW 'Enabled RPM repositories' "Readable=$(ok rpm_repos && printf yes || printf no); repository URLs and credentials withheld" 'Enabled approved repositories with usable cache' 'Check disabled security channels and entitlement separately'
  pattern_check PATCH-105 Patching 'RPM package exclusions' MEDIUM '^[[:space:]]*(exclude|excludepkgs|includepkgs)[[:space:]]*=' /etc/dnf/dnf.conf /etc/yum.conf /etc/yum.repos.d
  pattern_check PATCH-106 Patching 'Repository signature bypass candidates' HIGH '^[[:space:]]*(gpgcheck|repo_gpgcheck)[[:space:]]*=[[:space:]]*0' /etc/dnf/dnf.conf /etc/yum.conf /etc/yum.repos.d
  control PATCH-003 Patching UNKNOWN MEDIUM 'RPM cache freshness' 'A single cache timestamp cannot establish completeness of every repository' 'Successful recent synchronization of all security repositories' 'Validate DNF metadata and subscription status outside this offline audit'
  control PATCH-004 Patching UNKNOWN MEDIUM 'RPM transaction completion' 'No mutation or recovery transaction is executed' 'Healthy package database and completed approved transactions' 'Review DNF transaction history and rpmdb health separately'
  if have needs-restarting; then
    snapshot needsrestart needs-restarting -r; rc=$(<"$TMP/needsrestart.rc")
    case "$rc" in 0) control PATCH-010 Patching PASS MEDIUM 'RPM reboot hint' 'needs-restarting -r returned 0; kernel livepatch coverage separate' 'No reboot hint' 'Maintain approved reboot workflow';;
      1) control PATCH-010 Patching WARNING MEDIUM 'RPM reboot hint' 'needs-restarting -r returned reboot hint' 'No reboot hint' 'Schedule reboot and service validation';;
      *) control PATCH-010 Patching UNKNOWN MEDIUM 'RPM reboot hint' "Command unavailable/failed; exit=$rc" 'Readable reboot assessment' 'Validate plugin and privileges';; esac
  else control PATCH-010 Patching UNKNOWN MEDIUM 'RPM reboot hint' 'needs-restarting missing; DNF plugins deliberately not loaded' 'Vendor reboot hint available' 'Verify kernel and running-library state in maintenance workflow'; fi
  unit_check PATCH-006 Patching dnf-automatic.timer 'Automatic RPM updates scheduler' LOW
}
audit_suse_patches() {
  # --no-refresh alone is not a filesystem read-only guarantee: libzypp may rebuild caches.
  # Prefer a read-only zypper view in a private mount namespace when supported; otherwise UNKNOWN.
  local name cmd n rc can=0
  if have bwrap && have zypper && ((EUID==0)); then
    if run bwrap --ro-bind / / --dev-bind /dev /dev --proc /proc --bind "$TMP" "$TMP" --unshare-net --die-with-parent /usr/bin/true >/dev/null 2>&1; then can=1; fi
  fi
  for name in updates patches repos; do
    case "$name" in updates) cmd=list-updates;; patches) cmd=list-patches;; repos) cmd=repos;; esac
    if ((can)); then
      snapshot "suse_$name" bwrap --ro-bind / / --dev-bind /dev /dev --proc /proc --bind "$TMP" "$TMP" --unshare-net --die-with-parent env ZYPP_LOGFILE="$TMP/zypp.log" zypper --non-interactive --no-refresh "$cmd"
    else : > "$TMP/suse_$name"; printf 125 > "$TMP/suse_$name.rc"; fi
  done
  for name in updates patches; do
    [[ $name == updates ]] && cmd=PATCH-001 || cmd=PATCH-002
    rc=$(<"$TMP/suse_$name.rc")
    if [[ $rc == 0 ]]; then
      if [[ $name == patches ]]; then n=$(awk -F'|' 'tolower($0)~/security/ && NF>3{n++} END{print n+0}' "$TMP/suse_$name")
      else n=$(awk -F'|' 'NF>=5 && $0!~/^[-+ ]+$/ && tolower($0)!~/repository.*name/ {n++} END{print n+0}' "$TMP/suse_$name"); fi
      if ((n>0)); then control "$cmd" Patching WARNING HIGH "SUSE cached $name" "Candidate rows=$n; cache freshness unverified" 'No applicable update/patch candidates' 'Review vendor patches and update policy'
      else control "$cmd" Patching UNKNOWN MEDIUM "SUSE cached $name" 'No candidates observed; complete current repository coverage not established' 'Current complete security metadata' 'Validate repository cache health separately'; fi
    else control "$cmd" Patching UNKNOWN HIGH "SUSE cached $name" "Offline read-only query unavailable; exit=$rc; requires usable cache and supported bwrap mount namespace" 'Read-only cached zypper query' 'Run on staging with cached repositories; audit never falls back to cache-modifying queries'; fi
  done
  control PATCH-103 Patching "$(ok suse_repos && printf INFO || printf UNKNOWN)" LOW 'SUSE repository health' "Read-only list available=$(ok suse_repos && printf yes || printf no); remote reachability not probed" 'Approved enabled repositories and fresh cache' 'Validate repository health in normal patch workflow'
  pattern_check PATCH-106 Patching 'SUSE repository signature bypass' HIGH '^[[:space:]]*(gpgcheck|repo_gpgcheck|pkg_gpgcheck)[[:space:]]*=[[:space:]]*0' /etc/zypp/zypp.conf /etc/zypp/repos.d
  control PATCH-010 Patching UNKNOWN MEDIUM 'SUSE reboot requirement' 'No package transaction status proves running kernel/library freshness' 'Vendor reboot status independently verified' 'Review zypper needs-rebooting in approved maintenance context'
  control PATCH-006 Patching UNKNOWN LOW 'SUSE automated patch execution' 'No universal automatic patch service; central orchestration may apply' 'Verified patch orchestration' 'Validate SUSE Manager or approved scheduler centrally'
}
audit_patching() {
  case "$DISTRO_FAMILY" in Debian) audit_apt;; RHEL) audit_rpm_patches;; SUSE) audit_suse_patches;; *) control PATCH-001 Patching UNKNOWN HIGH 'Patch adapter' 'No supported package adapter' 'Recognized package manager' 'Use vendor tooling';; esac
  local n path end now status val
  if [[ $DISTRO_FAMILY == Debian ]]; then
    if [[ -f /run/reboot-required ]]; then control PATCH-010 Patching WARNING MEDIUM 'Vendor reboot marker' "Marker present; mtime=$(stat -c %y /run/reboot-required 2>/dev/null)" 'No outstanding reboot hint' 'Schedule approved reboot and service checks'
    else control PATCH-010 Patching INFO LOW 'Vendor reboot marker' 'Marker absent; marker support is package-dependent' 'No outstanding reboot hint' 'Absence does not prove running libraries or kernel are current'; fi
    pattern_check PATCH-104 Patching 'APT error log indicators' MEDIUM '(^E:|Failed to fetch|NO_PUBKEY|not signed)' /var/log/apt/term.log /var/log/unattended-upgrades/unattended-upgrades.log
    unit_check PATCH-107 Patching apt-daily-upgrade.timer 'APT automatic upgrade timer' LOW
  fi
  snapshot installedkernels find /boot -maxdepth 1 -type f -name 'vmlinuz-*' -printf '%f\n'
  if ok installedkernels; then
    n=$(wc -l < "$TMP/installedkernels")
    control PATCH-013 Patching INFO LOW 'Installed versus running kernel inventory' "Running=$KERNEL; images=$n; $(snippet installedkernels 8); ordering across flavors is not reliable" 'Running approved kernel for its vendor/flavor' 'Compare package EVR within kernel flavor; Oracle UEK/RHCK and livepatch require context'
    if grep -qxF "vmlinuz-$KERNEL" "$TMP/installedkernels"; then control PATCH-108 Patching PASS LOW 'Running kernel image present' "vmlinuz-$KERNEL is present under /boot" 'Running kernel image available on host boot volume' 'Validate actual boot loader and livepatch status'
    else control PATCH-108 Patching UNKNOWN LOW 'Running kernel image present' 'No matching /boot image; expected in containers or separate boot mounts' 'Inspect host kernel package context' 'Review on actual host rather than container namespace'; fi
  else control PATCH-013 Patching UNKNOWN LOW 'Kernel image inventory' 'Boot volume not readable' 'Readable boot volume' 'Validate kernel inventory with host administrator'; fi
  end=$(os_field /etc/os-release SUPPORT_END); now=$(date -u +%F)
  if [[ $end =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]]; then
    status=PASS; [[ $now > $end ]] && status=WARNING
    control PATCH-011 Patching "$status" HIGH 'Vendor advertised support end' "SUPPORT_END=$end; audit date=$now; extended entitlement unverified" 'Current vendor security maintenance coverage' 'Validate entitlement before classifying complete end of support'
  else control PATCH-011 Patching UNKNOWN HIGH 'OS lifecycle and subscription' 'Vendor support end/extended entitlement not established from local data' 'Supported release and active required entitlement' 'Verify current vendor lifecycle and ESM/ELS/LTSS coverage; version detection is not EOL proof'; fi
  control PATCH-012 Patching UNKNOWN HIGH 'Package and kernel CVE exposure' 'No authoritative current vulnerability feed evaluated; vendor backports prevent version-only conclusions' 'Current authenticated vendor vulnerability assessment' 'Run vendor OVAL/security tooling separately'
  if [[ $DISTRO_FAMILY == Debian ]] && have dpkg-query; then
    local pkg version pkgstate flavor installedver='' runningver='' latestpkg='' same=0
    flavor=${KERNEL#*-}; flavor=${flavor#*-}
    snapshot kernelpackages dpkg-query -W '-f=${Package}|${Version}|${db:Status-Status}\n' 'linux-image-[0-9]*'
    if ok kernelpackages; then
      while IFS='|' read -r pkg version pkgstate; do
        [[ $pkgstate == installed && $pkg == linux-image-*"-$flavor" ]] || continue
        same=$((same+1)); [[ $pkg == "linux-image-$KERNEL" ]] && runningver=$version
        if [[ -z $installedver ]] || run dpkg --compare-versions "$version" gt "$installedver"; then installedver=$version; latestpkg=$pkg; fi
      done < "$TMP/kernelpackages"
      if [[ -n $installedver && -n $runningver ]]; then
        status=PASS; run dpkg --compare-versions "$runningver" lt "$installedver" && status=WARNING
        control PATCH-111 Patching "$status" MEDIUM 'Running versus newest installed kernel package of same flavor' "Running package version=$runningver; newest=$latestpkg version=$installedver; same-flavor packages=$same; livepatch not assessed" 'Running kernel package is not older than installed approved same-flavor kernel' 'Validate livepatch and schedule controlled reboot if appropriate'
      else control PATCH-111 Patching UNKNOWN MEDIUM 'Running versus newest installed same-flavor kernel' 'Running kernel package or comparable flavor not found; common inside containers' 'Comparable installed kernel package for host flavor' 'Inspect the actual host boot/package namespace'; fi
    else control PATCH-111 Patching UNKNOWN MEDIUM 'Same-flavor kernel comparison' 'No readable matching kernel packages' 'Vendor package version evidence' 'Inspect host kernel packages'; fi
  else control PATCH-111 Patching UNKNOWN MEDIUM 'Running versus newest installed kernel EVR' 'RPM kernel flavors (UEK/RHCK/default) require validated EVR/boot selection correlation; no cross-flavor numeric guess made' 'Approved same-flavor installed kernel and running kernel match' 'Use vendor kernel EVR, boot selection and livepatch status'; fi
  control PATCH-109 Patching UNKNOWN CRITICAL 'Overdue critical security advisories' 'No validated severity and publication-age correlation; no fabricated CVE verdict' 'No overdue applicable critical advisory' 'Correlate current vendor advisory dates and installed package EVR'
  control PATCH-110 Patching UNKNOWN MEDIUM 'Repository approval and reachability' 'No network requests or organizational repository allowlist used' 'All repositories approved, reachable and signature-verified' 'Validate repository origin and live health outside this audit'
}
audit_integrity() {
  local tool=dpkg package n=0 rc=0 diffs=0 done_count=0 failures=0
  local -a packages=(bash coreutils sudo openssh-server libc6 systemd)
  if [[ $DISTRO_FAMILY != Debian ]]; then tool=rpm; packages=(bash coreutils sudo openssh-server glibc systemd); fi
  if ! have "$tool"; then control PKG-001 Recovery UNKNOWN MEDIUM 'Core package integrity' 'Package verifier unavailable' 'Readable vendor package metadata' 'Use vendor package integrity tooling'; return; fi
  if [[ $MODE == quick ]]; then control PKG-001 Recovery UNKNOWN MEDIUM 'Core package integrity' 'Skipped in quick mode' 'Core package digest comparison' 'Use standard or deep mode'; return; fi
  if [[ $MODE == deep ]]; then
    if [[ $tool == rpm ]]; then snapshot pkgverify rpm -Va --noscripts; else snapshot pkgverify dpkg -V; fi
    rc=$(<"$TMP/pkgverify.rc"); n=$(wc -l < "$TMP/pkgverify"); diffs=$n; done_count=1
    [[ $rc != 0 && ! ( $tool == rpm && $rc == 1 && $n -gt 0 ) ]] && failures=1
  else
    for package in "${packages[@]}"; do
      if [[ $tool == rpm ]]; then run rpm -q "$package" >/dev/null 2>&1 || continue; snapshot pkgverify rpm -V --noscripts "$package"
      else [[ $(run dpkg-query -W '-f=${db:Status-Status}' "$package" 2>/dev/null || true) == installed ]] || continue; snapshot pkgverify dpkg -V "$package"; fi
      rc=$(<"$TMP/pkgverify.rc"); n=$(wc -l < "$TMP/pkgverify"); diffs=$((diffs+n)); done_count=$((done_count+1))
      [[ $rc != 0 && ! ( $tool == rpm && $rc == 1 && $n -gt 0 ) ]] && failures=$((failures+1))
    done
  fi
  if ((diffs>0)); then control PKG-001 Recovery WARNING MEDIUM 'Package integrity differences' "Differences=$diffs; scopes=$done_count; errors=$failures; conffile changes can be expected; output withheld" 'Vendor package content matches approved baseline' 'Review changes against approved configuration; differences are not malware proof'
  elif ((failures>0 || done_count==0)); then control PKG-001 Recovery UNKNOWN MEDIUM 'Package integrity differences' "Scopes=$done_count; errors=$failures" 'Successful complete verification' 'Review verifier diagnostics locally'
  else control PKG-001 Recovery PASS MEDIUM 'Package integrity differences' "No differences in $done_count selected scope(s); mode=$MODE; local metadata may be tampered" 'Vendor package content matches approved baseline' 'Use independent signed baseline for forensic assurance'; fi
}
audit_mac() {
  local val configured policy state n field id expected_mac=AppArmor
  [[ $DISTRO_FAMILY == RHEL ]] && expected_mac=SELinux
  snapshot selinux getenforce
  if ok selinux; then
    val=$(snippet selinux 1); MAC_SUMMARY="SELinux $val"
    [[ $val == Enforcing || $val == Permissive ]] && expected_mac=SELinux
    case "$val" in Enforcing) state=PASS;; Permissive) state=HIGH;; Disabled) state=HIGH; [[ $expected_mac == SELinux && $ENVIRONMENT != docker && $ENVIRONMENT != lxc ]] && state=CRITICAL;; *) state=UNKNOWN;; esac
    control MAC-001 MAC "$state" HIGH 'SELinux runtime enforcement' "$val; expected MAC=$expected_mac" 'Enforcing when SELinux is the selected host policy' 'Validate workload labels and approved exceptions before enabling enforcement' 'CIS Level 1'
    configured=$(awk -F= '$1=="SELINUX"{print $2}' /etc/selinux/config 2>/dev/null || true)
    policy=$(awk -F= '$1=="SELINUXTYPE"{print $2}' /etc/selinux/config 2>/dev/null || true)
    control MAC-002 MAC "$([[ $configured == enforcing ]] && printf PASS || { [[ $configured == permissive || $configured == disabled ]] && printf HIGH || printf UNKNOWN; })" MEDIUM 'SELinux configured mode' "Configured=${configured:-unavailable}; runtime=$val" 'Configured enforcing and runtime consistent' 'Review boot policy and approved exceptions' 'CIS Level 1'
    control MAC-003 MAC INFO LOW 'SELinux policy type' "Policy=${policy:-unavailable}" 'Approved policy such as targeted or mls' 'Validate policy and service domain transitions'
    snapshot avc ausearch -m AVC,USER_AVC -ts recent
    n=$(grep -Ec '^type=(AVC|USER_AVC)' "$TMP/avc" || true)
    if ((n>0)); then state=WARNING; elif ok avc; then state=PASS; else state=UNKNOWN; fi
    control MAC-004 MAC "$state" LOW 'Recent AVC denial indicators' "Recent records=$n; audit records and command lines withheld; absence may reflect incomplete audit logging" 'No unexplained denial pattern' 'Review AVCs locally; do not automatically generate allow rules'
    snapshot seprocess ps -eZ
    if ok seprocess; then n=$(grep -Ec 'unconfined_service_t' "$TMP/seprocess" || true); state=INFO; else n=0; state=UNKNOWN; fi
    control MAC-005 MAC "$state" LOW 'Unconfined SELinux services' "unconfined_service_t process rows=$n; user sessions are a different policy concern" 'Service domains comply with approved SELinux policy' 'Review unconfined service exceptions'
  else
    [[ $expected_mac == SELinux ]] && state=UNKNOWN || state=NA
    for id in 001 002 003 004 005; do control "MAC-$id" MAC "$state" HIGH "SELinux control $id unavailable" 'SELinux inspection tool unavailable; not treated as disabled' 'Vendor MAC observation available when applicable' 'Inspect selected MAC implementation'; done
  fi
  snapshot aa aa-status
  if ok aa; then
    MAC_SUMMARY="${MAC_SUMMARY/unknown/} AppArmor active"
    control MAC-010 MAC PASS MEDIUM 'AppArmor enabled' 'aa-status succeeded' 'AppArmor module enabled and accessible' 'Maintain service-specific profile coverage' 'CIS Level 1'
    for field in loaded enforce complain unconfined; do
      case "$field" in loaded) id=011; n=$(awk '/profiles are loaded/{print $1}' "$TMP/aa");; enforce) id=012; n=$(awk '/profiles are in enforce mode/{print $1}' "$TMP/aa");; complain) id=013; n=$(awk '/profiles are in complain mode/{print $1}' "$TMP/aa");; unconfined) id=014; n=$(awk '/processes are unconfined/{print $1}' "$TMP/aa");; esac
      state=INFO; [[ ! $n =~ ^[0-9]+$ ]] && state=UNKNOWN
      [[ $field == complain && $n =~ ^[1-9][0-9]*$ ]] && state=WARNING
      control "MAC-$id" MAC "$state" LOW "AppArmor $field inventory" "Count=${n:-unavailable}; aa-status scope only; unprofiled services are not necessarily included" 'Approved service profiles enforced' 'Compare loaded and enforced profiles to service inventory'
    done
  else
    state=UNKNOWN; [[ $expected_mac != AppArmor && ! -e /sys/module/apparmor ]] && state=NA
    val=$(cat /sys/module/apparmor/parameters/enabled 2>/dev/null || true)
    [[ $val == N && $expected_mac == AppArmor && $ENVIRONMENT != docker && $ENVIRONMENT != lxc ]] && state=CRITICAL
    for id in 010 011 012 013 014; do [[ $id != 010 && $state != NA ]] && state=UNKNOWN; control "MAC-$id" MAC "$state" HIGH "AppArmor control $id unavailable" "Inspection unavailable; module enabled=${val:-unknown}; expected MAC=$expected_mac" 'Readable AppArmor policy when applicable' 'Inspect host MAC policy and kernel support'; done
  fi
}
# Helpers do not execute configuration text.
pattern_check() {
  local id=$1 cat=$2 title=$3 sev=$4 pattern=$5; shift 5
  scan_pattern "pattern-$id" "$SCAN_SECONDS" "$pattern" "$@"
  scan_result "pattern-$id" "$id" "$cat" "$title" "$sev" 'Review the matching file and approved policy locally; contents withheld' 1 WARN
}
unit_check() {
  local id=$1 cat=$2 unit=$3 title=$4 sev=$5
  snapshot "unit-$id" systemctl show "$unit" -p LoadState -p ActiveState -p UnitFileState
  if ok "unit-$id"; then
    if grep -qx 'LoadState=not-found' "$TMP/unit-$id"; then control "$id" "$cat" INFO "$sev" "$title" 'Unit not installed; centrally managed alternatives may apply' 'Verified equivalent control or active unit' 'Validate organizational control coverage'
    elif grep -qx 'ActiveState=active' "$TMP/unit-$id"; then control "$id" "$cat" PASS "$sev" "$title" 'Unit active; enablement and event delivery separate' 'Active service/timer' 'Validate operational control health'
    else control "$id" "$cat" WARNING "$sev" "$title" 'Unit exists but inactive' 'Active service or documented alternative' 'Validate intended control and schedule'; fi
  else control "$id" "$cat" UNKNOWN "$sev" "$title" 'systemd state unavailable' 'Readable service state' 'Inspect alternate init or host namespace'; fi
}

audit_ssh_extended() {
  local id key op target sev level val state
  while IFS='|' read -r id key op target sev level; do
    if ! ok ssh; then
      state=UNKNOWN; [[ ! -f /etc/ssh/sshd_config && ! -d /etc/ssh/sshd_config.d ]] && ! have sshd && state=NA
      control "$id" SSH "$state" "$sev" "SSH $key effective policy" 'Effective sshd configuration unavailable' "$key $op $target" 'Inspect effective daemon config with the appropriate Match context' "$level"; continue
    fi
    val=$(awk -v k="$key" '$1==k{print $2;exit}' "$TMP/ssh"); state=UNKNOWN
    if [[ $op == eq && -n $val ]]; then [[ $val == "$target" ]] && state=PASS || state=WARNING
    elif [[ $val =~ ^[0-9]+$ && $target =~ ^[0-9]+$ ]]; then
      state=WARNING
      case "$op" in le) ((val<=target && val>0)) && state=PASS;; ge) ((val>=target)) && state=PASS;; esac
    fi
    control "$id" SSH "$state" "$sev" "SSH $key effective policy" "Observed=${val:-unavailable}; selected sshd scope only" "$key $op $target" 'Review effective policy, workload compatibility and administrative access before remediation' "$level"
  done <<'SSH_EXT'
SSH-101|maxsessions|le|10|LOW|CIS Level 1
SSH-102|logingracetime|le|60|MEDIUM|CIS Level 1
SSH-103|permittunnel|eq|no|MEDIUM|CIS Level 2
SSH-104|clientaliveinterval|ge|1|LOW|Enterprise Extended
SSH-105|clientalivecountmax|le|3|LOW|Enterprise Extended
SSH-106|loglevel|eq|VERBOSE|LOW|CIS Level 2
SSH-107|strictmodes|eq|yes|HIGH|CIS Level 1
SSH-108|permituserrc|eq|no|LOW|CIS Level 2
SSH_EXT
  if ok ssh; then
    val=$(awk '$1=="maxstartups"{print $2}' "$TMP/ssh")
    control SSH-109 SSH INFO LOW 'SSH unauthenticated connection throttling' "maxstartups=$val" 'Approved start:rate:full limit' 'Review DoS limits against legitimate automation'
    val=$(awk '$1~/^(allowusers|allowgroups|denyusers|denygroups)$/ {n++} END{print n+0}' "$TMP/ssh")
    control SSH-110 SSH "$([[ $val == 0 ]] && printf WARNING || printf INFO)" LOW 'SSH explicit identity restrictions' "Restriction directives present=$val; NSS/PAM may add restrictions" 'Documented administrative identity allowlist' 'Validate all effective identity access paths'
  else
    control SSH-109 SSH UNKNOWN LOW 'SSH unauthenticated connection throttling' 'Effective configuration unavailable' 'Approved MaxStartups policy' 'Inspect effective sshd'
    control SSH-110 SSH UNKNOWN LOW 'SSH identity restrictions' 'Effective configuration unavailable' 'Documented administrative identity restrictions' 'Inspect effective sshd'
  fi
}
audit_pam_sudo() {
  local main auth id title pattern sev n key target val state
  case "$DISTRO_FAMILY" in Debian) main=/etc/pam.d/common-password; auth=/etc/pam.d/common-auth;; RHEL) main=/etc/pam.d/system-auth; auth=/etc/pam.d/password-auth;; SUSE) main=/etc/pam.d/common-password; auth=/etc/pam.d/common-auth;; *) main=/etc/pam.d/common-password; auth=/etc/pam.d/common-auth;; esac
  while IFS='|' read -r id title pattern; do
    sev=${pattern##*|}; pattern=${pattern%|*}
    if [[ ! -r $main && ! -r $auth ]]; then state=UNKNOWN; n=0
    else n=$(grep -Ehs "$pattern" "$main" "$auth" | wc -l); [[ $n == 0 ]] && state=WARNING || state=INFO; fi
    control "$id" PAM "$state" "$sev" "$title" "Active reference candidates=$n; includes/external identity order not fully resolved" 'Approved effective PAM stack including provider overrides' 'Review the active PAM include graph and module arguments locally' 'CIS Level 1'
  done <<'PAM_ROWS'
PAM-101|PAM password hashing module|^[[:space:]]*password.*pam_unix\.so|MEDIUM
PAM-102|PAM password history module|^[[:space:]]*password.*(pam_pwhistory\.so|remember=)|MEDIUM
PAM-103|PAM authentication lockout module|^[[:space:]]*auth.*(pam_faillock\.so|pam_tally2\.so)|MEDIUM
PAM-104|PAM root quality override reference|^[[:space:]]*password.*pam_pwquality\.so.*enforce_for_root|MEDIUM
PAM_ROWS
  pattern_check PAM-105 PAM 'PAM nullok authentication candidates' HIGH '^[[:space:]]*[^#[:space:]].*pam_unix\.so.*[[:space:]]nullok([[:space:]]|$)' "$main" "$auth"
  # Values in pwquality.conf can be overridden by module arguments or snippets; never claim effective PASS.
  while IFS='|' read -r id key target; do
    val=$(awk -F= -v k="$key" '$0!~/^[ \t]*#/ {gsub(/[ \t]/,"",$1);if($1==k){gsub(/[ \t]/,"",$2);v=$2}} END{print v}' /etc/security/pwquality.conf 2>/dev/null || true)
    [[ $val =~ ^-?[0-9]+$ ]] || val=''
    control "$id" PAM "$([[ -n $val ]] && printf INFO || printf UNKNOWN)" MEDIUM "Password quality $key baseline" "$key=${val:-unset}; includes and PAM arguments may override" "$target" 'Validate effective pwquality policy including .conf.d and PAM arguments' 'CIS Level 1'
  done <<'PW_ROWS'
PAM-110|minlen|Minimum length >=14 or organizational equivalent
PAM-111|minclass|At least 4 classes or approved alternative
PAM-112|difok|At least 2 changed characters
PAM-113|maxrepeat|Repeated character limit <=3
PAM-114|maxsequence|Monotonic sequence limit <=3
PAM-115|dictcheck|Dictionary checking enabled
PAM-116|usercheck|Username inclusion checking enabled
PAM-117|enforcing|Quality check enforced
PAM-118|retry|Bounded password retry count
PW_ROWS
  for key in deny unlock_time fail_interval even_deny_root root_unlock_time; do
    case "$key" in deny) id=120;; unlock_time) id=121;; fail_interval) id=122;; even_deny_root) id=123;; root_unlock_time) id=124;; esac
    val=$(awk -F= -v k="$key" '$0!~/^[ \t]*#/ {gsub(/[ \t]/,"",$1);if($1==k){gsub(/[ \t]/,"",$2);v=($2==""?"present":$2)}} END{print v}' /etc/security/faillock.conf 2>/dev/null || true)
    [[ $val =~ ^(-?[0-9]+|present)$ ]] || val=''
    control "PAM-$id" PAM "$([[ -n $val ]] && printf INFO || printf UNKNOWN)" LOW "faillock $key configuration" "Value=${val:-unset}; PAM/module defaults can override" 'Organization-approved lockout and recovery policy' 'Review effective authselect/PAM profile and root recovery access'
  done
  while IFS='|' read -r id title pattern; do sev=${pattern##*|}; pattern=${pattern%|*}; pattern_check "$id" Sudo "$title" "$sev" "$pattern" /etc/sudoers /etc/sudoers.d; done <<'SUDO_ROWS'
SUDO-101|Unrestricted sudo ALL command candidates|^[[:space:]]*[^#[:space:]].*=[[:space:]]*(\([^)]*\))?[[:space:]]*(NOPASSWD:[[:space:]]*)?ALL([[:space:],]|$)|MEDIUM
SUDO-102|Wildcard sudo command candidates|^[[:space:]]*[^#[:space:]].*=[^#]*\*|HIGH
SUDO-103|Sudo interpreter/editor delegation candidates|^[[:space:]]*[^#[:space:]].*=[^#]*/(bash|sh|python[0-9.]*|perl|vim|vi|less|find|env)([[:space:],]|$)|MEDIUM
SUDO-104|Sudo disabled authentication candidates|^[[:space:]]*Defaults[^#]*!authenticate|HIGH
SUDO-105|Sudo setenv privilege candidates|^[[:space:]]*[^#[:space:]].*(SETENV:|!env_reset)|MEDIUM
SUDO_ROWS
  for key in use_pty secure_path logfile timestamp_timeout; do
    case "$key" in use_pty) id=110;; secure_path) id=111;; logfile) id=112;; timestamp_timeout) id=113;; esac
    scan_pattern "sudocfg$id" "$SCAN_SECONDS" "^[[:space:]]*Defaults[^#]*$key" /etc/sudoers /etc/sudoers.d
    n=$(tr -cd '\000' < "$TMP/sudocfg$id" | wc -c)
    control "SUDO-$id" Sudo "$(ok "sudocfg$id" && printf INFO || printf UNKNOWN)" LOW "sudo $key directive inventory" "Matching files=$n; no rule bodies disclosed; implicit vendor defaults and include order not resolved" "Approved effective $key policy" 'Review visudo validation and sudo -l for each administrative role' 'CIS Level 1'
  done
}
audit_user_extended() {
  local user pass uid gid gecos home shell mode owner path id label n unavailable count min max warn hash last inactive expire rest
  local missing=0 badowner=0 broad=0 netrc=0 rhosts=0 forward=0 history=0 sshmode=0 inaccessible=0 interactive=0
  while IFS=: read -r user pass uid gid gecos home shell; do
    [[ $shell =~ (nologin|false)$ || ! $uid =~ ^[0-9]+$ || $home != /* ]] && continue
    interactive=$((interactive+1))
    if [[ ! -d $home ]]; then missing=$((missing+1)); continue; fi
    read -r mode owner < <(stat -Lc '%a %u' -- "$home" 2>/dev/null) || { inaccessible=$((inaccessible+1)); continue; }
    [[ $owner != "$uid" ]] && badowner=$((badowner+1))
    (( (8#$mode & 0027)!=0 )) && broad=$((broad+1))
    [[ -e $home/.netrc ]] && netrc=$((netrc+1)); [[ -e $home/.rhosts ]] && rhosts=$((rhosts+1)); [[ -e $home/.forward ]] && forward=$((forward+1))
    for path in "$home/.bash_history" "$home/.zsh_history"; do
      [[ -e $path ]] || continue; mode=$(stat -Lc %a -- "$path" 2>/dev/null) || { inaccessible=$((inaccessible+1)); continue; }
      (( (8#$mode & 0077)!=0 )) && history=$((history+1))
    done
    if [[ -d $home/.ssh ]]; then mode=$(stat -Lc %a -- "$home/.ssh" 2>/dev/null) || { inaccessible=$((inaccessible+1)); continue; }; (( (8#$mode & 0077)!=0 )) && sshmode=$((sshmode+1)); fi
  done < /etc/passwd
  for id in 101 102 103 104 105 106 107 108; do
    case "$id" in 101) n=$missing; label='Interactive user home existence';; 102) n=$badowner; label='Interactive home owner matches account';; 103) n=$broad; label='Interactive home confidentiality baseline';; 104) n=$netrc; label='User .netrc credential-file indicators';; 105) n=$rhosts; label='Legacy .rhosts trust indicators';; 106) n=$forward; label='User mail forwarding file indicators';; 107) n=$history; label='Shell history access permissions';; 108) n=$sshmode; label='User .ssh directory confidentiality';; esac
    if ((n>0)); then control "AUTH-$id" Authentication WARNING MEDIUM "$label" "Candidates=$n; interactive accounts=$interactive; inaccessible=$inaccessible; file contents withheld" 'No unapproved candidate in local interactive accounts' 'Review account policy and access permissions'
    elif ((inaccessible>0 || EUID!=0)); then control "AUTH-$id" Authentication UNKNOWN MEDIUM "$label" 'Limited user-home visibility; requires root for complete coverage' 'Complete local home visibility' 'Rerun as root'
    else control "AUTH-$id" Authentication PASS MEDIUM "$label" "No candidate in $interactive interactive local accounts" 'No unapproved candidate' 'Validate external identity users separately'; fi
  done
  local minbad=0 warnbad=0 inactbad=0 seen=0
  if ((EUID==0)) && [[ -r /etc/shadow ]]; then
    while IFS=: read -r user hash last min max warn inactive expire rest; do
      [[ $hash == '!'* || $hash == '*'* || -z $hash ]] && continue
      seen=$((seen+1)); [[ ! $min =~ ^[0-9]+$ || $min == 0 ]] && minbad=$((minbad+1))
      [[ ! $warn =~ ^[0-9]+$ || $warn -lt 7 ]] && warnbad=$((warnbad+1))
      [[ ! $inactive =~ ^[0-9]+$ || $inactive -gt 30 ]] && inactbad=$((inactbad+1))
    done < /etc/shadow
    unset hash
    for id in 110 111 112; do
      case "$id" in 110) n=$minbad; label='Minimum password age >=1';;111) n=$warnbad;label='Password expiry warning >=7 days';;112) n=$inactbad;label='Post-expiry account inactivity <=30 days';;esac
      control "AUTH-$id" Authentication "$([[ $n == 0 ]] && printf PASS || printf WARNING)" LOW "$label" "Candidates=$n; password-enabled accounts=$seen; no hashes retained" "$label for password-based accounts where aging policy applies" 'Apply organizational credential lifecycle policy; key-only accounts require separate review' 'CIS Level 2'
    done
  else
    for id in 110 111 112; do control "AUTH-$id" Authentication UNKNOWN LOW 'Account aging subcontrol' 'Requires root privileges and readable shadow' 'Readable account lifecycle metadata' 'Rerun as root' 'CIS Level 2'; done
  fi
}
audit_mount_extended() {
  local path id=200 opt target type options mode state info i
  for path in / /boot /boot/efi /home /tmp /var /var/tmp /var/log /var/log/audit /dev/shm; do
    snapshot "mount$id" findmnt -n -o TARGET,FSTYPE,OPTIONS -T "$path"
    target='' type='' options=''; if ok "mount$id"; then read -r target type options < "$TMP/mount$id"; fi
    for opt in separate nodev nosuid noexec metadata; do
      id=$((id+1)); state=UNKNOWN; info='findmnt/stat unavailable'
      if [[ ! -e $path ]]; then state=NA; info='Path absent'
      elif [[ -n $target ]]; then
        info="Containing mount=$target; type=$type; requested=$path"
        case "$opt" in
          separate) if [[ $path == / ]]; then state=INFO; elif [[ $target == "$path" ]]; then state=PASS; else state=WARNING; fi;;
          metadata) state=INFO; info+="; $(stat -Lc 'mode=%a uid=%u gid=%g owner=%U group=%G' -- "$path" 2>/dev/null || printf metadata-unavailable); options=$options";;
          *) if [[ ,$options, == *,$opt,* ]]; then state=PASS; else state=WARNING; fi
            # / and executable system volumes cannot safely require noexec; EFI/nodev semantics differ.
            [[ $path == / || $path == /boot/efi || ( $path == /var && $opt == noexec ) ]] && state=INFO;;
        esac
      fi
      # Preserve original tmp mount IDs; extended registry adds only partition/metadata for them.
      if [[ ( $path == /tmp || $path == /var/tmp || $path == /dev/shm ) && ( $opt == nodev || $opt == nosuid || $opt == noexec ) ]]; then continue; fi
      control "FS-$id" Filesystem "$state" LOW "Mount $path: $opt" "$info" "Approved $opt policy for this specific mount and host role" 'Plan filesystem policy changes after testing service compatibility' "$([[ $opt == metadata ]] && printf 'Enterprise Extended' || printf 'CIS Level 2')"
    done
  done
  snapshot allmounts findmnt -rn -o TARGET,FSTYPE,OPTIONS
  if ok allmounts; then
    info=$(awk '$2~/^(nfs|nfs4|cifs|smb3|sshfs|fuse.sshfs)$/ {print $1" "$2}' "$TMP/allmounts")
    control FS-260 Filesystem INFO LOW 'Network filesystem inventory' "${info:-No known network filesystem types}; these are excluded from file scans" 'Only approved network mounts' 'Validate export/share ACLs and network authentication'
    info=$(awk '$3~/(^|,)ro(,|$)/{n++}END{print n+0}' "$TMP/allmounts")
    control FS-261 Filesystem INFO LOW 'Read-only mounted filesystem inventory' "Read-only mounts=$info" 'Expected mount mutability' 'Investigate unexpected read-only transitions using storage telemetry'
  else control FS-260 Filesystem UNKNOWN LOW 'Filesystem type inventory' 'findmnt failed' 'Readable mount table' 'Inspect host mount namespace'; fi
}
audit_permission_extended() {
  local id path mask sev
  while IFS='|' read -r id path mask sev; do perm_check "$id" Permissions "$path" "$mask" "$sev"; done <<'PERM_ROWS'
PERM-101|/etc/passwd-|0022|HIGH
PERM-102|/etc/group-|0022|HIGH
PERM-103|/etc/shadow-|0037|HIGH
PERM-104|/etc/gshadow-|0037|HIGH
PERM-105|/etc/sudoers.d|0022|HIGH
PERM-106|/etc/cron.d|0022|HIGH
PERM-107|/etc/cron.hourly|0022|HIGH
PERM-108|/etc/cron.daily|0022|HIGH
PERM-109|/etc/cron.weekly|0022|HIGH
PERM-110|/etc/cron.monthly|0022|HIGH
PERM-111|/etc/profile|0022|HIGH
PERM-112|/etc/profile.d|0022|HIGH
PERM-113|/etc/bashrc|0022|HIGH
PERM-114|/etc/bash.bashrc|0022|HIGH
PERM-115|/boot/grub2/grub.cfg|0077|HIGH
PERM-116|/etc/security|0022|HIGH
PERM-117|/etc/pam.d|0022|HIGH
PERM-118|/etc/ssh/sshd_config.d|0022|HIGH
PERM-119|/etc/ld.so.conf|0022|HIGH
PERM-120|/etc/ld.so.conf.d|0022|HIGH
PERM-121|/etc/hosts|0022|MEDIUM
PERM-122|/etc/rsyslog.conf|0022|MEDIUM
PERM-123|/etc/audit/auditd.conf|0027|HIGH
PERM-124|/etc/audit/rules.d|0027|HIGH
PERM_ROWS
  local -a paths=(); for path in /etc/sudoers.d /etc/ssh/sshd_config.d /boot/grub /boot/grub2; do [[ -d $path ]] && paths+=("$path"); done
  if (( ${#paths[@]} )); then
    scan_files extra_perm "$SCAN_SECONDS" "${paths[@]}" -xdev -maxdepth 3 -type f -perm /0022 -print0
    scan_result extra_perm PERM-125 Permissions 'Writable privileged configuration fragments' HIGH 'Review ownership and trusted write access for each fragment'
  else control PERM-125 Permissions NA HIGH 'Privileged configuration fragments' 'No selected directories present' 'Trusted fragment permissions' 'Validate alternate configuration locations'; fi
}
audit_kernel_extended() {
  local id key op expected val sev level state mod loaded disabled resolv
  while IFS='|' read -r id key op expected sev level; do
    val=$(run sysctl -n "$key" 2>/dev/null || true); state=UNKNOWN
    if [[ $val =~ ^-?[0-9]+$ ]]; then
      state=WARNING
      case "$op" in ge) ((val>=expected)) && state=PASS;; eq) ((val==expected)) && state=PASS;; esac
    elif [[ ! -e /proc/sys/${key//.//} ]]; then state=NA; fi
    control "$id" Kernel "$state" "$sev" "Kernel $key" "Observed=${val:-unavailable}" "$op $expected" 'Validate kernel capability and workload requirements before any change' "$level"
  done <<'KERNEL_EXT'
KERNEL-101|fs.protected_fifos|ge|1|MEDIUM|CIS Level 1
KERNEL-102|fs.protected_regular|ge|1|MEDIUM|CIS Level 1
KERNEL-103|kernel.unprivileged_bpf_disabled|ge|1|MEDIUM|Enterprise Extended
KERNEL-104|kernel.perf_event_paranoid|ge|2|MEDIUM|Enterprise Extended
KERNEL-105|user.max_user_namespaces|eq|0|LOW|CIS Level 2
KERNEL-106|net.ipv4.conf.default.rp_filter|ge|1|MEDIUM|CIS Level 1
KERNEL-107|net.ipv4.conf.all.log_martians|eq|1|LOW|CIS Level 1
KERNEL-108|net.ipv4.conf.default.log_martians|eq|1|LOW|CIS Level 1
KERNEL-109|net.ipv4.icmp_echo_ignore_broadcasts|eq|1|MEDIUM|CIS Level 1
KERNEL-110|net.ipv4.icmp_ignore_bogus_error_responses|eq|1|LOW|CIS Level 1
KERNEL-111|net.ipv4.conf.all.secure_redirects|eq|0|MEDIUM|CIS Level 1
KERNEL-112|net.ipv4.conf.default.secure_redirects|eq|0|MEDIUM|CIS Level 1
KERNEL-113|net.ipv6.conf.default.accept_source_route|eq|0|MEDIUM|CIS Level 1
KERNEL-114|net.ipv6.conf.all.accept_ra|eq|0|LOW|CIS Level 2
KERNEL-115|net.ipv6.conf.default.accept_ra|eq|0|LOW|CIS Level 2
KERNEL-116|net.ipv6.conf.all.forwarding|eq|0|LOW|CIS Level 2
KERNEL-117|kernel.sysrq|eq|0|LOW|CIS Level 2
KERNEL-118|kernel.core_uses_pid|eq|1|LOW|Enterprise Extended
KERNEL_EXT
  id=200
  for mod in cramfs freevxfs hfs hfsplus jffs2 squashfs udf usb-storage dccp sctp rds tipc; do
    id=$((id+1)); loaded=0; [[ -d /sys/module/${mod//-/_} ]] && loaded=1
    snapshot "mod$id" modprobe --show-depends "$mod"
    state=INFO; ((loaded)) && state=WARNING
    if (( ! loaded )) && ok "mod$id" && grep -Eq '^install (/usr)?/bin/(false|true)' "$TMP/mod$id"; then state=PASS
    elif (( ! loaded )) && ! ok "mod$id"; then state=UNKNOWN; fi
    control "KERNEL-$id" Kernel "$state" LOW "Optional module: $mod" "Loaded=$loaded; modprobe resolution available=$(ok "mod$id" && printf yes || printf no); never loaded/unloaded by audit" 'Unused modules unavailable or explicitly restricted' 'Review storage/network role; squashfs/SCTP/USB may be required' 'CIS Level 2'
  done
  control BOOT-101 Kernel INFO LOW 'Firmware boot mode' "$([[ -d /sys/firmware/efi ]] && printf UEFI || printf 'Legacy/hidden/container')" 'Boot trust appropriate to platform' 'Inspect host firmware separately for containers'
  pattern_check BOOT-102 Kernel 'Kernel boot security bypass candidates' HIGH '(^|[[:space:]])(selinux=0|enforcing=0|apparmor=0|audit=0|mitigations=off)([[:space:]]|$)' /proc/cmdline
  pattern_check BOOT-103 Kernel 'Boot recovery debug candidates' LOW '(^|[[:space:]])(init=/bin/(ba)?sh|rd.break|systemd.debug_shell)([[:space:]]|$)' /proc/cmdline
  control BOOT-104 Kernel UNKNOWN LOW 'Bootloader authentication effectiveness' 'Bootloader files may contain password hashes; values never read or exported' 'Approved console and recovery authentication policy' 'Inspect password protection and physical/virtual console access separately'
  pattern_check KERNEL-220 Kernel 'Core dump persistence configuration' LOW '^[[:space:]]*(Storage[[:space:]]*=[[:space:]]*(external|journal)|ProcessSizeMax[[:space:]]*=[[:space:]]*[1-9])' /etc/systemd/coredump.conf /etc/systemd/coredump.conf.d
}

audit_network_extended() {
  local id n val state chain backend
  snapshot routes6 ip -6 route show table all
  snapshot firewalld firewall-cmd --state
  local active_managers=0
  if ok firewalld && grep -qx running "$TMP/firewalld"; then active_managers=$((active_managers+1)); FIREWALL_SUMMARY=firewalld; fi
  if ok ufw && grep -q 'Status: active' "$TMP/ufw"; then active_managers=$((active_managers+1)); FIREWALL_SUMMARY="${FIREWALL_SUMMARY/unknown/} UFW"; fi
  for backend in nftables iptables; do
    snapshot "fwunit_$backend" systemctl is-active "$backend"
    ok "fwunit_$backend" && active_managers=$((active_managers+1))
  done
  ok nft && grep -q 'hook ' "$TMP/nft" && FIREWALL_SUMMARY="${FIREWALL_SUMMARY/unknown/}/nftables"
  ok ipt && grep -q '^-A ' "$TMP/ipt" && FIREWALL_SUMMARY="${FIREWALL_SUMMARY/unknown/}/iptables"
  control FW-101 Firewall "$([[ $active_managers -gt 1 ]] && printf WARNING || printf INFO)" MEDIUM 'Concurrent firewall managers' "Active manager indicators=$active_managers; firewalld/UFW using nftables is not itself a conflict" 'One documented authoritative manager per policy scope' 'Review independent managers and backend ownership'
  for chain in INPUT FORWARD OUTPUT; do
    case "$chain" in INPUT) id=110;; FORWARD) id=111;; OUTPUT) id=112;; esac
    if ok ipt; then
      val=$(awk -v c=":$chain" '$1==c{print $2}' "$TMP/ipt")
      control "FW-$id" Firewall "$([[ -n $val ]] && printf INFO || printf UNKNOWN)" LOW "IPv4 $chain base policy inventory" "Policies=${val:-no legacy base chain}; ordered rules and nft hooks can override apparent protection" 'Approved default policy in complete packet path' 'Evaluate ordered rules, jumps, sets and container NAT paths'
    else control "FW-$id" Firewall UNKNOWN LOW "IPv4 $chain base policy" 'iptables backend not readable; nftables may be authoritative' 'Approved complete packet-path verdict' 'Review authoritative backend'; fi
  done
  if ok nft; then
    val=$(awk '/hook (input|forward|output)/ {gsub(/comment.*/,"[comment withheld]"); print}' "$TMP/nft" | head -n8)
    control FW-113 Firewall INFO LOW 'nftables base hooks and policies' "${val:-No readable base hooks}; sets and rule ordering not reduced to a reachability verdict" 'Approved inet/ip/ip6 base chains' 'Validate chain priorities and default policies'
  else control FW-113 Firewall UNKNOWN LOW 'nftables base hooks and policies' 'nft ruleset unreadable' 'Readable authoritative backend' 'Rerun with sufficient network namespace privileges'; fi
  if ok ipt; then
    n=$(awk '/^-A / {sub(/ -m comment --comment .*/," [comment removed]"); if(seen[$0]++)n++}END{print n+0}' "$TMP/ipt")
    control FW-114 Firewall "$([[ $n == 0 ]] && printf PASS || printf WARNING)" LOW 'Duplicate literal iptables rule candidates' "Duplicate lines=$n; ordering can make duplicates intentional" 'No unexplained redundant rules' 'Review duplicates without automatically deleting rules'
    n=$(awk '/^-A / && / -j ACCEPT/ && !/ -s |--source /{n++}END{print n+0}' "$TMP/ipt")
    control FW-115 Firewall INFO LOW 'Source-unrestricted ACCEPT candidates' "Rules=$n; interfaces, state, ports, chains and nft hooks may restrict these" 'Only intended broad rules' 'Perform semantic firewall review before exposure classification'
  else
    control FW-114 Firewall UNKNOWN LOW 'Duplicate literal firewall rules' 'Readable legacy rules unavailable; nft syntax requires separate interpretation' 'No unexplained redundant rules' 'Review authoritative backend'
    control FW-115 Firewall UNKNOWN LOW 'Broad firewall allow candidates' 'Readable legacy rules unavailable' 'Least privilege source scope' 'Review authoritative backend'
  fi
  snapshot ipv4 ip -o -4 addr show
  snapshot ipv6 ip -o -6 addr show
  for id in 101 102 103 104 105 106; do
    case "$id" in
      101) backend=ipv4; val='IPv4 interface inventory';;102) backend=ipv6;val='IPv6 interface inventory';;
      103) backend=routes; val='IPv4 route and default gateway inventory';;104) backend=routes6;val='IPv6 route and default gateway inventory';;
      105) backend=sockets;val='Listener process ownership correlation';;106) backend=connections;val='Established outbound connection inventory';;
    esac
    n=0; ok "$backend" && n=$(wc -l < "$TMP/$backend")
    control "NET-$id" Network "$(ok "$backend" && printf INFO || printf UNKNOWN)" LOW "$val" "Rows=$n; detailed socket/routes inventory where readable; credentials/arguments not collected" 'Documented interfaces/routes/processes and intended traffic' 'Compare against approved network and workload inventory'
  done
  control NET-107 Network UNKNOWN HIGH 'Public internet reachability proof' 'No external vantage point, cloud API or upstream firewall evaluation' 'Database/admin ports restricted to authorized clients' 'Validate from an authorized external vantage point; wildcard binding alone is not public exposure'
  control NET-108 Network UNKNOWN MEDIUM 'Unexpected outbound destinations' 'Connections observed, but no authorized destination baseline' 'Outbound destinations match approved allowlist' 'Correlate process ownership and SIEM/network policy'
  if [[ -r /etc/resolv.conf ]]; then
    val=$(awk '$1=="nameserver"{print $2}' /etc/resolv.conf)
    control DNS-101 DNS INFO LOW 'Configured resolver addresses' "${val:-No nameserver entries; stub/alternate resolver possible}" 'Approved resolvers for host role' 'Validate split DNS, routing and resolver ownership'
  else control DNS-101 DNS UNKNOWN LOW 'Resolver addresses' 'resolv.conf unreadable' 'Readable intended resolver configuration' 'Inspect systemd-resolved or NetworkManager'; fi
  snapshot dnssec resolvectl status
  control DNS-102 DNS "$(ok dnssec && printf INFO || printf UNKNOWN)" LOW 'DNSSEC configuration indicators' "$(awk '/DNSSEC/{print}' "$TMP/dnssec" | head -n4); configuration does not prove end-to-end validation" 'Organization-approved DNS validation policy' 'Verify validation at the authoritative resolver boundary'
}
audit_persistence_extended() {
  local id title pattern path
  local -a roots=()
  for path in /etc/crontab /etc/cron.d /etc/cron.daily /etc/cron.hourly /etc/cron.weekly /etc/cron.monthly /var/spool/cron /etc/rc.local /etc/profile /etc/profile.d /etc/bashrc /etc/bash.bashrc /etc/systemd/system; do [[ -e $path ]] && roots+=("$path"); done
  while IFS='|' read -r id title pattern; do
    if (( ${#roots[@]} )); then
      CURRENT_LEVEL='Threat Indicator'; pattern_check "$id" Persistence "$title" MEDIUM "$pattern" "${roots[@]}"; CURRENT_LEVEL=''
    else control "$id" Persistence UNKNOWN MEDIUM "$title" 'No readable persistence roots' 'Approved trusted startup commands' 'Inspect host startup mechanisms' 'Threat Indicator'; fi
  done <<'PERSIST_ROWS'
PERSIST-101|Startup temporary-path execution candidates|^[[:space:]]*[^#[:space:]].*(/tmp/|/var/tmp/|/dev/shm/)
PERSIST-102|Startup base64 decode-to-execution candidates|^[[:space:]]*[^#[:space:]].*base64.*(-d|--decode).*\|.*(sh|python|perl)
PERSIST-103|Startup external download execution candidates|^[[:space:]]*[^#[:space:]].*(curl|wget).*https?://.*(&&|;).*(sh|bash|python|perl)
PERSIST-104|Startup hidden script path candidates|^[[:space:]]*[^#[:space:]].*/\.[^ /]+/[^ ]+
PERSIST-105|Startup reverse-shell syntax candidates|^[[:space:]]*[^#[:space:]].*(/dev/tcp/|nc[[:space:]].*-e[[:space:]]|socat.*EXEC:)
PERSIST_ROWS
  if (( ${#roots[@]} )); then
    scan_files extra_recent "$SCAN_SECONDS" "${roots[@]}" -xdev -maxdepth 4 -type f -mtime -7 -print0
    scan_result extra_recent PERSIST-106 Persistence 'Persistence files modified in last seven days' LOW 'Compare metadata with approved change records; recent is not malicious' 0 INFO
  fi
  snapshot atjobs atq
  control PERSIST-107 Persistence "$(ok atjobs && printf INFO || printf UNKNOWN)" LOW 'Deferred at job inventory' "Rows=$(wc -l < "$TMP/atjobs"); root needed for cross-user coverage; commands withheld" 'Approved deferred jobs only' 'Review scheduled jobs and their owners without executing commands'
  perm_check PERSIST-108 Persistence /etc/cron.allow 0027 MEDIUM
  perm_check PERSIST-109 Persistence /etc/at.allow 0027 MEDIUM
  local service fields uid count=0 rootcount=0 custom=0 writable=0 unknowns=0 execpath unitpath
  if ok services; then
    while read -r service rest; do
      [[ $service == *.service ]] || continue; count=$((count+1)); ((count>30)) && break
      snapshot unitfields systemctl show "$service" -p User -p FragmentPath
      if ! ok unitfields; then unknowns=$((unknowns+1)); continue; fi
      uid=$(awk -F= '$1=="User"{print $2}' "$TMP/unitfields"); [[ -z $uid || $uid == root || $uid == 0 ]] && rootcount=$((rootcount+1))
      unitpath=$(awk -F= '$1=="FragmentPath"{print substr($0,index($0,"=")+1)}' "$TMP/unitfields")
      [[ $unitpath == /etc/systemd/system/* ]] && custom=$((custom+1))
      if [[ -f $unitpath ]]; then
        fields=$(stat -Lc %a -- "$unitpath" 2>/dev/null || true)
        [[ $fields =~ ^[0-7]+$ ]] && (( (8#$fields & 0022)!=0 )) && writable=$((writable+1))
      fi
    done < "$TMP/services"
    control SYSTEMD-101 Systemd INFO LOW 'Root service inventory' "Root/default users=$rootcount; inspected up to 30 running services; failures=$unknowns" 'Approved service identities' 'Reduce privileges where compatible; root daemons can be legitimate'
    control SYSTEMD-102 Systemd INFO LOW 'Custom service unit inventory' "Local fragments=$custom; inspected up to 30 services" 'Custom units match trusted deployment baseline' 'Validate unit provenance and drop-in overrides'
    control SYSTEMD-103 Systemd "$([[ $writable -gt 0 ]] && printf HIGH || printf UNKNOWN)" HIGH 'Service unit write permission candidates' "Writable fragment candidates=$writable; only sampled active units; drop-ins assessed in persistence scan" 'Trusted ownership and write permissions' 'Inspect complete unit/drop-in graph and executable provenance'
  else
    for id in 101 102 103; do control "SYSTEMD-$id" Systemd UNKNOWN MEDIUM "Service inspection $id" 'Active service inventory unavailable' 'Readable systemd runtime' 'Inspect host systemd namespace'; done
  fi
  if ok sandbox; then
    fields=$(awk 'NR>1 && NF {n++} END{print n+0}' "$TMP/sandbox")
    control SYSTEMD-104 Systemd INFO LOW 'Service sandbox assessment summary' "Summary rows=$fields; bounded systemd-analyze security output retained" 'Sandbox policy appropriate for each service' 'Review exposure values as hardening suggestions, not vulnerability scores'
  else control SYSTEMD-104 Systemd UNKNOWN LOW 'Service sandbox assessment' 'systemd-analyze unavailable/failed' 'Supported offline or runtime service inspection' 'Inspect sandbox directives on host'; fi
  control SYSTEMD-105 Systemd "$(ok timers && printf INFO || printf UNKNOWN)" LOW 'Systemd scheduled timer inventory' "Rows=$(wc -l < "$TMP/timers"); inventory in report" 'Timers correspond to approved jobs' 'Review user/system timers and triggered services'
}
audit_logging_extended() {
  unit_check LOG-101 Logging rsyslog.service 'rsyslog service state' LOW
  unit_check LOG-102 Logging logrotate.timer 'Log rotation timer' LOW
  perm_check LOG-103 Logging /etc/logrotate.conf 0022 MEDIUM
  pattern_check LOG-104 Logging 'Remote syslog destination indicators' LOW '^[[:space:]]*[^#[:space:]].*(@@?|omfwd|omrelp)' /etc/rsyslog.conf /etc/rsyslog.d
  if [[ ${STATES[-1]} == WARN || ${STATES[-1]} == PASS ]]; then STATES[-1]=INFO; LEVELS[-1]=Informational; fi
  local path n=0 recent=0 state id pattern title
  for path in /var/log/auth.log /var/log/secure /var/log/audit/audit.log; do [[ -s $path ]] && n=$((n+1)); done
  control LOG-105 Logging INFO LOW 'Authentication log presence' "Nonempty conventional files=$n; journal-only deployments may be valid" 'Durable authentication events in approved sink' 'Verify actual event delivery centrally'
  local -a logroots=(/var/log)
  scan_files extra_logs "$SCAN_SECONDS" "${logroots[@]}" -xdev -maxdepth 2 -type f -perm -0002 -print0
  scan_result extra_logs LOG-106 Logging 'World-writable log files' HIGH 'Investigate unauthorized write access and log tampering risk'
  scan_files extra_truncated "$SCAN_SECONDS" /var/log -xdev -maxdepth 2 -type f -size 0 -mtime -1 -print0
  scan_result extra_truncated LOG-107 Logging 'Recently empty log file indicators' LOW 'Correlate with log rotation; empty file alone is not tampering' 0 INFO
  control LOG-108 Logging UNKNOWN MEDIUM 'Unexplained logging gaps' 'No trusted event baseline or expected event frequency provided' 'Continuous verified log delivery' 'Correlate external SIEM receipt and maintenance windows'
  snapshot auditstatus auditctl -s; snapshot auditrules auditctl -l
  local key val expected
  for key in enabled lost backlog_limit; do
    case "$key" in enabled) id=101; expected='Enabled auditing; immutable mode 2 for strict policy';; lost) id=102; expected='Zero lost events';; backlog_limit) id=103; expected='Capacity sufficient for peak event rate';; esac
    val=$(awk -v k="$key" '$1==k{print $2}' "$TMP/auditstatus"); state=UNKNOWN
    if ok auditstatus && [[ $val =~ ^[0-9]+$ ]]; then
      state=INFO
      [[ $key == lost && $val == 0 ]] && state=PASS
      [[ $key == lost && $val -gt 0 ]] && state=HIGH
      [[ $key == enabled && $val == 0 ]] && state=HIGH
      [[ $key == enabled && $val == 2 ]] && state=PASS
    fi
    control "AUDIT-$id" Audit "$state" MEDIUM "Kernel audit $key" "Observed=${val:-unavailable}; root/CAP_AUDIT_CONTROL may be required" "$expected" 'Inspect audit pipeline capacity, event loss and approved immutable policy' 'CIS Level 1'
  done
  while IFS='|' read -r id title pattern; do
    state=UNKNOWN; n=0
    if ok auditrules; then n=$(grep -Ec "$pattern" "$TMP/auditrules" || true); [[ $n -gt 0 ]] && state=INFO || state=WARNING; fi
    control "$id" Audit "$state" MEDIUM "Audit rule coverage: $title" "Pattern matches=$n; rule semantics, architecture and permissions require verification" 'Effective rules cover required events on both relevant ABIs' 'Validate full audit rule semantics and test event receipt' 'CIS Level 1'
  done <<'AUDIT_ROWS'
AUDIT-110|identity files|/etc/(passwd|group|shadow|gshadow)
AUDIT-111|sudo authorization|/etc/sudoers
AUDIT-112|login and logout|/(lastlog|faillog|btmp|wtmp|utmp)
AUDIT-113|privileged executions|execve|/usr/bin/sudo|perm=x
AUDIT-114|kernel module changes|init_module|finit_module|delete_module|/sbin/(insmod|modprobe|rmmod)
AUDIT-115|clock changes|adjtimex|settimeofday|clock_settime
AUDIT-116|network configuration|sethostname|setdomainname|/etc/hosts|/etc/issue
AUDIT-117|MAC policy changes|/etc/selinux|/etc/apparmor
AUDIT_ROWS
  snapshot chrony chronyc tracking
  control TIME-101 Logging "$(ok chrony && printf INFO || printf UNKNOWN)" LOW 'Chrony offset and leap status' "$(awk '/System time|Last offset|Leap status/{print}' "$TMP/chrony"); no network probe initiated by audit" 'Synchronized approved time source; offset within organizational tolerance' 'Review chrony/ntpd or timesyncd health'
  perm_check TIME-102 Logging /etc/chrony.conf 0022 MEDIUM
}
audit_processes_cloud_resources() {
  local id title pattern n state path pid exe deleted=0 tmpexec=0 homeexec=0 inspected=0 denied=0 rootbin=0 mode uid
  # Shallow known /proc/PID/exe observations only. No process arguments or environment.
  for path in /proc/[0-9]*/exe; do
    [[ -L $path ]] || continue; inspected=$((inspected+1)); ((inspected>4096)) && { denied=$((denied+1)); break; }
    exe=$(readlink -- "$path" 2>/dev/null) || { denied=$((denied+1)); continue; }
    [[ $exe == *' (deleted)' ]] && deleted=$((deleted+1))
    [[ $exe == /tmp/* || $exe == /var/tmp/* || $exe == /dev/shm/* ]] && tmpexec=$((tmpexec+1))
    [[ $exe == /home/* ]] && homeexec=$((homeexec+1))
    if [[ $exe == /* && -f $exe ]]; then
      read -r mode uid < <(stat -Lc '%a %u' -- "$exe" 2>/dev/null) || continue
      if [[ $uid == 0 ]] && (( (8#$mode & 0022)!=0 )); then rootbin=$((rootbin+1)); fi
    fi
  done
  for id in 101 102 103; do
    case "$id" in 101) n=$tmpexec;title='Processes executing from temporary paths';;102) n=$homeexec;title='Processes executing from user home paths';;103) n=$rootbin;title='Writable root-owned running executable candidates';;esac
    if ((n>0)); then state=WARNING; elif ((denied>0 || EUID!=0)); then state=UNKNOWN; else state=PASS; fi
    control "PROC-$id" Threat "$state" MEDIUM "$title" "Candidates=$n; inspected=$inspected; inaccessible=$denied; kernel threads have no exe link and are excluded" 'No unexplained executable provenance indicator' 'Correlate with deployments and EDR; this is a threat indicator, not malware proof' 'Threat Indicator'
  done
  while IFS='|' read -r id title pattern; do
    if ok processes; then n=$(awk "$pattern" "$TMP/processes"); state=INFO; [[ $id == MAL-* && $n -gt 0 ]] && state=WARNING
    else n=0; state=UNKNOWN; fi
    control "$id" Threat "$state" LOW "$title" "Candidate processes=$n; point-in-time comm/user/cpu/memory only" 'No unexplained process indicator' 'Review owner, executable provenance and sustained workload; names are not malware proof' "$([[ $id == MAL-* ]] && printf 'Threat Indicator' || printf Informational)"
  done <<'PROC_ROWS'
PROC-110|Root process inventory|NR>1 && $3=="root"{n++}END{print n+0}
PROC-111|High CPU process inventory|NR>1 && $5+0>80{n++}END{print n+0}
PROC-112|High memory process inventory|NR>1 && $6+0>50{n++}END{print n+0}
MAL-101|Web identity shell process indicators|NR>1 && $3~/^(www-data|nginx|apache)$/ && $4~/^(sh|bash|dash|zsh)$/{n++}END{print n+0}
MAL-102|Mining-name process indicators|NR>1 && tolower($4)~/^(xmrig|cpuminer|minerd|xmr-stak)$/{n++}END{print n+0}
PROC_ROWS
  snapshot procstates ps -eo stat=
  n=$(awk '$1~/^Z/{n++}END{print n+0}' "$TMP/procstates")
  control RESOURCE-101 Resources "$(ok procstates && printf INFO || printf UNKNOWN)" LOW 'Zombie processes' "Count=$n" 'No persistent zombie accumulation' 'Review parent process reaping and service health'
  control RESOURCE-102 Resources INFO LOW 'Process and descriptor limits' "pid_max=$(cat /proc/sys/kernel/pid_max 2>/dev/null || printf unknown); file-max=$(cat /proc/sys/fs/file-max 2>/dev/null || printf unknown); shell-nofile=$(ulimit -n); process rows=$(wc -l < "$TMP/procstates")" 'Capacity appropriate for service load' 'Inspect per-service LimitNOFILE/TasksMax; shell limit is not every service limit'
  snapshot swap swapon --show --noheadings --output NAME,TYPE,SIZE,USED
  control RESOURCE-103 Resources "$(ok swap && printf INFO || printf UNKNOWN)" LOW 'Swap usage inventory' "$(snippet swap 5)" 'Approved swap and memory pressure policy' 'Review workload needs and encryption of swap'
  local vendor product cloud=Unknown
  vendor=$(cat /sys/class/dmi/id/sys_vendor 2>/dev/null || true); product=$(cat /sys/class/dmi/id/product_name 2>/dev/null || true)
  case "$vendor $product" in *Amazon*) cloud=AWS;; *Google*) cloud=GCP;; *Microsoft*Virtual*) cloud='Azure-or-HyperV';; esac
  control CLOUD-101 Cloud INFO LOW 'Cloud platform hints' "DMI platform=$cloud; clues do not prove tenant/provider identity" 'Identify hosting context without metadata requests' 'Correlate with asset inventory'
  control CLOUD-102 Cloud UNKNOWN MEDIUM 'Cloud metadata access controls' 'No requests sent to metadata endpoints; host routing does not prove IMDSv2/hop-limit enforcement' 'Provider-recommended metadata restrictions' 'Validate IMDS/IAM policies with authorized provider tooling'
  local -a credroots=(); for path in /root/.aws /root/.azure /root/.config/gcloud /var/lib/cloud/instances; do [[ -d $path ]] && credroots+=("$path"); done
  if (( ${#credroots[@]} )); then
    scan_files extra_cloud "$SCAN_SECONDS" "${credroots[@]}" -xdev -maxdepth 3 -type f -perm /0007 -print0
    scan_result extra_cloud CLOUD-103 Cloud 'Other-accessible cloud credential/initialization file candidates' HIGH 'Inspect metadata and restrict actual credential-bearing files; contents were never read' 2 WARN
  elif ((EUID!=0)); then control CLOUD-103 Cloud UNKNOWN HIGH 'Cloud credential metadata' 'Requires root privileges for protected homes/cloud-init' 'Protected credential paths' 'Rerun as root'
  else control CLOUD-103 Cloud INFO LOW 'Cloud credential metadata' 'No selected credential directories detected; custom locations not assessed' 'Approved credential storage' 'Prefer scoped workload identity'; fi
  local agent=0
  if ok services; then agent=$(grep -Eic '(veeam|bacula|bareos|restic|borg|rubrik|commvault|urbackup)' "$TMP/services" || true); fi
  control BACKUP-101 Recovery INFO LOW 'Backup service indicators' "Agent/service name candidates=$agent; backup recoverability not verified" 'Verified off-host recoverable backup' 'Review backup platform reports and recent restore evidence'
  local -a backuproots=(); for path in /var/backups /backup /backups; do [[ -d $path ]] && backuproots+=("$path"); done
  if (( ${#backuproots[@]} )); then
    scan_files extra_backup "$SCAN_SECONDS" "${backuproots[@]}" -xdev -maxdepth 2 -type f -mtime -7 -print0
    scan_result extra_backup BACKUP-102 Recovery 'Recent local backup-file indicators' LOW 'Backup indicators found does not mean successful backup; verify off-host copy and restoration' 0 INFO
  else control BACKUP-102 Recovery INFO LOW 'Recent backup-file indicators' 'No selected local backup directories; remote backup may still exist' 'Verified backup evidence' 'Check backup system centrally'; fi
}

audit_containers() {
  local engine prefix state id title format line c total=0 failed=0 key n sock
  local priv net usr caps pid ipc readonly security mounts ports created
  local -a cli=()
  local -A hits=()
  for engine in docker podman; do
    prefix=${engine^^}; hits=(); total=0 failed=0; cli=()
    # Only local Unix sockets; never inherit DOCKER_HOST/remote contexts.
    if [[ $engine == docker ]]; then
      sock=/var/run/docker.sock
      cli=(docker --host "unix://$sock" --config "$TMP/docker-client")
    else
      sock=/run/podman/podman.sock
      ((EUID!=0)) && sock="/run/user/$EUID/podman/podman.sock"
      # Podman's local CLI may initialize storage. Use its existing read-only service API only.
      cli=(podman --remote --url "unix://$sock")
    fi
    if [[ ! -S $sock ]]; then
      state=NA; have "$engine" && state=UNKNOWN
    else
      snapshot "$engine-ids" "${cli[@]}" ps -q
      ok "$engine-ids" && state=PASS || state=UNKNOWN
    fi
    if [[ $state == PASS ]]; then
      format='{{.HostConfig.Privileged}}|{{.HostConfig.NetworkMode}}|{{.Config.User}}|{{.HostConfig.CapAdd}}|{{.HostConfig.PidMode}}|{{.HostConfig.IpcMode}}|{{.HostConfig.ReadonlyRootfs}}|{{.HostConfig.SecurityOpt}}|{{range .Mounts}}{{.Source}}={{.RW}};{{end}}|{{.HostConfig.PortBindings}}|{{.Created}}'
      while IFS= read -r c; do
        [[ $c =~ ^[a-f0-9]{12,64}$ ]] || continue; total=$((total+1)); ((total>100)) && { failed=$((failed+1)); break; }
        line=$(run "${cli[@]}" inspect --format "$format" "$c" 2>/dev/null) || { failed=$((failed+1)); continue; }
        IFS='|' read -r priv net usr caps pid ipc readonly security mounts ports created <<< "$line"
        [[ $priv == true || $priv == false ]] && [[ $readonly == true || $readonly == false ]] || { failed=$((failed+1)); continue; }
        [[ $priv == true ]] && hits[priv]=$(( ${hits[priv]:-0}+1 ))
        [[ $net == host ]] && hits[net]=$(( ${hits[net]:-0}+1 ))
        [[ -z $usr || $usr == root || $usr == 0 || $usr == 0:* || $usr == root:* ]] && hits[user]=$(( ${hits[user]:-0}+1 ))
        [[ $caps == *SYS_ADMIN* || $caps == *ALL* || $caps == *SYS_PTRACE* ]] && hits[caps]=$(( ${hits[caps]:-0}+1 ))
        [[ $pid == host ]] && hits[pid]=$(( ${hits[pid]:-0}+1 ))
        [[ $ipc == host ]] && hits[ipc]=$(( ${hits[ipc]:-0}+1 ))
        [[ $readonly != true ]] && hits[readonly]=$(( ${hits[readonly]:-0}+1 ))
        [[ $security != *no-new-privileges* ]] && hits[nnp]=$(( ${hits[nnp]:-0}+1 ))
        [[ $mounts == *docker.sock* || $mounts == *podman.sock* ]] && hits[socket]=$(( ${hits[socket]:-0}+1 ))
        [[ $mounts == /=* || $mounts == *';/='* ]] && hits[rootmount]=$(( ${hits[rootmount]:-0}+1 ))
        [[ $mounts == *'=true;'* ]] && hits[writable]=$(( ${hits[writable]:-0}+1 ))
        [[ $mounts == *'/etc/'* || $mounts == *'/proc/'* || $mounts == *'/sys/'* || $mounts == *'/root/'* ]] && hits[sensitive]=$(( ${hits[sensitive]:-0}+1 ))
        [[ $ports != 'map[]' && $ports != '<nil>' && -n $ports ]] && hits[ports]=$(( ${hits[ports]:-0}+1 ))
        [[ $security == *unconfined* ]] && hits[unconfined]=$(( ${hits[unconfined]:-0}+1 ))
      done < "$TMP/$engine-ids"
    fi
    while IFS='|' read -r id key title sev; do
      n=${hits[$key]:-0}
      if [[ $state != PASS ]]; then
        control "$prefix-$id" Containers "$state" "$sev" "$engine $title" 'No readable existing local runtime API; absent tool is not a failed security control' 'Approved runtime policy' 'Validate local runtime endpoint; remote daemon access deliberately disabled'
      elif ((n>0)); then
        control "$prefix-$id" Containers "$([[ $key == ports ]] && printf INFO || printf WARNING)" "$sev" "$engine $title" "Candidates=$n; inspected=$total; failures=$failed; names/env/commands never collected" 'Only documented least-privilege exceptions' 'Review workload isolation and host impact before remediation'
      elif ((failed>0)); then control "$prefix-$id" Containers UNKNOWN "$sev" "$engine $title" "Incomplete inspection; failures=$failed" 'Complete readable container metadata' 'Inspect remaining containers'
      else control "$prefix-$id" Containers PASS "$sev" "$engine $title" "No candidate among $total running containers" 'Approved runtime policy' 'Maintain least privilege and image lifecycle'; fi
    done <<'CONTAINER_ROWS'
101|priv|privileged mode|HIGH
102|net|host network namespace|MEDIUM
103|user|root or unspecified container user|MEDIUM
104|caps|high-risk capabilities|HIGH
105|pid|host PID namespace|HIGH
106|ipc|host IPC namespace|MEDIUM
107|readonly|writable root filesystem|LOW
108|nnp|missing no-new-privileges option|MEDIUM
109|socket|runtime socket host mount|HIGH
110|rootmount|host root mounted into container|HIGH
111|writable|writable host mounts|LOW
112|sensitive|sensitive host path mounts|HIGH
113|ports|published port review|LOW
114|unconfined|unconfined security options|HIGH
CONTAINER_ROWS
    if [[ -S $sock ]]; then
      line=$(stat -Lc '%a %u %g' -- "$sock" 2>/dev/null || true)
      read -r n priv net <<< "$line"
      if [[ $n =~ ^[0-7]+$ ]]; then
        state=PASS; (( (8#$n & 0002)!=0 )) && state=CRITICAL
        control "$prefix-115" Containers "$state" HIGH "$engine API socket permissions" "mode=$n uid=$priv gid=$net; group is privileged" 'No world-write API access' 'Restrict daemon access and review privileged group membership'
      else control "$prefix-115" Containers UNKNOWN HIGH "$engine socket permissions" 'stat unavailable' 'Trusted socket access' 'Inspect local socket'; fi
    else control "$prefix-115" Containers NA LOW "$engine socket permissions" 'Selected socket absent' 'Trusted socket access if runtime is used' 'Inspect custom endpoint separately'; fi
    if [[ $state == PASS || -S $sock ]]; then
      snapshot "$engine-stopped" "${cli[@]}" ps -a -q --filter status=exited
      control "$prefix-116" Containers "$(ok "$engine-stopped" && printf INFO || printf UNKNOWN)" LOW "$engine stopped containers" "Exited rows=$(wc -l < "$TMP/$engine-stopped"); volumes and images require separate retention review" 'Approved stopped workload retention' 'Review stale containers without deleting data'
    else control "$prefix-116" Containers "$([[ $state == NA ]] && printf NA || printf UNKNOWN)" LOW "$engine stopped containers" 'Runtime visibility unavailable' 'Approved retention' 'Inspect local runtime'; fi
    control "$prefix-117" Containers "$([[ $state == NA ]] && printf NA || printf UNKNOWN)" LOW "$engine image currency" 'Creation age alone does not establish image vulnerability or update status' 'Verified signed, maintained and scanned image' 'Use approved registry digest and vulnerability scanner'
  done
  pattern_check DOCKER-120 Containers 'Docker insecure registry configuration candidates' MEDIUM '"insecure-registries"[[:space:]]*:' /etc/docker/daemon.json
  if ok sockets; then
    n=$(awk '$5~/:237[56]$/{n++}END{print n+0}' "$TMP/sockets")
    control DOCKER-121 Containers "$([[ $n -gt 0 ]] && printf WARNING || printf INFO)" HIGH 'Docker TCP API listener candidates' "Port 2375/2376 rows=$n; identity/TLS/authorization unverified" 'Authenticated restricted daemon API' 'Validate actual listener owner and API authentication; no API request sent'
  else control DOCKER-121 Containers UNKNOWN HIGH 'Docker TCP API candidates' 'Socket inventory unavailable' 'Restricted API exposure' 'Inspect listening daemon'; fi
}
audit_crypto_secrets() {
  local path id n val expired=0 soon=0 valid=0 failed=0 count=0 rc
  snapshot opensslversion openssl version
  control CRYPTO-101 Cryptography "$(ok opensslversion && printf INFO || printf UNKNOWN)" LOW 'OpenSSL vendor version' "$(snippet opensslversion 1); vendor backports prevent version-only CVE inference" 'Supported vendor crypto library' 'Use current vendor security advisories'
  pattern_check TLS-101 Cryptography 'Legacy TLS protocol configuration candidates' MEDIUM '^[[:space:]]*(ssl_protocols.*TLSv1([[:space:];]|\.1)|SSLProtocol.*\+(SSLv[23]|TLSv1([[:space:]]|\.1))|MinProtocol[[:space:]]*=[[:space:]]*(SSLv3|TLSv1(\.1)?))' /etc/nginx /etc/apache2 /etc/httpd /etc/ssl/openssl.cnf
  pattern_check TLS-102 Cryptography 'Weak TLS cipher configuration candidates' MEDIUM '^[[:space:]]*(ssl_ciphers|SSLCipherSuite|CipherString).*(:|[[:space:]])(RC4|3DES|DES|aNULL|eNULL)(:|[[:space:];]|$)' /etc/nginx /etc/apache2 /etc/httpd /etc/ssl/openssl.cnf
  # Public certificate extensions only. Never open private-key files or mixed PEM bundles.
  if have openssl && [[ $MODE != quick && -d /etc/ssl/certs ]]; then
    scan_files certpaths "$SCAN_SECONDS" /etc/ssl/certs -xdev -maxdepth 2 -type f -name '*.crt' -print0
    while IFS= read -r -d '' path; do
      count=$((count+1)); ((count>100)) && { failed=$((failed+1)); break; }
      if run openssl x509 -in "$path" -noout -checkend 0 >/dev/null 2>&1; then
        valid=$((valid+1)); run openssl x509 -in "$path" -noout -checkend 2592000 >/dev/null 2>&1 || soon=$((soon+1))
      else
        if run openssl x509 -in "$path" -noout -enddate >/dev/null 2>&1; then expired=$((expired+1)); else failed=$((failed+1)); fi
      fi
    done < "$TMP/certpaths"
    ok certpaths || failed=$((failed+1))
    control TLS-103 Cryptography "$([[ $expired -gt 0 ]] && printf WARNING || printf INFO)" LOW 'Expired public certificate file indicators' "Expired=$expired; parseable=$((valid+expired)); errors=$failed; trust-store files are not necessarily served certificates" 'Required service certificates current' 'Validate actually served certificate and full chain; no handshake performed'
    control TLS-104 Cryptography "$([[ $soon -gt 0 ]] && printf WARNING || printf INFO)" LOW 'Public certificates expiring within 30 days' "Candidates=$soon; selected .crt files only; no private keys read" 'Renew required service certificates before expiry' 'Check active service bindings and renewal automation'
  else
    for id in 103 104; do control "TLS-$id" Cryptography UNKNOWN LOW 'Public certificate expiry assessment' 'Skipped in quick mode or required public-certificate tool/path unavailable' 'Current service certificate inventory' 'Use standard/deep or service-specific certificate inventory'; done
  fi
  control TLS-105 Cryptography UNKNOWN MEDIUM 'Negotiated TLS protocol strength' 'No live TLS handshake or application credential used' 'Approved effective TLS protocol/cipher negotiation' 'Perform an authorized service-specific TLS test'
  local -a roots=(); for path in /etc /opt /srv /home /root; do [[ -d $path ]] && roots+=("$path"); done
  scan_files extra_git "$SCAN_SECONDS" "${roots[@]}" -xdev -maxdepth 5 -type f -path '*/.git/config' -perm /0007 -print0
  scan_result extra_git SECRET-101 Cryptography 'Other-accessible Git configuration candidates' MEDIUM 'Check whether remote URLs embed credentials; file contents never collected' 1 WARN
  scan_files extra_deploy "$SCAN_SECONDS" "${roots[@]}" -xdev -maxdepth 4 -type f \( -name '*credentials*' -o -name '*secrets*' -o -name '*backup*.conf' -o -name 'docker-compose*.yml' -o -name 'compose.yaml' \) -perm -0004 -print0
  scan_result extra_deploy SECRET-102 Cryptography 'Readable deployment/credential filename candidates' LOW 'Verify actual secret-bearing files locally; names do not prove leaked credentials' 1 WARN
  scan_files extra_wwdir "$SCAN_SECONDS" "${roots[@]}" -xdev -maxdepth 5 -type d -perm -0002 ! -perm -1000 -print0
  scan_result extra_wwdir FS-270 Filesystem 'World-writable directories without sticky bit' HIGH 'Validate directory purpose and prevent untrusted file replacement'
  scan_files extra_hidden "$SCAN_SECONDS" "${roots[@]}" -xdev -maxdepth 5 -type f -name '.*' -perm /0111 -print0
  CURRENT_LEVEL='Threat Indicator'; scan_result extra_hidden MAL-110 Threat 'Hidden executable file candidates' LOW 'Review provenance; hidden executable names may be legitimate' 1 WARN; CURRENT_LEVEL=''
}
usage() {
  cat <<'HELP'
Enterprise Linux Security Auditor 2.0.0 (single-file Bash 4.4+)
Usage: sudo bash security-audit.sh [options]
  --quick | --standard | --deep     Cost profile; standard is default
  --full | --summary               Detailed or executive terminal report
  --profile cis-l1|cis-l2|enterprise|full  Selected policy (default enterprise)
  --no-color | --color auto|always|never
  --export-json FILE               Create a new private JSON file
  --export-html [FILE]             Create standalone HTML; without FILE uses legacy report directory
  --output-dir ABSOLUTE_PATH       Create NEW private JSON/HTML/text report directory
  --scan-seconds N                 Per scan timeout, 5..300 seconds
  --disk-thresholds WARN,HIGH,CRIT  Defaults 80,90,95; comparisons are strictly >
  --critical-caps ONE,TWO,THREE     Defaults 85,75,65; for 1/2/3+ critical findings
  --ssh-context SPEC               sshd -T -C context for Match evaluation
  --sshd-config ABSOLUTE_PATH      Actual SSH daemon configuration
  --self-test                     Isolated local fixtures, no host remediation
  --demo                          Synthetic report (clearly labeled)
  --version | --help
Examples (report targets must not already exist):
  sudo ./security-audit.sh --quick --summary
  sudo ./security-audit.sh --deep --profile full --scan-seconds 60
  sudo ./security-audit.sh --export-json /root/audit.json
  sudo ./security-audit.sh --export-html /root/audit.html
  sudo ./security-audit.sh --output-dir /root/audit-new
  sudo ./security-audit.sh --ssh-context user=admin,host=admin.example,addr=192.0.2.10
  ./security-audit.sh --self-test
Cost (--deep), terminal detail (--full), and policy (--profile full) differ.
Report parents must exist, contain no symlinks and be trusted when run as root.
Distribution adapters have partial validation; consult VALIDATION.md.
Only private temporary files and requested reports are written. No installs,
refresh, upgrade, service changes, firewall changes or sysctl writes. Cached
package data cannot prove current CVE/lifecycle state. Missing evidence UNKNOWN.
CIS-aligned selected controls only; no official benchmark section numbers claimed.
Exit: 0=no HIGH/CRITICAL with >=80% coverage; 1=HIGH/CRITICAL findings;
      2=incomplete; 3=usage/runtime failure; 130/143=interrupted.
HELP
}
parse_args() {
  local raw
  while (( $# )); do
    case "$1" in
      --full) FULL=1;; --summary) FULL=0;; --quick) MODE=quick; DEEP=0;; --standard) MODE=standard; DEEP=0;; --deep) MODE=deep; DEEP=1;;
      --demo) DEMO=1;; --self-test) SELF_TEST=1;; --no-color) COLOR=never;;
      --output-dir|--export-json|--profile|--scan-seconds|--ssh-context|--sshd-config|--color|--disk-thresholds|--critical-caps)
        (( $#>=2 )) || die "Missing value: $1"
        case "$1" in
          --output-dir) OUT=$2;; --export-json) JSON_PATH=$2;; --profile) PROFILE=$2;; --scan-seconds) SCAN_SECONDS=$2;;
          --ssh-context) SSH_CONTEXT=$2;; --sshd-config) SSH_CONFIG=$2;; --color) COLOR=$2;;
          --disk-thresholds) raw=$2; [[ $raw =~ ^[0-9]{1,3},[0-9]{1,3},[0-9]{1,3}$ ]] || die 'Invalid disk thresholds'; IFS=, read -r DISK_WARN DISK_HIGH DISK_CRITICAL <<< "$raw";;
          --critical-caps) raw=$2; [[ $raw =~ ^[0-9]{1,3},[0-9]{1,3},[0-9]{1,3}$ ]] || die 'Invalid caps'; IFS=, read -r CAP_ONE CAP_TWO CAP_THREE <<< "$raw";;
        esac; shift;;
      --export-html) if (( $#>=2 )) && [[ $2 != --* ]]; then HTML_PATH=$2; shift; else [[ -n $OUT ]] || OUT="$PWD/security-audit-$(date -u +%Y%m%dT%H%M%SZ)-$$"; fi;;
      --version) printf '%s\n' "$VERSION"; exit 0;; --help|-h) usage; exit 0;; *) die "Unknown option: $1";;
    esac
    shift
  done
  [[ $SCAN_SECONDS =~ ^[0-9]{1,3}$ ]] && ((10#$SCAN_SECONDS>=5 && 10#$SCAN_SECONDS<=300)) || die 'scan-seconds must be 5..300'
  SCAN_SECONDS=$((10#$SCAN_SECONDS))
  DISK_WARN=$((10#$DISK_WARN)); DISK_HIGH=$((10#$DISK_HIGH)); DISK_CRITICAL=$((10#$DISK_CRITICAL))
  CAP_ONE=$((10#$CAP_ONE)); CAP_TWO=$((10#$CAP_TWO)); CAP_THREE=$((10#$CAP_THREE))
  ((0<=DISK_WARN && DISK_WARN<DISK_HIGH && DISK_HIGH<DISK_CRITICAL && DISK_CRITICAL<=100)) || die 'Thresholds must increase within 0..100'
  ((100>=CAP_ONE && CAP_ONE>=CAP_TWO && CAP_TWO>=CAP_THREE && CAP_THREE>=0)) || die 'Caps must decrease within 0..100'
  [[ $PROFILE == cis-l1 || $PROFILE == cis-l2 || $PROFILE == enterprise || $PROFILE == full ]] || die 'Invalid profile'
  [[ $COLOR == auto || $COLOR == always || $COLOR == never ]] || die 'Invalid color mode'
  [[ -z $SSH_CONTEXT || $SSH_CONTEXT =~ ^[a-zA-Z0-9_.:,=@%/-]+$ ]] || die 'Invalid SSH context'
  [[ -z $SSH_CONFIG || ( $SSH_CONFIG == /* && -f $SSH_CONFIG ) ]] || die 'sshd config requires absolute file path'
  [[ -z $OUT || $OUT == /* ]] || die 'output-dir requires absolute path'
  [[ -z $OUT || ! $OUT =~ (^|/)\.\.?(/|$) ]] || die 'Noncanonical output path'
  return 0
}
write_export() {
  local kind=$1 path=$2 oldout=$OUT
  [[ $path == /* ]] || path="$PWD/$path"
  [[ ! $path =~ (^|/)\.\.?(/|$) ]] || die 'Use canonical report paths'
  [[ ! -e $path && ! -L $path ]] || die "Report already exists: $path"
  OUT=$path; safe_output_parent; OUT=$oldout
  local staged
  staged=$(mktemp "${path%/*}/.audit-export.XXXXXXXX") || die 'Cannot stage export'
  EXPORT_STAGE=$staged
  if "export_$kind" > "$staged"; then :; else die "Could not generate $kind report"; fi
  ln -- "$staged" "$path" || die "Could not atomically create $kind report (target exists or crosses filesystems)"
  rm -f -- "$staged"; EXPORT_STAGE=''
}
self_test() (
  local id version family expected output i=0
  printf 'SELF-TEST: isolated local fixtures\n'
  while IFS='|' read -r id version expected; do
    printf 'ID=%s\nVERSION_ID="%s"\nNAME="CentOS Stream"\n' "$id" "$version" > "$TMP/os-release"
    detect_os "$TMP/os-release"
    [[ $DISTRO_FAMILY == "$expected" ]] || { printf 'FAIL OS %s\n' "$id"; return 3; }; i=$((i+1))
  done <<'FIXTURES'
ubuntu|20.04|Debian
ubuntu|22.04|Debian
ubuntu|24.04|Debian
ubuntu|26.04|Debian
debian|11|Debian
debian|12|Debian
debian|13|Debian
rhel|8.10|RHEL
rhel|9.6|RHEL
rhel|10.0|RHEL
rocky|9.6|RHEL
almalinux|9.6|RHEL
ol|8.10|RHEL
ol|9.6|RHEL
ol|10.0|RHEL
centos|9|RHEL
centos|10|RHEL
opensuse-leap|15.6|SUSE
opensuse-leap|16.0|SUSE
sles|15.6|SUSE
sles|16.0|SUSE
alpine|3.22|Unknown
FIXTURES
  printf 'PASS OS detection: %d fixtures\n' "$i"
  COLOR=never; colors; [[ -z $RESET ]] || return 3
  UNICODE=0; [[ $(bar 50 4) == '##..' ]] || return 3
  printf 'PASS color/ASCII fallback\n'
  emit TEST-001 SSH PASS HIGH 1 'fixture' 'safe' 'none'
  unknown TEST-002 SSH HIGH 1 'fixture unknown' 'missing' 'none'
  emit TEST-003 SSH NA HIGH 1 'fixture NA' 'absent' 'none'
  calculate
  [[ $SCORE == 100 && $COVERAGE == 50 ]] || return 3
  [[ $(public_status FAIL CRITICAL) == CRITICAL ]] || return 3
  emit TEST-004 SSH FAIL CRITICAL 1 'fixture critical' 'safe' 'none'
  calculate; ((SCORE<=CAP_ONE)) || return 3
  printf 'PASS scoring, applicability and severity\n'
  export_json > "$TMP/self-test.json"
  if have jq; then jq -e '.schema_version==2 and (.findings|length)==4' "$TMP/self-test.json" >/dev/null || return 3
  elif have python3; then python3 -c 'import json,sys; x=json.load(open(sys.argv[1])); assert x["schema_version"]==2' "$TMP/self-test.json" || return 3
  else printf 'LIMITED JSON parser absent; escaping fixture checked\n'; [[ $(json_string 'a"b') == '"a\"b"' ]] || return 3; fi
  printf 'PASS JSON generation\n'
  [[ -d $TMP && $(stat -c %a "$TMP") == 700 ]] || return 3
  snapshot absent auditor_nonexistent_optional_command
  [[ $(<"$TMP/absent.rc") == 127 ]] || return 3
  ((EUID==0)) && output=ROOT || output=LIMITED
  printf 'PASS temp directory, required tools, optional-command isolation, privilege detection (%s)\n' "$output"
  return 0
)
main() {
  (( BASH_VERSINFO[0]>4 || (BASH_VERSINFO[0]==4 && BASH_VERSINFO[1]>=4) )) || die 'Requires Bash 4.4+'
  parse_args "$@"
  local required module rc=0
  for required in awk grep sed find stat df timeout mktemp tr cut head sort wc date readlink; do have "$required" || die "Missing required standard utility: $required"; done
  START=$(date +%s); colors
  TMP=$(mktemp -d /tmp/linux-security-audit.XXXXXXXX) || die 'Cannot create private temp directory'
  trap '[[ -n ${EXPORT_STAGE:-} && -f $EXPORT_STAGE ]] && rm -f -- "$EXPORT_STAGE"; [[ -n ${TMP:-} && -d $TMP ]] && rm -rf -- "$TMP"' EXIT
  trap 'exit 130' INT; trap 'exit 143' TERM
  if ((SELF_TEST)); then self_test; return $?; fi
  if [[ -n $OUT ]]; then safe_output_parent; mkdir -m 700 -- "$OUT" || die 'Report directory already exists or parent unavailable'; fi
  if ((DEMO)); then demo
  else
    # Module status is handled explicitly; optional command errors never abort orchestration.
    for module in audit_identity audit_enterprise_identity audit_accounts audit_user_extended audit_pam_sudo audit_ssh audit_ssh_extended audit_patching audit_firewall_network audit_network_extended audit_filesystem audit_mount_extended audit_permission_extended audit_persistence audit_persistence_extended audit_kernel audit_kernel_extended audit_logging_isolation audit_mac audit_logging_extended audit_integrity audit_recovery audit_processes_cloud_resources audit_containers audit_crypto_secrets; do
      progress "$module"
      if "$module"; then :; else printf 'Note: %s completed with unavailable optional evidence.\n' "$module" >&2; fi
    done
  fi
  if calculate; then :; else die 'Scoring engine failed'; fi
  [[ -t 2 ]] && printf '\r\033[2K' >&2
  if dashboard; then :; else die 'Dashboard failed'; fi
  [[ -n $JSON_PATH ]] && write_export json "$JSON_PATH"
  [[ -n $HTML_PATH ]] && write_export html "$HTML_PATH"
  if [[ -n $OUT ]]; then
    write_export json "$OUT/report.json"; write_export html "$OUT/report.html"
    RESET='' BOLD='' RED='' GREEN='' YELLOW='' CYAN='' GRAY='' ORANGE=''; UNICODE=0
    local oldfull=$FULL; FULL=1
    (set -o noclobber; dashboard > "$OUT/report.txt") || die 'Text export failed'; FULL=$oldfull
  fi
  if (( ${COUNTS[CRITICAL]:-0}>0 || ${COUNTS[HIGH]:-0}>0 )); then return 1; fi
  ((COVERAGE<80)) && return 2
  return 0
}
if [[ ${BASH_SOURCE[0]} == "$0" ]]; then main "$@"; fi
