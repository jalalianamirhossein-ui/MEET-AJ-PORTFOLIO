"""Run article builders in an isolated workspace copy; never replace maintained prose."""
from pathlib import Path
import argparse,shutil,subprocess,sys,json,re,html
from collections import Counter
sys.dont_write_bytecode=True
parser=argparse.ArgumentParser(description=__doc__)
parser.add_argument("--php", default=shutil.which("php"), help="PHP 8.4+ executable (GD required by image builders)")
args=parser.parse_args()
if not args.php:parser.error("Pass --php /path/to/php; PHP 8.4+ is required")
php=str(Path(args.php).resolve())
root=Path(__file__).resolve().parents[1];target=root/'storage/app/article-regeneration'
target.mkdir(parents=True,exist_ok=True)
for relative in ['scripts','resources/content','resources/legacy/articles','resources/assets/img/articles']:
    shutil.copytree(root/relative,target/relative,dirs_exist_ok=True,ignore=shutil.ignore_patterns('__pycache__'))
shutil.copytree(root/'app/Services',target/'app/Services',dirs_exist_ok=True)
sys.path.insert(0,str(target/'scripts'))
from article_structure import normalize_html
results=[];commands=[]
builders=sorted((target/'scripts').glob('build-*-article.py'))+[target/'resources/content/articles/mikrotik-ping-triggered-policy-routing/build.py']
for path in builders:
    command=[sys.executable,'-X','utf8',str(path)];commands.append(command)
    run=subprocess.run(command,cwd=target,capture_output=True,text=True,encoding='utf-8')
    results.append({'builder':str(path.relative_to(target)),'exit':run.returncode,'output':(run.stdout+run.stderr)[-1600:]})
    print(results[-1]['builder'],run.returncode)
    if run.returncode:print(results[-1]['output'])
for slug in ['10-essential-group-policies-windows-domain','cisco-catalyst-layer-2-layer-3-switch-hardening']:
    name='build-essential-gpo-article.php' if slug.startswith('10-') else 'build-cisco-hardening-article.php'
    command=[php,'-d','extension=gd',str(target/'scripts'/name)];commands.append(command)
    run=subprocess.run(command,cwd=target,capture_output=True,text=True,encoding='utf-8')
    results.append({'builder':name,'exit':run.returncode,'output':(run.stdout+run.stderr)[-1600:]})
    print(name,run.returncode)
(target/'vendor').mkdir(exist_ok=True)
(target/'vendor/autoload.php').write_text("<?php require '"+str(root/'vendor/autoload.php').replace('\\','/')+"';",encoding='utf-8')
for slug in ['linux-security-auditor-bash','truenas-zfs-enterprise','sql-server-automatic-backup-job']:
    path=target/'resources/content/articles'/slug/'build.php'
    command=[php,str(path)];commands.append(command)
    run=subprocess.run(command,cwd=target,capture_output=True,text=True,encoding='utf-8')
    results.append({'builder':slug+'/build.php','exit':run.returncode,'output':(run.stdout+run.stderr)[-1600:]})
    print(slug,run.returncode)
# Historical entry points now preserve maintained bodies and compile reviewed
# technical examples. Exercise both, including their second-run stability.
for name in ['upgrade-enterprise-articles.py', 'build-bilingual-articles.py']:
    command=[sys.executable,'-X','utf8',str(target/'scripts'/name)];commands.append(command)
    run=subprocess.run(command,cwd=target,capture_output=True,text=True,encoding='utf-8')
    results.append({'builder':name,'exit':run.returncode,'output':(run.stdout+run.stderr)[-1600:]})
    print(name,run.returncode)
# Verify that each generated source is already in normalized form.
drift=[]
for path in (target/'resources/legacy/articles').glob('*.html'):
    source=path.read_text(encoding='utf-8')
    if normalize_html(source,path.stem)!=source: drift.append(path.stem)
results.append({'normalization_drift':drift})
from article_structure import Nodes,ancestors
def headings(source):
    return [(n['parent']['attrs'].get('id'),n['attrs'].get('data-en'),n['attrs'].get('data-fa')) for n in Nodes(source).nodes if n['tag']=='h2' and n['end'] and not any(a['tag'] in {'pre','footer'} for a in ancestors(n))]
heading_drift=[];code_drift=[];image_drift=[];reference_drift=[];technical_drift=[];technical_failures=[]
from article_technical_content import technical_issues

def reviewed_prose(source):
    return [(n['attrs'].get('id'),n['tag'],n['attrs'].get('data-en'),n['attrs'].get('data-fa'),n['attrs'].get('href'))
            for n in Nodes(source).nodes
            if any(a['attrs'].get('id','').startswith('technical-') for a in ancestors(n))
            and n['tag'] in {'p','h3','h4','a'}]
def references(source):
    result=[]
    for n in Nodes(source).nodes:
        if any(a['tag'] in {'pre','code','script'} for a in ancestors(n)):continue
        for lang in ['en','fa']:
            value=n['attrs'].get('data-'+lang,'')
            if not re.search(r'(?:\bsections?\b|\bchapters?\b|بخش(?:‌های|های)?|فصل(?:‌های|های)?)\s+[0-9۰-۹٠-٩]+',value,re.I):continue
            section=next((a['attrs'].get('id') for a in ancestors(n) if a['tag']=='section'),None)
            result.append((section,n['tag'],lang,re.sub(r'\s+',' ',value).strip()))
    return Counter(result)
for path in (target/'resources/legacy/articles').glob('*.html'):
    maintained=(root/'resources/legacy/articles'/path.name).read_text(encoding='utf-8');generated=path.read_text(encoding='utf-8')
    if headings(maintained)!=headings(generated):heading_drift.append(path.stem)
    # Compare executable text, independent of presentation whitespace outside code tags.
    codes=lambda s:Counter(html.unescape(re.sub('<[^>]*>','',m)) for m in re.findall(r'<pre\b[^>]*>.*?</pre>',s,re.S))
    if codes(maintained)!=codes(generated):code_drift.append(path.stem)
    images=lambda s:Counter(n['attrs'].get('src') for n in Nodes(s).nodes if n['tag']=='img')
    if images(maintained)!=images(generated):image_drift.append(path.stem)
    if references(maintained)!=references(generated):reference_drift.append(path.stem)
    if reviewed_prose(maintained)!=reviewed_prose(generated):technical_drift.append(path.stem)
    if technical_issues(generated,path.stem):technical_failures.append(path.stem)
first={p:p.read_bytes() for p in (target/'resources/legacy/articles').glob('*.html')}
second_failures=[]
for command in commands:
    run=subprocess.run(command,cwd=target,capture_output=True,text=True,encoding='utf-8')
    if run.returncode:second_failures.append(command[-1])
unstable=[p.stem for p,data in first.items() if p.read_bytes()!=data]
results.append({'heading_drift':heading_drift,'code_drift':code_drift,'image_drift':image_drift,'section_reference_drift':reference_drift,'technical_prose_drift':technical_drift,'technical_contract_failures':technical_failures,'second_generation_failures':second_failures,'regeneration_instability':unstable})
(root/'storage/app/structure-regeneration.json').write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf-8')
print('Normalization drift',drift)
print('Heading drift',heading_drift,'Code drift',code_drift,'Image drift',image_drift,'Section reference drift',reference_drift,'Repeated generation drift',unstable)
print('Reviewed bilingual prose drift',technical_drift,'Technical contract failures',technical_failures)
raise SystemExit(int(any(r.get('exit',0) for r in results) or any([drift,heading_drift,code_drift,image_drift,reference_drift,technical_drift,technical_failures,second_failures,unstable])))
