"""Rebuild authored Grafana configuration downloads; never execute installation commands."""
from pathlib import Path
import json
import shutil
import uuid

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'grafana-installation-zabbix-integration'
SOURCE = ROOT / 'resources/content/articles' / SLUG
SOURCE.mkdir(parents=True, exist_ok=True)

def write(name, content):
    (SOURCE / name).write_text(content.strip()+'\n', encoding='utf-8', newline='\n')

write('grafana.ini', r'''
[paths]
data = /var/lib/grafana
logs = /var/log/grafana
plugins = /var/lib/grafana/plugins
provisioning = /etc/grafana/provisioning
[server]
protocol = http
http_addr = 127.0.0.1
http_port = 3000
domain = grafana.example.com
enforce_domain = true
root_url = https://grafana.example.com/
[database]
type = sqlite3
path = grafana.db
[security]
secret_key = $__file{/etc/grafana/secrets/secret_key}
cookie_secure = true
cookie_samesite = lax
disable_gravatar = true
[users]
allow_sign_up = false
auto_assign_org_role = Viewer
[auth.anonymous]
enabled = false
[log]
mode = console file
level = info
[dashboards]
min_refresh_interval = 30s
''')
write('nginx-grafana.conf', r'''
# Include inside nginx's http context (Ubuntu sites-enabled is in http).
map $http_upgrade $grafana_connection_upgrade {
    default upgrade;
    '' close;
}
upstream grafana_backend {
    server 127.0.0.1:3000;
    keepalive 16;
}
server {
    listen 80;
    listen [::]:80;
    server_name grafana.example.com;
    location ^~ /.well-known/acme-challenge/ {
        root /var/www/acme;
    }
    location / { return 301 https://grafana.example.com$request_uri; }
}
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name grafana.example.com;
    ssl_certificate /etc/letsencrypt/live/grafana.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/grafana.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_session_cache shared:GrafanaTLS:10m;
    ssl_session_timeout 1d;
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header X-Content-Type-Options nosniff always;
    # Optional management/VPN allowlist, ONLY after ACME/bootstrap works:
    # allow 192.0.2.0/24;
    # deny all;
    location / {
        proxy_pass http://grafana_backend;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $remote_addr;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header Connection "";
        proxy_read_timeout 60s;
    }
    location /api/live/ {
        proxy_pass http://grafana_backend;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $remote_addr;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $grafana_connection_upgrade;
        proxy_read_timeout 3600s;
    }
}
''')
write('nginx-bootstrap.conf', r'''
server {
    listen 80;
    listen [::]:80;
    server_name grafana.example.com;
    location ^~ /.well-known/acme-challenge/ { root /var/www/acme; }
    location / { return 404; }
}
''')
write('zabbix-datasource.yaml', '''
apiVersion: 1
datasources:
  - name: Zabbix
    uid: zabbix-enterprise
    type: alexanderzobnin-zabbix-datasource
    access: proxy
    url: ${ZABBIX_API_URL}
    isDefault: true
    jsonData:
      authType: token
      trends: true
      trendsFrom: 7d
      trendsRange: 4d
      cacheTTL: 1h
      timeout: 30
      queryTimeout: 60
      dbConnectionEnable: false
      disableReadOnlyUsersAck: true
      disableDataAlignment: false
    secureJsonData:
      apiToken: $ZABBIX_API_TOKEN
    version: 1
    editable: false
''')
write('zabbix-app.yaml', '''
apiVersion: 1
apps:
  - type: alexanderzobnin-zabbix-app
    org_id: 1
    disabled: false
''')
write('grafana.env.example', '''
# Install as /etc/grafana/grafana-integration.env, root:root 0600.
# Replace locally; never publish the edited file.
ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php
ZABBIX_API_TOKEN=REPLACE_LOCALLY_WITH_EXPIRING_READ_ONLY_TOKEN
''')
write('grafana-environment.conf', '''
[Service]
EnvironmentFile=/etc/grafana/grafana-integration.env
''')
write('dashboard-provider.yaml', '''
apiVersion: 1
providers:
  - name: Enterprise monitoring
    orgId: 1
    folder: Enterprise monitoring
    type: file
    disableDeletion: true
    updateIntervalSeconds: 60
    allowUiUpdates: false
    options:
      path: /var/lib/grafana/dashboards
''')

