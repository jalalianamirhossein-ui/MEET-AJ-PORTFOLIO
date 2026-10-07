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
faq_count = source.split('## FAQ')[1].split('## SEO')[0].count('### ')
refs = len(re.findall(r'^\d+\. \[Forti', source, re.M))
assert faq_count >= 8
print(f'Words: {len(source.split())}; CLI blocks: {len(blocks)}; FAQs: {faq_count}; images: {len(images)}; references: {refs}')
(root / 'VALIDATION.md').write_text(
    '# Validation\n\n'
    'Source validation: five SVG/PNG article illustrations, balanced base CLI configuration nesting, '
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
print(f'Package created: {archive_path.name} ({archive_path.stat().st_size} bytes)')
