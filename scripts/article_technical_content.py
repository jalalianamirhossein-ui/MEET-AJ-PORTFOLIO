"""Compile reviewed bilingual examples into existing sections, without touching metadata.

This is a source writer, never a runtime fallback. Section ordering remains owned by
article_structure. Inputs are reviewed prose and shared executable text, not prompts.
"""
from pathlib import Path
from html import escape, unescape
import json
import re
import hashlib
from article_structure import Nodes, ancestors, normalize_html, write_text

ROOT = Path(__file__).resolve().parents[1]
DATA = ROOT / 'resources/content/article-technical-content.json'


def inputs():
    return json.loads(DATA.read_text(encoding='utf-8'))


def technical_issues(source, slug):
    """Check reviewed complete procedure artifacts, never repair a failed check.

    Contracts describe real authored examples in their procedure context; a
    command word in prose or another chapter cannot satisfy a missing example.
    The PHP counterpart also applies these same contracts to final Blade HTML.
    """
    contracts = json.loads((ROOT / 'resources/content/article-technical-contracts.json').read_text(encoding='utf-8'))['articles']
    if slug not in contracts:
        return {'unreviewed_article': [slug]}
    nodes = Nodes(source).nodes
    issues = {}
    for procedure in contracts[slug]['procedures']:
        targets = [n for n in nodes if n['attrs'].get('id') == procedure['section']]
        if len(targets) != 1:
            issues.setdefault('missing_or_duplicate_procedure', []).append(procedure['section'])
            continue
        target = targets[0]
        if target['tag'] != 'section':
            target = next((n for n in ancestors(target) if n['tag'] == 'section'), target)
        hashes = []
        for n in nodes:
            if n['tag'] == 'pre' and n['end'] and target['start'] < n['start'] < target['end']:
                text = unescape(re.sub('<[^>]*>', '', source[n['open_end']:n['end']-len('</pre>')]))
                hashes.append(hashlib.sha256(text.replace('\r\n', '\n').replace('\r', '\n').encode()).hexdigest())
        for artifact in procedure['artifacts']:
            if artifact['sha256'] not in hashes:
                issues.setdefault('missing_or_changed_artifact', []).append(procedure['section'] + ': ' + artifact['description'])
    for block in contracts[slug]['bilingual_blocks']:
        targets = [n for n in nodes if n['attrs'].get('id') == block['id']]
        if len(targets) != 1:
            issues.setdefault('missing_technical_explanation', []).append(block['id'])
            continue
        target = targets[0]
        for n in nodes:
            if not (target['start'] < n['start'] < target['end']) or n['tag'] not in ('p', 'h3', 'h4', 'a'):
                continue
            if any(p['tag'] == 'pre' for p in ancestors(n)):
                continue
            if n['tag'] == 'p' and any(c['tag'] == 'a' and c['parent'] is n for c in nodes):
                continue
            if not all(n['attrs'].get('data-' + locale, '').strip() for locale in ('en', 'fa')):
                issues.setdefault('incomplete_technical_translation', []).append(block['id'] + ': ' + n['tag'])
    if slug == 'mikrotik-firewall-hardening-input-forward-chain':
        for n in nodes:
            if n['tag'] == 'pre' and n['end']:
                text = unescape(re.sub('<[^>]*>', '', source[n['open_end']:n['end']]))
                for line in text.splitlines():
                    if line.lstrip().startswith('#'):
                        continue
                    if 'action=drop' in line and 'limit=' in line:
                        issues.setdefault('rate_limited_deny', []).append(line)
    return issues


def render(block):
    tag = block['type']
    if tag == 'code':
        return '<pre dir="ltr"><code class="language-' + escape(block['language'], quote=True) + '">' + escape(block['text']) + '</code></pre>'
    if tag == 'reference':
        return '<p><a href="' + escape(block['url'], quote=True) + '" rel="noopener" data-en="' + escape(block['en'], quote=True) + '" data-fa="' + escape(block['fa'], quote=True) + '">' + escape(block['en']) + '</a></p>'
    if tag not in ('p', 'h3', 'h4') or not block['en'].strip() or not block['fa'].strip():
        raise ValueError('Invalid or incomplete bilingual technical block')
    attrs = ' id="' + escape(block['id'], quote=True) + '"' if block.get('id') else ''
    return '<' + tag + attrs + ' data-en="' + escape(block['en'], quote=True) + '" data-fa="' + escape(block['fa'], quote=True) + '">' + escape(block['en']) + '</' + tag + '>'


def enrich(source, slug):
    article = inputs()['articles'].get(slug)
    if not article:
        return source
    def repair_code(match):
        inner = match[2]
        text = unescape(re.sub('<[^>]*>', '', inner))
        changed = text
        for old, new in article.get('code_replacements', []):
            changed = changed.replace(old, new)
        if changed == text:
            return match[0]
        code = re.search(r'<code\b([^>]*)>', inner)
        return match[1] + '<code' + (code[1] if code else '') + '>' + escape(changed, quote=False) + '</code></pre>'
    # Parse inert pre content so quoted command strings match regardless of how
    # the author escaped quotes or highlighted tokens. Never search metadata.
    source = re.sub(r'(<pre\b[^>]*>)(.*?)</pre>', repair_code, source, flags=re.S)
    for section_id, blocks in article['sections'].items():
        marker = 'technical-' + section_id
        nodes = Nodes(source).nodes
        owned = [n for n in nodes if n['tag'] == 'div' and n['attrs'].get('id') == marker]
        if len(owned) > 1:
            raise ValueError(slug + ': duplicate technical content marker')
        if owned:
            n = owned[0]
            source = source[:n['start']] + source[n['end']:]
        sections = [n for n in Nodes(source).nodes if n['tag'] == 'section' and n['attrs'].get('id') == section_id]
        if len(sections) != 1:
            raise ValueError(slug + ': missing/duplicate target section ' + section_id)
        section = sections[0]
        position = source.rfind('</section', section['open_end'], section['end'])
        if section_id in article.get('replace_sections', []):
            headings = [n for n in Nodes(source).nodes if n['tag'] == 'h2' and n['parent'] is not None and n['parent']['start'] == section['start']]
            if len(headings) != 1:
                raise ValueError('Replacement needs one direct main heading')
            start = headings[0]['end']
            previous = source[start:position]
            if any(n['tag'] == 'img' or n['attrs'].get('id') for n in Nodes(previous).nodes):
                raise ValueError('Replacement would lose an image or anchor')
            source = source[:start] + source[position:]
            position = start
        payload = '<div id="' + marker + '">' + ''.join(render(b) for b in blocks) + '</div>'
        source = source[:position] + payload + source[position:]
    return normalize_html(source, slug)


def build():
    changed = []
    for slug in inputs()['articles']:
        path = ROOT / 'resources/legacy/articles' / (slug + '.html')
        before = path.read_text(encoding='utf-8')
        after = enrich(before, slug)
        if before != after:
            write_text(path, after)
            changed.append(slug)
    print('Technical source examples:', len(inputs()['articles']), 'articles;', len(changed), 'changed')


if __name__ == '__main__':
    build()