DS = {'type': 'alexanderzobnin-zabbix-datasource', 'uid': 'zabbix-enterprise'}
def query(item='', host='$host', group='$group', query_type='0'):
    return {'schema': 13, 'refId': 'A', 'datasource': DS, 'queryType': query_type,
            'group': {'filter': group}, 'host': {'filter': host},
            'application': {'filter': ''}, 'itemTag': {'filter': ''},
            'item': {'filter': item}, 'macro': {'filter': ''}, 'proxy': {'filter': ''},
            'trigger': {'filter': ''}, 'tags': {'filter': ''}, 'problemTags': [],
            'textFilter': '', 'itemids': '', 'mode': 0, 'useCaptureGroups': False,
            'functions': [], 'options': {'useTrends': 'default', 'showDisabledItems': False,
            'skipEmptyValues': False, 'disableDataAlignment': False, 'useZabbixValueMapping': False}}

def variable(name, q, multi=False):
    return {'name': name, 'label': name.replace('_',' ').title(), 'type': 'query',
            'datasource': DS, 'query': q, 'refresh': 1, 'sort': 1,
            'multi': multi, 'includeAll': multi, 'allValue': '/.*/' if multi else None,
            'current': {'text':'All','value':'$__all'} if multi else {},
            'options': [{'text':'All','value':'$__all','selected':True}] if multi else []}

