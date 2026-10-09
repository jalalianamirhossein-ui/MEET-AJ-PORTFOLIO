"""Validate article assets, download parity and runnable syntax without touching servers."""
from pathlib import Path
from html.parser import HTMLParser
import ast
import hashlib
import html
import json
import os
import re
import shutil
import subprocess
import sys
import zipfile
from PIL import Image

ROOT=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(ROOT/'storage/app/grafana-python-deps'))
import yaml
SLUG='grafana-installation-zabbix-integration'
SOURCE=ROOT/'resources/content/articles'/SLUG

class Inspect(HTMLParser):
    def __init__(self):
        super().__init__();self.nodes=[]
    def handle_starttag(self,tag,attrs):self.nodes.append((tag,dict(attrs)))

def main():
    source=(ROOT/'resources/legacy/articles'/f'{SLUG}.html').read_text(encoding='utf-8')
    metadata=json.loads((SOURCE/'metadata.json').read_text(encoding='utf-8'))
    parser=Inspect();parser.feed(source)
    ids=[a['id'] for tag,a in parser.nodes if 'id' in a]
    assert len(ids)==len(set(ids)),'Duplicate HTML ID'
    assert all(id in ids for id in metadata['sections'])
    for tag,a in parser.nodes:
        if tag in ['h1','h2','h3','th','td','li']:
            if a.get('class')=='article-nav-item':continue
            assert a.get('data-en') and a.get('data-fa'),(tag,a)
        if tag=='pre':assert a.get('dir')=='ltr'
        if tag=='a' and a.get('href','').startswith('#'):assert a['href'][1:] in ids
        if tag=='a' and a.get('href','').startswith('/articles/'):
            assert (ROOT/'resources/legacy'/ (a['href'][1:]+'.html')).exists()
        if tag=='a' and a.get('href','').startswith('/downloads/'):
            assert (ROOT/'public'/a['href'][1:]).exists()
        if tag=='img':
            assert a.get('data-en-alt') and re.search('[\u0600-\u06ff]',a.get('data-fa-alt',''))
    assert metadata['canonical_route']=='/articles/'+SLUG
    canonical=[a.get('href') for tag,a in parser.nodes if tag=='link' and a.get('rel')=='canonical']
    assert canonical==[metadata['canonical_url']], 'Canonical metadata mismatch'
    assert len(metadata['localizations']['en']['faq'])==len(metadata['localizations']['fa']['faq'])==8
    blocks={}
    for locale in ['en','fa']:
        md=(SOURCE/f'article.{locale}.md').read_text(encoding='utf-8')
        blocks[locale]=re.findall(r'```([^\n]*)\n(.*?)\n```',md,re.S)
        assert len(re.findall(r'^## [0-9۰-۹]+\.',md,re.M))==15
    assert blocks['en']==blocks['fa'],'Commands differ by locale'
    assert len(blocks['en'])>=40
    for _,a in parser.nodes:
        if 'data-en' in a:assert not re.search('[\u0600-\u06ff]',a['data-en'])
    for section,body in re.findall(r'<section id="([^"]+)">(.*?)</section>',source,re.S):
        if section=='troubleshooting':
            for label in ['Symptoms:','Root Cause:','Diagnostic Commands:','Expected Output:','Resolution:']:
                assert body.count('data-en="'+label)==12,label

    images=json.loads((SOURCE/'images.json').read_text(encoding='utf-8'))
    assert len(images)==7
    for info in images:
        folder='banners' if 'banner' in info['filename'] else 'content'
        p=ROOT/'resources/assets/img/articles'/folder/info['filename']
        with Image.open(p) as im:
            im.verify()
        with Image.open(p) as im:
            assert im.size==((1000,1000) if folder=='banners' else (1920,1080))
        assert hashlib.sha256(p.read_bytes()).hexdigest()==info['sha256']
        assert p.read_bytes()==(ROOT/'public/assets/img/articles'/folder/p.name).read_bytes()

    dashboards=[]
    for filename in ['linux-dashboard.json','windows-dashboard.json','enterprise-noc-dashboard.json']:
        d=json.loads((SOURCE/filename).read_text(encoding='utf-8'));dashboards.append(d)
        assert d['id'] is None and d['schemaVersion']==39 and d['version']==1
        assert len(set(p['id'] for p in d['panels']))==len(d['panels'])
        assert 'zabbix-enterprise' in json.dumps(d)
        assert not re.search(r'"(?:apiToken|password|data|datapoints)"\s*:',json.dumps(d))
        variables={v['name'] for v in d['templating']['list']}
        for p in d['panels']:
            g=p['gridPos'];assert g['x']+g['w']<=24 and min(g.values())>=0
            assert p['type'] in ['timeseries','stat','gauge','bargauge','table','alexanderzobnin-zabbix-triggers-panel']
            for q in p['targets']:
                assert q['schema']==13 and q['queryType'] in ['0','2','5']
                assert q['datasource']['type']=='alexanderzobnin-zabbix-datasource'
                for v in re.findall(r'\$(\w+)',json.dumps(q)):assert v in variables,v
                if p['type']=='alexanderzobnin-zabbix-triggers-panel':
                    assert q['queryType']=='5' and q['options']['useTimeRange'] is False
        for v in d['templating']['list']:
            if v['type']=='query':assert v['query']['queryType'] in ['group','host','itemTag','item']
    freshness=next(p for p in dashboards[2]['panels'] if p['title']=='Collector last success')
    assert freshness['targets'][0]['functions'][0]['def']['name']=='scale'
    assert freshness['targets'][0]['functions'][0]['params']==[1000], 'Date units require milliseconds'
    assert all(p['options']['reduceOptions']['values'] for p in dashboards[2]['panels'] if p['type']=='bargauge')
    assert dashboards[2]['templating']['list'][1]['allValue']=='', 'NOC All must preserve environment scope'
    for dashboard in dashboards[:2]:
        interface=next(v for v in dashboard['templating']['list'] if v['name']=='interface')
        assert interface['allValue']=='/Interface .*: Bits received/', 'Interface All must not select every metric'
    for filename in ['zabbix-datasource.yaml','zabbix-app.yaml','dashboard-provider.yaml']:
        y=yaml.safe_load((SOURCE/filename).read_text(encoding='utf-8'));assert y['apiVersion']==1
    ds=yaml.safe_load((SOURCE/'zabbix-datasource.yaml').read_text())['datasources'][0]
    assert ds['uid']=='zabbix-enterprise' and ds['jsonData']['authType']=='token'
    assert ds['secureJsonData']['apiToken']=='$ZABBIX_API_TOKEN'
    assert ds['jsonData']['dbConnectionEnable'] is False
    assert 'tlsSkipVerify' not in ds['jsonData']
    template=json.loads((SOURCE/'zabbix-noc-template.json').read_text())['zabbix_export']
    assert template['version']=='7.0'
    assert len(template['templates'][0]['items'])==8
    assert all(i['type']=='TRAP' for i in template['templates'][0]['items'])
    for path in SOURCE.glob('*.py'):ast.parse(path.read_text(encoding='utf-8'),str(path))

    bash=shutil.which('bash')
    if os.name=='nt':bash='C:/Program Files/Git/bin/bash.exe'
    assert bash and Path(bash).exists(),'Bash validator unavailable'
    count=0
    for lang,block in blocks['en']:
        if lang=='bash':
            result=subprocess.run([bash,'-n'],input=block,text=True,capture_output=True)
            assert result.returncode==0,result.stderr;count+=1
    result=subprocess.run([bash,'-n',str(SOURCE/'grafana-backup.sh')],capture_output=True,text=True)
    assert result.returncode==0,result.stderr
    for name in metadata['downloads']:
        assert (SOURCE/name).read_bytes()==(ROOT/'public/downloads'/SLUG/name).read_bytes()
    with zipfile.ZipFile(SOURCE/'grafana-configuration-package.zip') as z:
        assert z.testzip() is None
        for name in metadata['downloads']:assert z.read(name)==(SOURCE/name).read_bytes()
    print(json.dumps({'article_sections':len(metadata['sections']),'numbered_chapters':15,
                      'identical_bilingual_code_blocks':len(blocks['en']),'bash_blocks_syntax_validated':count,
                      'images':7,'dashboard_panels':[len(d['panels']) for d in dashboards],
                      'provisioning_yaml_files':3,'individual_downloads':len(metadata['downloads']),
                      'result':'PASS','live_zabbix_api_tested':False},indent=2))

if __name__=='__main__':main()
