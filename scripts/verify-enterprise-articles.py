"""Validate documentation structure and embedded Python without running runbook commands."""
from pathlib import Path
from html.parser import HTMLParser
import ast
import hashlib
import json
import re
import zipfile

ROOT = Path(__file__).resolve().parents[1]
DOC = ROOT / 'docs/enterprise-articles'

class Inspect(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.ids, self.codes, self.text = [], [], []
        self.meta = {}
        self.in_pre = False
        self.current = ''

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if 'id' in attrs:
            self.ids.append(attrs['id'])
        if tag == 'meta':
            self.meta[attrs.get('name', attrs.get('property'))] = attrs.get('content')
        if tag == 'pre':
            self.in_pre, self.current = True, ''

    def handle_endtag(self, tag):
        if tag == 'pre':
            self.in_pre = False
            self.codes.append(self.current)

    def handle_data(self, data):
        self.text.append(data)
        if self.in_pre:
            self.current += data

errors, checks = [], []
manifest = {r['file']: r for r in json.loads((DOC/'report.json').read_text(encoding='utf-8'))}
if (DOC/'bilingual-report.json').exists():
    for row in json.loads((DOC/'bilingual-report.json').read_text(encoding='utf-8')):
        manifest[row['file']]['updated_sha256'] = row['updated_sha256']
files = sorted((ROOT/'resources/legacy/articles').glob('*.html'))
assert len(files) == 25
with zipfile.ZipFile(DOC/'originals.zip') as archive:
    for file in files:
        raw = file.read_bytes()
        source = raw.decode('utf-8')
        parsed = Inspect()
        parsed.feed(source)
        for section in ['intro','scenario','prerequisites','architecture','installation','security','monitoring','troubleshooting','recovery','practices','compatibility','sources']:
            if parsed.ids.count('enterprise-'+section) != 1:
                errors.append(file.name+': section '+section)
        if parsed.meta.get('article:content-language') != 'fa':
            errors.append(file.name+': content language')
        for key in ['description','keywords']:
            if not parsed.meta.get(key):
                errors.append(file.name+': metadata '+key)
        for anchor in re.findall(r'<li class="article-nav-item"><a href="#([^"]+)"', source):
            if parsed.ids.count(anchor) != 1:
                errors.append(file.name+': anchor '+anchor)
        schemas = [json.loads(s) for s in re.findall(r'<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>',source,re.S)]
        faq = next((s for s in schemas if s.get('@type') == 'FAQPage'), None)
        if faq is None:
            errors.append(file.name+': missing FAQPage')
        else:
            text = ''.join(parsed.text)
            for item in faq['mainEntity']:
                if item['name'] not in text or item['acceptedAnswer']['text'] not in text:
                    errors.append(file.name+': FAQ text does not match schema')
        for code in parsed.codes:
            for python in re.findall(r"<<'PY'\n(.*?)\nPY",code,re.S):
                try:
                    ast.parse(python)
                except SyntaxError as error:
                    errors.append(file.name+': Python syntax '+str(error))
        row = manifest[file.name]
        if hashlib.sha256(raw).hexdigest() != row['updated_sha256']:
            errors.append(file.name+': final hash')
        if hashlib.sha256(archive.read(file.name)).hexdigest() != row['original_sha256']:
            errors.append(file.name+': original archive hash')
        checks.append({'file':file.name,'active_code_blocks':len(parsed.codes),'sections':12})
result = {'articles':len(checks),'checks':checks,'errors':errors,
          'validation_scope':'HTML, metadata, FAQ schema, TOC, archive hashes and embedded Python syntax. Infrastructure commands were not executed.'}
(DOC/'validation.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({'articles':len(checks),'errors':errors},ensure_ascii=False))
raise SystemExit(bool(errors))
