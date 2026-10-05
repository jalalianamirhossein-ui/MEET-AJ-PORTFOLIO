# Validation and support matrix

No distribution is labeled Fully Supported without native integration coverage of its security subsystems. OS detection fixtures are not equivalent to full OS testing.

| Distribution | Versions | Support | Evidence / limitations |
|---|---|---|---|
| Ubuntu | 24.04 | Partial; live execution verified | Ubuntu 24.04 container, root, Quick/Standard; many host services/capabilities absent |
| Ubuntu | 20.04, 22.04, 26.04 | Partial | Adapter and release detection fixtures; no native integration run |
| Debian | 11, 12, 13 | Partial | APT/DPKG adapter; OS fixtures; no native integration run |
| RHEL | 8, 9, 10 | Partial | DNF/RPM/SELinux adapter and command fixtures; no native integration run |
| Rocky, AlmaLinux | 8, 9, 10 | Partial | RHEL-family dispatch; no complete per-release native validation |
| Oracle Linux | 8, 9, 10 | Partial | OL detection fixtures; UEK/RHCK newest-kernel correlation remains UNKNOWN |
| CentOS Stream | 9, 10 | Partial | Stream detection; security advisory metadata availability can limit results |
| openSUSE Leap, SLES | Leap 15/16, SLES 15/16 families | Partial | Zypper/RPM/MAC adapter; requires read-only bwrap execution for package queries |
| Other Linux / non-Linux | Outside target matrix | Unsupported | Generic observations may run; no distro-specific assurance |

Fully Supported: none across the entire requested checklist. Some semantic checks deliberately require external evidence and remain UNKNOWN.

- `bash -n`: passed.
- `--self-test`: passed, 22 OS detection fixtures, ASCII/color, scoring, statuses, JSON, private temp path, required/optional utilities and privilege detection.
- Unit/regression suite: 67 tests (46 for v2 behavior and 21 against the preserved v1 baseline).
- ShellCheck: unavailable in execution environment; no successful ShellCheck result claimed.
- Native non-root integration: blocked by execution environment, which maps only UID/GID 0. Non-root code paths exist, but no successful native non-root run claimed.
- Syscall-level read-only tracing: blocked because ptrace is unavailable. No claim of successful strace verification.
- HTML: standalone, responsive CSS, print CSS, escaped evidence, CSP and no external assets. Browser-rendered visual verification unavailable because a browser binary is not installed.
- Root live report is from the execution container, not the user's server. A risk exit code of 1 is expected when findings exist; it is not a crash.

The supplied package contains `security-audit.sh` and this document. The historical live JSON/HTML/text reports, catalogue and 67-test regression suite referenced by the original validation record are not included here, so those historical claims cannot be independently reproduced from this package alone. The observed registry below separates automated controls, inventory and manual evidence gaps. Numbers are scoped to the recorded run; optional backends can emit additional observations. Do not equate record count with all checks successfully verified.

## Observed control registry

inventory: 108, automated_check: 293, manual_evidence_required: 24, coverage: 2

| Category | Automated checks | All records |
|---|---:|---:|
| Audit | 11 | 11 |
| Authentication | 23 | 27 |
| Cloud | 0 | 3 |
| Containers | 29 | 36 |
| Cryptography | 4 | 9 |
| DNS | 0 | 2 |
| Filesystem | 53 | 73 |
| Firewall | 4 | 13 |
| Kernel | 53 | 57 |
| Logging | 10 | 16 |
| MAC | 5 | 10 |
| Network | 4 | 14 |
| PAM | 4 | 19 |
| Patching | 12 | 19 |
| Permissions | 25 | 25 |
| Persistence | 17 | 25 |
| Recovery | 2 | 8 |
| Resources | 2 | 5 |
| SSH | 23 | 27 |
| Sudo | 5 | 9 |
| System | 0 | 5 |
| Systemd | 1 | 5 |
| Threat | 6 | 9 |

Automated-check counts describe implemented local evaluation paths; they do not mean all paths passed or had sufficient evidence on this host. Inventory and manual gaps are excluded from the 293 automated-check total.

## Actual execution samples

