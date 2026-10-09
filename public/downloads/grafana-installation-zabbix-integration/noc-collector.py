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
