<?php

// Editorial priority, not publication dates. Missing slugs are harmless.
// Add a new enterprise article here when it should lead the library.
return [
    'enterprise' => [
        'deploy-msi-active-directory-group-policy',
        'mikrotik-pbr-client',
        'mikrotik-ping-triggered-policy-routing',
        'linux-security-auditor-bash',
        'mongodb-installation-configuration-production-deployment',
        'netbox-installation-setup-ubuntu',
        'oxidized-network-device-configuration-backup',
        'nginx-installation-configuration-ubuntu',
        'linux-security-account-access-management',
        'mikrotik-unequal-dual-wan-load-balancing-ecmp',
        'sql-server-automatic-backup-job',
        'vsphere-standard-switch-vs-distributed-switch',
        'mikrotik-openvpn-setup-v7',
    ],
    // New articles not explicitly classified appear between these two groups,
    // newest first. Promote an enterprise article into the list above as needed.
    'guides' => [
        'enable-ssh-linux-complete-guide',
        'set-static-ip-ubuntu-server-netplan',
        'linux-cli-common-commands',
        'install-mikrotik-chr-vmware-workstation',
        'install-vmware-esxi-vmware-workstation-vmcisr',
        'install-dfs-server-windows-server',
        'http-vs-https-ssl-certificate-impact',
        'mikrotik-block-port-scanners',
        'mikrotik-block-website',
        'downgrade-mikrotik-routeros-firmware-safely',
        'windows-cmd-common-network-commands',
        'windows-password-reset-secure-access-recovery',
        'ubuntu-date-time-settings',
        'imap-vs-pop3-email-protocol-comparison',
        'windows-hardware-info-cmd-vs-dxdiag',
        'vmware-esxi-8-installation-basic-configuration',
        'creating-a-bootable-usb',
    ],
];
