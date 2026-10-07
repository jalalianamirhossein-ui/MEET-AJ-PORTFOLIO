# Example only; review prerequisites and firewall ordering before applying.
/interface bridge
add name=PBR-Control protocol-mode=none comment="PBR Control Interface"
/ip address
add address=10.255.255.1/32 interface=PBR-Control comment="Enable PBR"
add address=10.255.255.2/32 interface=PBR-Control comment="Disable PBR"

/ip firewall address-list
add list=LOCAL-NETS address=192.168.10.0/24 comment="LAN example"
add list=LOCAL-NETS address=192.168.20.0/24 comment="Internal VLAN example"
add list=PBR-ELIGIBLE address=192.168.10.0/24 comment="Stable FastTrack exclusion for trigger-capable clients"

/routing table
add fib name=VPN-ROUTE
/ip route
add dst-address=0.0.0.0/0 gateway=l2tp-vpn-example \
    routing-table=VPN-ROUTE comment="PBR default route"

/ip firewall mangle
add chain=prerouting in-interface-list=LAN src-address=192.168.10.0/24 \
    src-address-list=!PBR-REMOVE protocol=icmp icmp-options=8:0 \
    dst-address=10.255.255.1 action=add-src-to-address-list \
    address-list=PBR-ACTIVE address-list-timeout=none-dynamic \
    passthrough=yes comment="Enable PBR using ICMP trigger"

/ip firewall mangle
add chain=prerouting in-interface-list=LAN src-address=192.168.10.0/24 \
    protocol=icmp icmp-options=8:0 dst-address=10.255.255.2 \
    action=add-src-to-address-list address-list=PBR-REMOVE \
    address-list-timeout=10s passthrough=yes comment="Disable PBR request"

/ip firewall mangle
add chain=prerouting in-interface-list=LAN src-address-list=PBR-ACTIVE \
    dst-address-list=!LOCAL-NETS dst-address-type=!local \
    action=mark-routing new-routing-mark=VPN-ROUTE passthrough=no \
    comment="Route PBR clients using VPN-ROUTE"

/system script
add name=PBR-Remove-Worker policy=read,write,test dont-require-permissions=no source={
    :if ([:len [/system script job find where script="PBR-Remove-Worker"]] > 1) do={
        :return
    }
    :foreach request in=[/ip firewall address-list find where list="PBR-REMOVE"] do={
        :local clientIP ""
        :do {
            :set clientIP [/ip firewall address-list get $request address]
            :local active [/ip firewall address-list find where list="PBR-ACTIVE" and address=$clientIP]
            :if ([:len $active] > 0) do={
                /ip firewall address-list remove $active
                :log info ("PBR disabled for client " . $clientIP)
            }
            /ip firewall address-list remove $request
        } on-error={
            :if ([:len [/ip firewall address-list find where list="PBR-REMOVE" and address=$clientIP]] > 0) do={
                :log warning ("PBR removal failed; queued retry for " . $clientIP)
            }
        }
    }
}

/system scheduler
add name=PBR-Remove-Scheduler start-time=startup interval=1s \
    on-event=PBR-Remove-Worker policy=read,write,test \
    comment="Process queued PBR disable requests"

/ip firewall address-list
add list=PBR-CONTROL-IPS address=10.255.255.1 comment="Enable trigger destination"
add list=PBR-CONTROL-IPS address=10.255.255.2 comment="Disable trigger destination"
/ip firewall filter
add chain=input in-interface-list=LAN src-address=192.168.10.0/24 \
    dst-address-list=PBR-CONTROL-IPS protocol=icmp icmp-options=8:0 \
    action=accept comment="Allow authorized PBR echo requests"
add chain=input dst-address-list=PBR-CONTROL-IPS action=drop \
    comment="Deny other access to PBR control addresses"

/ip firewall nat
add chain=srcnat src-address-list=PBR-ACTIVE out-interface=l2tp-vpn-example \
    action=masquerade comment="NAT PBR traffic"