def panel(id, title, item, kind='timeseries', unit='percent', host='$host', group='$group'):
    options = {'legend': {'displayMode': 'table', 'placement': 'bottom', 'calcs': ['lastNotNull']},
               'tooltip': {'mode': 'multi', 'sort': 'desc'}} if kind=='timeseries' else {
               'reduceOptions': {'values': False, 'calcs': ['lastNotNull'], 'fields': ''},
               'orientation': 'auto', 'colorMode': 'value', 'graphMode': 'none', 'textMode': 'auto'}
    defaults = {'unit': unit, 'mappings': [], 'noValue': 'No data',
                'color': {'mode': 'palette-classic' if kind=='timeseries' else 'thresholds'},
                'thresholds': {'mode': 'absolute', 'steps': [{'color': 'green', 'value': None}]}}
    if unit=='percent':
        defaults.update(min=0, max=100)
        defaults['thresholds']['steps'] += [{'color': 'orange','value':80}, {'color':'red','value':90}]
    return {'id': id, 'title': title, 'type': kind, 'datasource': DS, 'pluginVersion': '13.2.3',
            'gridPos': {'h': 8, 'w': 12, 'x': ((id-1)%2)*12, 'y': ((id-1)//2)*8},
            'targets': [query(item,host,group)], 'fieldConfig': {'defaults': defaults, 'overrides': []},
            'options': options}

def problems(id, host='$host', group='$group'):
    result = panel(id,'Active Zabbix problems','', 'alexanderzobnin-zabbix-triggers-panel','none',host,group)
    q = query('',host,group,'5')
    q.update(showProblems='problems', resultFormat='table', evaltype='0')
    q['options'] = {'acknowledged': 2, 'minSeverity': 0, 'severities': [0,1,2,3,4,5],
                    'count': False, 'limit': 1001, 'useTimeRange': False,
                    'hostsInMaintenance': True, 'fetchHistoricalItemValue': False, 'sortProblems': 'lastchange'}
    result['targets'] = [q]
    result['options'] = {'layout':'table', 'fontSize':'100%', 'pageSize':10,
                        'hostField':True, 'hostGroups':True, 'severityField':True,
                        'statusField':True, 'ackField':True, 'descriptionField':True,
                        'showTags':True, 'allowDangerousHTML':False, 'sortProblems':'lastchange'}
    return result

def dashboard(os, metrics):
    variables = [variable('group',{'queryType':'group','group':f'/{os} servers/'}),
                 variable('host',{'queryType':'host','group':'$group','host':'/.*/'}),
                 variable('item_group',{'queryType':'itemTag','group':'$group','host':'$host','itemTag':'/component:.*/'},True),
                 variable('interface',{'queryType':'item','group':'$group','host':'$host','item':'/Interface .*: Bits received/'},True)]
    # All must still select inbound interface items, not every metric on the host.
    variables[-1]['allValue']='/Interface .*: Bits received/'
    # Interface is the full received-item name. No brittle extraction of Windows GUIDs.
    panels=[panel(i+1,*m) for i,m in enumerate(metrics)]
    for p in panels:
        if p['type'] not in ('table',): p['targets'][0]['itemTag']['filter'] = '$item_group'
    network = panel(len(panels)+1,'Selected interface: inbound traffic','$interface','timeseries','bps')
    network['targets'][0]['itemTag']['filter']=''
    panels += [network, problems(len(panels)+2)]
    return {'id':None,'uid':f'enterprise-{os.lower()}','title':f'{os} Server Monitoring',
            'description':'Authored importable dashboard; select existing discovered items. No simulated metric values.',
            'schemaVersion':39,'version':1,'timezone':'browser','editable':True,'tags':['Grafana','Zabbix',os],
            'time':{'from':'now-6h','to':'now'},'refresh':'1m','graphTooltip':1,
            'templating':{'list':variables},'annotations':{'list':[]},'panels':panels,'links':[]}

linux=[('CPU utilization','CPU utilization','gauge','percent'),
       ('CPU load (1m, 5m, 15m)','/Load average/','timeseries','none'),
       ('RAM utilization','Memory utilization','gauge','percent'),
       ('Available memory','Available memory','timeseries','bytes'),
       ('Filesystem usage','/.*: Space: Used, in %/','gauge','percent'),
       ('Filesystem available space','/.*: Space: Available/','timeseries','bytes'),
       ('Network bandwidth / interface traffic','/Interface .*: Bits (received|sent)/','timeseries','bps'),
       ('System uptime','System uptime','stat','s'),
       ('Zabbix agent availability','Zabbix agent availability','stat','none')]
windows=[('CPU utilization','CPU utilization','gauge','percent'),
         ('RAM utilization','Memory utilization','gauge','percent'),
         ('Used memory','Used memory','timeseries','bytes'),
         ('Disk utilization','/.*: Space: Used, in %/','gauge','percent'),
         ('Filesystem free space','/.*: Space: Available/','timeseries','bytes'),
         ('Network throughput','/Interface .*: Bits (received|sent)/','timeseries','bps'),
         ('Windows service state','/State of service/','stat','none'),
         ('System uptime','Uptime','stat','s'),
         ('Zabbix agent availability','Zabbix agent availability','stat','none'),
         ('Windows Event Log (custom active item)','Windows Application errors','table','none')]
for os, metrics in [('Linux',linux),('Windows',windows)]:
    d=dashboard(os,metrics)
    for p in d['panels']:
        if 'availability' in p['title']:
            p['fieldConfig']['defaults']['mappings']=[{'type':'value','options':{
                '0':{'text':'Unknown','color':'orange'},'1':{'text':'Available','color':'green'},
                '2':{'text':'Unavailable','color':'red'}}}]
        if p['title']=='Windows service state':
            p['description']='service.info state: 0 running, 6 stopped, 255 absent. Adapt filter to discovered service names.'
            p['fieldConfig']['defaults']['mappings']=[{'type':'value','options':{
                '0':{'text':'Running','color':'green'},'6':{'text':'Stopped','color':'red'},
                '255':{'text':'Absent','color':'orange'}}}]
        if 'Event Log' in p['title']:
            p['targets'][0]['queryType']='2'; p['targets'][0]['itemTag']['filter']=''
    write(os.lower()+'-dashboard.json',json.dumps(d,ensure_ascii=False,indent=2))

counts=[('Total monitored hosts','NOC: Total monitored hosts'),
        ('Available hosts','NOC: Available hosts'),('Unavailable hosts','NOC: Unavailable hosts'),
        ('Unknown availability','NOC: Unknown availability'),('Active problems','NOC: Active problems'),
        ('Critical (Disaster) problems','NOC: Disaster problems'),('High severity problems','NOC: High problems'),
        ('Collector last success','NOC: Last successful collection')]
noc=dashboard('Enterprise',[])
noc.update(uid='enterprise-noc',title='Enterprise NOC',description='Overview counts require the included API collector and Zabbix trapper template. Counts refer to configured environment scope, independent of performance Group/Host filters.')
noc['templating']['list']=[{'name':'environment','label':'Environment','type':'custom','query':'Production,Staging',
                          'current':{'text':'Production','value':'Production'},'options':[
                          {'text':'Production','value':'Production','selected':True},
                          {'text':'Staging','value':'Staging','selected':False}], 'multi':False,'includeAll':False},
                         variable('group',{'queryType':'group','group':r'/^$environment\/.*/'},True),
                         variable('host',{'queryType':'host','group':'$group','host':'/.*/'},True)]
# Empty custom All combines the actual environment-filtered group options.
# An unbounded /.*/ here would include other permitted environments.
noc['templating']['list'][1]['allValue']=''
noc['panels']=[]
for i,(title,item) in enumerate(counts):
    p=panel(i+1,title,item,'stat','dateTimeAsIso' if i==7 else 'none','$environment NOC summary','NOC collectors')
    p['gridPos']={'h':4,'w':6,'x':(i%4)*6,'y':(i//4)*4}
    p['description']='Collector scope: '+('$environment')+'. Last-success panel must be fresh; stale numbers are not healthy monitoring.'
    if i==7:
        # Zabbix unixtime values are seconds; Grafana date units expect milliseconds.
        p['targets'][0]['functions']=[{'text':'scale(1000)','params':[1000],
                                     'def':{'name':'scale','category':'Transform',
                                            'params':[{'name':'factor','type':'float','options':[100,0.01,10,-1]}],
                                            'defaultParams':[100]}}]
    if i in (2,3,4,5,6):
        p['fieldConfig']['defaults']['thresholds']['steps'].append({'color':'orange' if i==3 else 'red','value':1})
    noc['panels'].append(p)
for i,(title,item) in enumerate([('Top CPU consumers','CPU utilization'),('Top memory consumers','Memory utilization'),
                                ('Top disk utilization','/.*: (Space utilization|Space: Used, in %)/'),
                                ('Network utilization','/Interface .*: Bits (received|sent)/')]):
    p=panel(9+i,title,item,'bargauge','bps' if i==3 else 'percent')
    p['gridPos']={'h':8,'w':12,'x':(i%2)*12,'y':8+(i//2)*8}
    p['transformations']=[{'id':'reduce','options':{'mode':'seriesToRows','reducers':['lastNotNull'],'includeTimeField':False}},
                           {'id':'sortBy','options':{'sort':[{'field':'Last *','desc':True}]}},
                           {'id':'limit','options':{'limitField':10}}]
    p['description']='Last value per returned series, sorted descending and limited to 10; tune field name if your Grafana locale renames Last *. Disk ranking is per filesystem; network is per interface, already bits/s.'
    p['options']['reduceOptions']['values']=True
    noc['panels'].append(p)
p=problems(13);p['gridPos']={'h':12,'w':24,'x':0,'y':24};noc['panels'].append(p)
write('enterprise-noc-dashboard.json',json.dumps(noc,indent=2))

write('grafana-backup.env.example', '''
# /etc/grafana/backup.env, root:root 0600. No passwords here.
BACKUP_ROOT=/srv/grafana-backups
GRAFANA_DB_TYPE=sqlite3
# For PostgreSQL Grafana metadata DB ONLY (not the Zabbix DB):
PGHOST=127.0.0.1
PGPORT=5432
PGDATABASE=grafana
PGUSER=grafana_backup
PGPASSFILE=/etc/grafana/secrets/pgpass
# Optional restic upload; initialize encrypted repository separately.
# RESTIC_REPOSITORY=sftp:backup.example.com:/srv/restic/grafana
# RESTIC_PASSWORD_FILE=/etc/grafana/secrets/restic-password
''')
write('grafana-backup.sh', r'''
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
''')
write('grafana-backup.service', '''
[Unit]
Description=Consistent Grafana metadata and configuration backup
After=network-online.target grafana-server.service
Wants=network-online.target
[Service]
Type=oneshot
User=root
EnvironmentFile=/etc/grafana/backup.env
ExecStart=/usr/local/sbin/grafana-backup.sh
UMask=0077
TimeoutStartSec=20min
PrivateTmp=true
ProtectHome=true
''')
write('grafana-backup.timer', '''
[Unit]
Description=Daily Grafana backup
[Timer]
OnCalendar=*-*-* 02:15:00 UTC
RandomizedDelaySec=15min
Persistent=true
Unit=grafana-backup.service
[Install]
WantedBy=timers.target
''')
write('noc-collector.py', r'''
#!/usr/bin/env python3
"""Read Zabbix API counts, submit to pre-created trapper items. No secret logging."""
import json
import os
import subprocess
import time
import urllib.request

def availability(host):
    # 0 unknown, 1 available, 2 unavailable. Active and passive checks are distinct.
    states = [int(host.get('active_available', 0))]
    states += [int(i.get('available', 0)) for i in host.get('interfaces', [])]
    return 1 if 1 in states else 2 if 2 in states else 0

def api(method, params):
    url = os.environ['ZABBIX_API_URL']
    if not url.startswith('https://'): raise ValueError('Trusted HTTPS endpoint required')
    token = open(os.environ['ZABBIX_TOKEN_FILE'], encoding='utf-8').read().strip()
    req = urllib.request.Request(url, data=json.dumps({'jsonrpc':'2.0','method':method,
              'params':params,'id':1}).encode(), headers={'Content-Type':'application/json-rpc',
              'Authorization':'Bearer '+token})
    with urllib.request.urlopen(req, timeout=30) as response:
        data=json.load(response)
    if 'error' in data: raise RuntimeError('Zabbix API rejected '+method)
    return data['result']

def collect():
    groups=os.environ['ZABBIX_GROUP_IDS'].split(',')
    hosts=api('host.get',{'output':['hostid','active_available'],'monitored_hosts':True,
                         'groupids':groups,'selectInterfaces':['available','type']})
    if not hosts: raise RuntimeError('Empty permitted host scope; refusing misleading zero counts')
    counts={'noc.hosts.total':len(hosts), 'noc.hosts.available':sum(availability(h)==1 for h in hosts),
            'noc.hosts.unavailable':sum(availability(h)==2 for h in hosts),
            'noc.hosts.unknown':sum(availability(h)==0 for h in hosts)}
    # Explicit hostids keeps problem scope identical to monitored-host scope.
    params={'hostids':[h['hostid'] for h in hosts],'countOutput':True,
            'source':0,'object':0,'recent':False}
    counts['noc.problems.active']=int(api('problem.get',params))
    counts['noc.problems.disaster']=int(api('problem.get',dict(params,severities=[5])))
    counts['noc.problems.high']=int(api('problem.get',dict(params,severities=[4])))
    counts['noc.last.success']=int(time.time())
    host=os.environ['ZABBIX_SUMMARY_HOST']
    if any(c.isspace() for c in host): raise ValueError('Use a technical host name without whitespace')
    payload='\n'.join(f'{host} {key} {value}' for key,value in counts.items())+'\n'
    command=['zabbix_sender','-z',os.environ['ZABBIX_SERVER'],'-s',host,
             '--tls-connect','psk','--tls-psk-identity',os.environ['ZABBIX_PSK_IDENTITY'],
             '--tls-psk-file',os.environ['ZABBIX_PSK_FILE'],'-i','-']
    result=subprocess.run(command,input=payload,text=True,capture_output=True,timeout=30)
    if result.returncode: raise RuntimeError('Sender rejected metrics; inspect trapper permissions and PSK')
    print('Submitted',len(counts),'NOC metrics')

if __name__=='__main__': collect()
''')
write('noc-collector.env.example', '''
# Install as /etc/grafana/noc.env, root:noc-collector 0640.
ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php
ZABBIX_TOKEN_FILE=/etc/grafana/secrets/noc-token
# IDs of all Production/* groups to count, from hostgroup.get; exclude NOC collectors.
ZABBIX_GROUP_IDS=REPLACE_WITH_COMMA_SEPARATED_GROUP_IDS
ZABBIX_SUMMARY_HOST=noc-production
ZABBIX_SERVER=192.0.2.10
ZABBIX_PSK_IDENTITY=noc-production
ZABBIX_PSK_FILE=/etc/grafana/secrets/noc.psk
''')
write('noc-collector.service', '''
[Unit]
Description=Zabbix API NOC summary collector
After=network-online.target
Wants=network-online.target
[Service]
Type=oneshot
User=noc-collector
Group=noc-collector
EnvironmentFile=/etc/grafana/noc.env
ExecStart=/usr/bin/python3 /usr/local/lib/grafana-noc/noc-collector.py
NoNewPrivileges=true
ProtectSystem=strict
ProtectHome=true
PrivateTmp=true
TimeoutStartSec=3min
''')
write('noc-collector.timer', '''
[Unit]
Description=Refresh NOC summary every minute
[Timer]
OnBootSec=2min
OnUnitInactiveSec=1min
Unit=noc-collector.service
[Install]
WantedBy=timers.target
''')
keys=['noc.hosts.total','noc.hosts.available','noc.hosts.unavailable','noc.hosts.unknown',
      'noc.problems.active','noc.problems.disaster','noc.problems.high','noc.last.success']
# JSON is a supported Zabbix template export format, not a Grafana dashboard.
template={'zabbix_export':{'version':'7.0','template_groups':[{'uuid':uuid.uuid5(uuid.NAMESPACE_URL,SLUG+'group').hex,'name':'Templates/Custom'}],
          'templates':[{'uuid':uuid.uuid5(uuid.NAMESPACE_URL,SLUG+'template').hex,'template':'Enterprise NOC summary',
          'name':'Enterprise NOC summary','groups':[{'name':'Templates/Custom'}],
          'items':[{'uuid':uuid.uuid5(uuid.NAMESPACE_URL,key).hex,'name':counts[i][1],
                    'type':'TRAP','key':key,'delay':'0','value_type':'UNSIGNED','history':'7d','trends':'365d',
                    'allowed_hosts':'{$NOC.SENDER.IP}', 'tags':[{'tag':'component','value':'noc'}],
                    **({'units':'unixtime','triggers':[{'uuid':uuid.uuid5(uuid.NAMESPACE_URL,SLUG+'stale').hex,
                    'expression':'nodata(/Enterprise NOC summary/noc.last.success,5m)=1',
                    'name':'NOC collector has not reported for 5 minutes','priority':'HIGH'}]} if i==7 else {})}
                    for i,key in enumerate(keys)],
          'macros':[{'macro':'{$NOC.SENDER.IP}','value':'192.0.2.20','description':'Source IP of authorized collector'}]}]}}
write('zabbix-noc-template.json',json.dumps(template,indent=2))
write('postgres-readonly.sql', r'''
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
''')

write('zabbix-api-probe.py', r'''
#!/usr/bin/env python3
import json
import os
import urllib.request

def request(method, params, token=None):
    url=os.environ['ZABBIX_API_URL']
    if not url.startswith('https://'): raise ValueError('HTTPS required')
    headers={'Content-Type':'application/json-rpc'}
    if token: headers['Authorization']='Bearer '+token
    req=urllib.request.Request(url, data=json.dumps({'jsonrpc':'2.0','method':method,
                              'params':params,'id':1}).encode(),headers=headers)
    with urllib.request.urlopen(req,timeout=30) as response: result=json.load(response)
    if 'error' in result: raise RuntimeError('API rejected '+method+'; inspect token/role/endpoint')
    return result['result']

if __name__=='__main__':
    print('API version:',request('apiinfo.version',{}))
    token=open(os.environ['ZABBIX_TOKEN_FILE'],encoding='utf-8').read().strip()
    if not token: raise ValueError('Token file is empty')
    print('Visible hosts:',request('host.get',{'countOutput':True},token))
''')

if __name__=='__main__': print('Built Grafana downloads and three authored dashboard JSON files')
