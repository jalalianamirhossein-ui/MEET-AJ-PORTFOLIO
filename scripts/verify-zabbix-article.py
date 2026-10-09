"""Static article/package checks. Never execute installation/backup/restore."""
from pathlib import Path
from lxml import html
from PIL import Image
import hashlib, json, re, zipfile
ROOT = Path(__file__).resolve().parents[1]
SLUG = 'zabbix-server-linux-windows-agents-backup'
SOURCE = ROOT/'resources/content/articles'/SLUG
document = html.fromstring((ROOT/'resources/legacy/articles'/f'{SLUG}.html').read_text(encoding='utf-8'))
body = document.xpath('//article')[0]
sections = body.xpath('.//section')
assert len(sections) == 17
ids = document.xpath('//@id')
assert len(ids) == len(set(ids)), 'Duplicate IDs'
for anchor in document.xpath('//a[starts-with(@href,"#")]'):
    assert anchor.get('href')[1:] in ids
translations = json.loads(document.xpath('//script[@id="article-localizations"]/text()')[0])
for locale in ['en','fa']:
    assert len(translations[locale]['faq']) == 8
    assert translations[locale]['image_alt'] and translations[locale]['image_caption']
    assert (SOURCE/f'article.{locale}.md').stat().st_size > 35000
    for node in body.xpath('.//*[@data-en]'):
        assert node.get('data-fa') and node.get('data-en')
    if locale == 'en':
        assert not re.search(r'[\u0600-\u06ff]', body.text_content())
for anchor in body.xpath('.//a[starts-with(@href,"/downloads/")]'):
    name = anchor.get('href').rsplit('/',1)[1]
    public = ROOT/'public'/anchor.get('href').lstrip('/')
    assert public.read_bytes() == (SOURCE/name).read_bytes()
for anchor in body.xpath('.//a[starts-with(@href,"/articles/")]'):
    slug = anchor.get('href').rsplit('/',1)[1]
    assert (ROOT/'resources/legacy/articles'/f'{slug}.html').exists(), slug
images = json.loads((SOURCE/'images.json').read_text())
assert len(images) == 6
for record in images:
    folder = 'banners' if '-banner' in record['filename'] else 'content'
    source = ROOT/'resources/assets/img/articles'/folder/record['filename']
    with Image.open(source) as image:
        assert list(image.size) == record['dimensions']
        image.verify()
    assert hashlib.sha256(source.read_bytes()).hexdigest() == record['sha256']
    assert source.read_bytes() == (ROOT/'public/assets/img/articles'/folder/record['filename']).read_bytes()
for image in body.xpath('.//img'):
    assert image.get('data-fa-alt') and image.get('data-en-alt')
    assert (ROOT/'public'/image.get('src').lstrip('/')).exists()
with zipfile.ZipFile(SOURCE/'zabbix-configuration-package.zip') as archive:
    assert archive.testzip() is None
    assert len(archive.namelist()) == 12
    for name in archive.namelist(): assert archive.read(name) == (SOURCE/name).read_bytes()
# Export isolated syntax-only snippets; no runtime execution.
qa = ROOT/'storage/app/zabbix-qa'; qa.mkdir(exist_ok=True,parents=True)
bash = body.xpath('.//pre/code[@class="language-bash"]/text()')
(qa/'all-bash.sh').write_text('\n\n'.join(bash)+'\n',encoding='utf-8',newline='\n')
powershell = body.xpath('.//pre/code[@class="language-powershell"]/text()')
for i,block in enumerate(powershell): (qa/f'block-{i}.ps1').write_text(block+'\n',encoding='utf-8')
report={'sections':len(sections),'code_blocks':len(body.xpath('.//pre/code')),'bash_blocks':len(bash),'powershell_blocks':len(powershell),'images':len(images),'downloads':13,'article_en_bytes':(SOURCE/'article.en.md').stat().st_size,'article_fa_bytes':(SOURCE/'article.fa.md').stat().st_size}
(qa/'static-results.json').write_text(json.dumps(report,indent=2)+'\n')
print(json.dumps(report,indent=2))
