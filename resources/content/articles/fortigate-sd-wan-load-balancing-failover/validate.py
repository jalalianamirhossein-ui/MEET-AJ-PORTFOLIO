from pathlib import Path
import re
import xml.etree.ElementTree as ET
import zipfile

root = Path(__file__).resolve().parent
source = (root / 'article.fa.md').read_text(encoding='utf-8')
images = re.findall(r'!\[[^\]]*\]\(([^)]+)\)', source)
assert len(images) == 5
for image in images:
    ET.parse(root / image)
    assert (root / image).with_suffix('.png').is_file()
blocks = re.findall(r'```fortios\n(.*?)```', source, re.S)
stack = []
for line in (root / 'base-config.fortios.conf').read_text().splitlines():
    token = line.strip()
    if token.startswith('config '):
        stack.append('config')
    elif token.startswith('edit '):
        stack.append('edit')
    elif token == 'next':
        assert stack.pop() == 'edit'
    elif token == 'end':
        assert stack.pop() == 'config'
assert not stack
assert (root / 'base-config.fortios.conf').read_text() == '\n'.join(blocks[:6])
html = (root / 'article.fa.html').read_text(encoding='utf-8')
assert html.count('<img ') == 5
assert html.count('<pre>') == len(re.findall(r'^```[^\n]*$', source, re.M)) // 2
assert 'dir="rtl"' in html
workspace = root.parents[3]
legacy_path = workspace / 'resources/legacy/articles/fortigate-sd-wan-load-balancing-failover.html'
legacy = legacy_path.read_text(encoding='utf-8')
assert '<article class="article-body"' in legacy
assert 'name="article:content-language" content="fa"' in legacy
assert len(re.findall(r'<img\b', legacy)) == 10
assert len(re.findall(r'<pre\b', legacy)) == html.count('<pre>')
assert 'src="images/' not in legacy
for asset in re.findall(r'<img[^>]*src="([^"]+)"', legacy):
    from urllib.parse import unquote
    assert (workspace / 'public' / unquote(asset.lstrip('/'))).is_file(), asset
targets = re.findall(r'<h2\b[^>]*id="([^"]+)"', legacy)
assert targets
assert len(targets) == len(set(targets))
assert all(f'href="#{target}"' in legacy for target in targets)
faq_count = source.split('## FAQ')[1].split('## SEO')[0].count('### ')
refs = len(re.findall(r'^\d+\. \[Forti', source, re.M))
assert faq_count >= 8
print(f'Words: {len(source.split())}; CLI blocks: {len(blocks)}; FAQs: {faq_count}; images: {len(images)}; references: {refs}')
(root / 'VALIDATION.md').write_text(
    '# Validation\n\n'
    'Source validation: importer-compatible legacy HTML body/hero/TOC, ten valid public image URLs '
    '(five supplied images and five generated diagrams), '
    'five SVG/PNG article illustrations, balanced base CLI configuration nesting, '
    'exact extraction of the six base CLI blocks, HTML code-block count and RTL metadata, '
    f'{faq_count} FAQs and {refs} official references.\n\n'
    'Visual QA: the five-figure contact sheet was inspected.\n\n'
    'No FortiOS execution, live network tests, CMS import or production publication was performed.\n',
    encoding='utf-8')
archive_path = root / 'fortigate-sd-wan-article-package.zip'
files = [file for file in root.rglob('*') if file.is_file() and file != archive_path and file.name != 'contact-sheet.png']
with zipfile.ZipFile(archive_path, 'w', zipfile.ZIP_DEFLATED) as archive:
    for file in files:
        archive.write(file, file.relative_to(root))
    archive.write(legacy_path, 'legacy/fortigate-sd-wan-load-balancing-failover.html')
print(f'Package created: {archive_path.name} ({archive_path.stat().st_size} bytes)')
