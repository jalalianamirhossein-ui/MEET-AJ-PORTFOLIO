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