- `KERNEL-001` / PASS: Observed: 2; expected: 2
- `FS-015` / HIGH: Insecure paths=2; reviewed=5; inaccessible=4
- `MAC-010` / UNKNOWN: Inspection unavailable; module enabled=unknown; expected MAC=AppArmor
- `PATCH-011` / UNKNOWN: Vendor support end/extended entitlement not established from local data

Live summary: {"total_checks": 427, "verdict": "HIGH RISK FINDINGS", "CRITICAL": 0, "HIGH": 2, "MEDIUM": 31, "LOW": 18, "PASS": 108, "WARN": 40, "FAIL": 11, "UNKNOWN": 149, "NA": 66, "INFO": 53}

## Version 2 engine and CLI interpretation

- Tool version: `2.0.0`; GNU/Linux and Bash 4.4+ are required.
- The final `emit` and `calculate` definitions implement severity weights: CRITICAL=10, HIGH=6, MEDIUM=3, LOW=1; explicit weight-zero observations remain unscored. Early category weights are legacy declarations and do not determine the active score.
- PASS earns full weight, WARN half, FAIL zero. UNKNOWN is excluded from the score denominator but reduces weighted evidence coverage. NA/INFO are excluded. Integer percentages truncate; critical caps default to 85/75/65 for 1/2/3+ critical findings.
- Public statuses are PASS, INFO, WARNING, HIGH, CRITICAL, UNKNOWN and NOT_APPLICABLE. LOW/MEDIUM are severities, not public statuses. Internal WARN/FAIL/NA are retained in JSON as `state`.
- `--deep` selects scan cost, `--full` selects terminal detail, and `--profile full` selects policy coverage. The default enterprise profile excludes controls labeled CIS Level 2. No official CIS certification or verified benchmark section mapping is claimed.
- SUSE detection accepts broader release strings than the matrix above. Detection is not evidence of native support for those releases. SLES SAP dispatch exists but has no dedicated native validation recorded here.
- Standard scans are bounded; deep does not remove all pattern-specific limits. Missing evidence remains UNKNOWN.

## Reproducible execution examples

Run on the intended Linux host after reviewing the source. Report destinations must be new; parents must already exist. Root exports require trusted root-owned ancestry without symlink components. A relative filename under an untrusted working directory can be rejected even with sudo.

```bash
chmod +x security-audit.sh
./security-audit.sh --version
./security-audit.sh --help
bash -n security-audit.sh
./security-audit.sh --self-test
sudo ./security-audit.sh --quick --summary
sudo ./security-audit.sh --standard --full --no-color
sudo ./security-audit.sh --deep --profile full --scan-seconds 60
sudo ./security-audit.sh --export-json /root/audit.json
sudo ./security-audit.sh --export-html /root/audit.html
sudo ./security-audit.sh --output-dir /root/audit-new
sudo ./security-audit.sh --ssh-context 'user=admin,host=admin.example,addr=192.0.2.10' --sshd-config /etc/ssh/sshd_config
```

Use actual SSH identities and source addresses instead of the example values. `--help` is the complete CLI reference. No `--export-text` option exists; detailed text is emitted by `--output-dir`.

Exit 0 means no HIGH/CRITICAL findings and at least 80% coverage; it does not impose a minimum score. Exit 1 takes precedence when HIGH/CRITICAL findings exist; exit 2 means incomplete evidence; exit 3 means usage/runtime failure; 130/143 indicate interruption.

## Article integration validation, 2026-10-05

The historical Linux integration evidence above is distinct from the current Windows workspace checks. Git for Windows Bash passed syntax validation and the isolated self-test (22 OS fixtures, scoring/statuses, color/ASCII, private temp and optional-command handling). The self-test used its limited JSON escaping fallback because no JSON parser was available to that Bash environment. This is not native GNU/Linux host integration, and no new distribution support is claimed. WSL enumeration was denied; no Linux live audit was run in this workspace.

ShellCheck remains unverified. If available on staging, run `shellcheck security-audit.sh`; this is a suggested check, not a passing result. Native non-root Linux integration, syscall tracing and full distribution integration still require independent execution.
