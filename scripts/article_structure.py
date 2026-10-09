"""Shared source audit/repair helpers. Move exact section slices, never reserialize HTML."""
from pathlib import Path
from html.parser import HTMLParser
import html, json, re
import time

ROOT = Path(__file__).resolve().parents[1]
POLICY = ROOT / 'resources/content/article-structure.json'
TRANSLATIONS = ROOT / 'resources/content/article-translations.json'

def write_text(path,content):
    for attempt in range(5):
        try:
            path.write_text(content,encoding='utf-8',newline='\n')
            return
        except OSError as error:
            if error.errno not in (13,22) or attempt==4: raise
            time.sleep(.25*(attempt+1))

class Nodes(HTMLParser):
    def __init__(self, source):
        super().__init__(convert_charrefs=False)
        self.source, self.nodes, self.stack = source, [], []
        self.lines = [0] + [m.end() for m in re.finditer('\n', source)]
        self.feed(source)
    def position(self):
        line, col = self.getpos()
        return self.lines[line-1] + col
    def handle_starttag(self, tag, attrs):
        n = dict(tag=tag, attrs=dict(attrs), start=self.position(), open_end=self.position()+len(self.get_starttag_text()), end=None, parent=self.stack[-1] if self.stack else None)
        self.nodes.append(n)
        if tag not in {'area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'}:
            self.stack.append(n)
        else: n['end'] = n['open_end']
    def handle_endtag(self, tag):
        for i in range(len(self.stack)-1,-1,-1):
            if self.stack[i]['tag']==tag:
                self.stack[i]['end'] = self.source.find('>',self.position())+1
                del self.stack[i:]
                break

def ancestors(n):
    while n.get('parent'):
        n=n['parent']; yield n

def section_nodes(source):
    nodes=Nodes(source).nodes
    return [n for n in nodes if n['tag']=='section' and n['end'] and not any(p['tag'] in {'section','footer','pre'} for p in ancestors(n)) and re.search(r'<h2\b',source[n['open_end']:n['end']])]

def clean_text(text, patterns):
    for pattern, replacement in patterns:
        text=re.sub(pattern,replacement,text,flags=re.I)
    return text

def clean_reviews(source, patterns):
    # Protect commands, JSON-LD, metadata and machine-readable dates.
    nodes=Nodes(source).nodes
    edits=[]
    count=0
    for n in nodes:
        if n['tag'] not in {'p','span','li','div'} or not n['end'] or any(p['tag'] in {'pre','code','script','style'} for p in ancestors(n)): continue
        # Edit leaf prose containers only; retain nested links and formatting.
        if any(c['parent'] is n and c['tag'] in {'p','div','li','span','pre'} for c in nodes): continue
        block=source[n['start']:n['end']]
        attrs=n['attrs']
        new=block
        matched=[]
        for lang in ['en','fa']:
            value=attrs.get('data-'+lang)
            if value is not None:
                cleaned=clean_text(value,patterns)
                if cleaned!=value:
                    matched.append(lang)
                    new=re.sub(r'(\bdata-'+lang+r'=)(["\'])(.*?)\2',lambda m:m[1]+m[2]+html.escape(cleaned,quote=True)+m[2],new,flags=re.S)
        # Only operate on text outside tags (URLs and non-language attributes stay untouched).
        new=''.join(part if part.startswith('<') else clean_text(part,patterns) for part in re.split(r'(<[^>]*>)',new))
        if new!=block:
            count+=len(matched) or 1
            if not html.unescape(re.sub('<[^>]*>','',new)).strip() and not any(attrs.get('data-'+lang,'').strip() and clean_text(attrs['data-'+lang],patterns).strip() for lang in ['en','fa']):
                new=''
            edits.append((n['start'],n['end'],new))
    for start,end,new in reversed(edits): source=source[:start]+new+source[end:]
    return source,count

def reorder(source, slug, policy):
    sections=section_nodes(source)
    order=policy['articles'].get(slug,{}).get('order',policy['default_order'])
    def rank(n):
        block=source[n['start']:n['end']]
        id=n['attrs'].get('id','')
        alias=re.match(r'<section\b[^>]*>\s*<span\b[^>]*\bid=["\']([^"\']+)["\'][^>]*class=["\']article-section-anchor["\']',block)
        ids=[alias[1] if alias and id in policy['default_order'] else id]
        return min([order.index(id) for id in ids if id in order] or [len(order)//2])
    ordered=sorted(sections,key=rank)
    output=source
    for slot,section in reversed(list(zip(sections,ordered))):
        output=output[:slot['start']]+source[section['start']:section['end']]+output[slot['end']:]
    return renumber(output)

def heading_number(text):
    """Only an integer H2 prefix followed by punctuation AND whitespace is a number.

    Versions, addresses, nested headings, lists and executable content are not H2s.
    """
    return re.match(r'^([0-9۰-۹٠-٩]+)([.)]\s+)', text.strip())

def display_number(value, original):
    digits='۰۱۲۳۴۵۶۷۸۹' if any(c in '۰۱۲۳۴۵۶۷۸۹' for c in original) else ('٠١٢٣٤٥٦٧٨٩' if any(c in '٠١٢٣٤٥٦٧٨٩' for c in original) else '0123456789')
    return str(value).translate(str.maketrans('0123456789',digits))

def section_references(text, mapping):
    def change(m):
        a,b=m[2],m[4]
        na=mapping.get(int(a),int(a));nb=mapping.get(int(b),int(b)) if b else None
        if b:
            values=sorted([na,nb]) if m[3].strip() in {'و','and'} else sorted(mapping.get(n,n) for n in range(int(a),int(b)+1))
            if values and values==list(range(values[0],values[-1]+1)):na,nb=values[0],values[-1]
            elif values:return m[1]+', '.join(display_number(n,a) for n in values)
        return m[1]+display_number(na,a)+(m[3]+display_number(nb,b) if b else '')
    return re.sub(r'((?:\bsections?\b|\bchapters?\b|بخش(?:‌های|های)?|فصل(?:‌های|های)?)\s+)([0-9۰-۹٠-٩]+)(?:([–−-]|\s+(?:تا|و|to|through|and)\s+)([0-9۰-۹٠-٩]+))?',change,text,flags=re.I)

def renumber(source):
    nodes=Nodes(source).nodes;edits=[];mapping={};headings=[]
    for n in nodes:
        if n['tag']!='h2' or not n['end'] or any(p['tag'] in {'pre','code','script','footer'} for p in ancestors(n)):continue
        visible=html.unescape(re.sub('<[^>]*>','',source[n['open_end']:n['end']])).strip()
        value=n['attrs'].get('data-en',visible);match=heading_number(value)
        if not match:continue
        headings.append(n);mapping[int(match[1])]=len(headings)
    for i,n in enumerate(headings,1):
        block=source[n['start']:n['end']]
        def prefix(text):
            m=heading_number(text)
            return display_number(i,m[1])+text[len(m[1]):] if m else text
        block=re.sub(r'(\bdata-(?:en|fa)=)(["\'])(.*?)\2',lambda m:m[1]+m[2]+html.escape(prefix(html.unescape(m[3])),quote=True)+m[2] if prefix(html.unescape(m[3]))!=html.unescape(m[3]) else m[0],block,flags=re.S)
        block=''.join(p if p.startswith('<') else re.sub(r'^(\s*)([0-9۰-۹٠-٩]+)([.)]\s+)',lambda m:m[1]+display_number(i,m[2])+m[3],p) for p in re.split(r'(<[^>]*>)',block))
        if block!=source[n['start']:n['end']]:edits.append((n['start'],n['end'],block))
    for n in nodes:
        if n['tag'] not in {'p','li','span','a','h3','h4','td','th'} or not n['end'] or any(p['tag'] in {'pre','code','script','style','h2'} for p in ancestors(n)):continue
        # Leaf containers: do not apply a number mapping twice through nested prose.
        if any(c['parent'] is n and c['tag'] in {'p','li','span','a','h3','h4','td','th','pre'} for c in nodes):continue
        block=source[n['start']:n['end']]
        block=re.sub(r'(\bdata-(?:en|fa)=)(["\'])(.*?)\2',lambda m:m[1]+m[2]+html.escape(section_references(html.unescape(m[3]),mapping),quote=True)+m[2] if section_references(html.unescape(m[3]),mapping)!=html.unescape(m[3]) else m[0],block,flags=re.S)
        block=''.join(p if p.startswith('<') else section_references(p,mapping) for p in re.split(r'(<code\b[^>]*>.*?</code>|<[^>]*>)',block,flags=re.S))
        if block!=source[n['start']:n['end']]:edits.append((n['start'],n['end'],block))
    for start,end,new in sorted(edits,reverse=True):source=source[:start]+new+source[end:]
    return source

def normalize_html(source,slug):
    policy=json.loads(POLICY.read_text(encoding='utf-8'))
    source,_=clean_reviews(source,policy['review_patterns'])
    match=re.search(r'(<article\b[^>]*class=["\']article-body["\'][^>]*>)(.*?)(</article>)',source,re.S)
    if not match: return reorder(translate(wrap_flat(source),slug),slug,policy)
    body=reorder(translate(wrap_flat(match[2]),slug),slug,policy)
    source=source[:match.start(2)]+body+source[match.end(2):]
    # Keep authored TOC items and attributes, sorting their complete li blocks.
    ids=[n['attrs'].get('id') for n in section_nodes(body)]
    toc=list(re.finditer(r'<li\b[^>]*class="article-nav-item"[^>]*>.*?</li>',source,re.S))
    if toc:
        def toc_rank(item):
            target=re.search(r'href=["\']#([^"\']+)',item[0])
            return ids.index(target[1]) if target and target[1] in ids else len(ids)
        ordered=sorted(toc,key=toc_rank)
        for slot,item in reversed(list(zip(toc,ordered))):source=source[:slot.start()]+item[0]+source[slot.end():]
        targets={}
        for n in Nodes(body).nodes:
            if n['tag']=='h2' and n['end']:
                m=heading_number(n['attrs'].get('data-en',html.unescape(re.sub('<[^>]*>','',body[n['open_end']:n['end']])).strip()))
                if m and n['parent']:targets[n['parent']['attrs'].get('id')]=int(m[1])
        def toc_number(m):
            target=re.search(r'href=["\']#([^"\']+)',m[0]);number=targets.get(target[1]) if target else None
            if number is None:return m[0]
            def prefix(value):return re.sub(r'^(\s*)([0-9۰-۹٠-٩]+)([.)]\s+)',lambda n:n[1]+display_number(number,n[2])+n[3],value)
            block=re.sub(r'(\bdata-(?:en|fa)=)(["\'])(.*?)\2',lambda n:n[1]+n[2]+html.escape(prefix(html.unescape(n[3])),quote=True)+n[2] if prefix(html.unescape(n[3]))!=html.unescape(n[3]) else n[0],m[0],flags=re.S)
            return ''.join(p if p.startswith('<') else prefix(p) for p in re.split(r'(<[^>]*>)',block))
        source=re.sub(r'<li\b[^>]*class="article-nav-item"[^>]*>.*?</li>',toc_number,source,flags=re.S)
    metadata=ROOT/'resources/content/article-localizations.json'
    if metadata.exists():
        localizations=json.loads(metadata.read_text(encoding='utf-8')).get(slug)
        if localizations:
            script='<script id="article-localizations" type="application/json">'+json.dumps(localizations,ensure_ascii=False).replace('<','\\u003c')+'</script>'
            pattern=r'<script\b[^>]*id=["\']article-localizations["\'][^>]*>.*?</script>'
            existing=re.search(pattern,source,re.S)
            if existing:
                stored=re.sub(r'^<script[^>]*>|</script>$','',existing[0])
                if json.loads(stored)!=localizations:source=re.sub(pattern,lambda _:script,source,count=1,flags=re.S)
            else:source=source.replace('</head>',script+'\n</head>',1)
    return source

def translate(source,slug):
    translations=json.loads(TRANSLATIONS.read_text(encoding='utf-8')).get(slug,{}) if TRANSLATIONS.exists() else {}
    if not translations:return source
    edits=[]
    nodes=Nodes(source).nodes
    for n in nodes:
        if n['tag'] not in {'p','li','h2','h3','h4','th','td','figcaption','summary'} or not n['end'] or 'data-en' in n['attrs']:continue
        if any(p['tag'] in {'pre','code','footer'} for p in ancestors(n)):continue
        value=html.unescape(re.sub('<[^>]*>','',source[n['open_end']:n['end']])).strip()
        value=re.sub(r'\s+',' ',value)
        if n['tag']=='h2' and value not in translations:
            key=re.sub(r'^[0-9۰-۹٠-٩]+[.)]\s+','',value)
            pair=next((v for k,v in translations.items() if re.sub(r'^[0-9۰-۹٠-٩]+[.)]\s+','',k)==key),None)
            if pair:translations[value]=pair
        if value in translations:
            pair=translations[value]
            en=pair['en'] if isinstance(pair,dict) else pair
            fa=pair['fa'] if isinstance(pair,dict) else value
            opening=source[n['start']:n['open_end']]
            opening=opening[:-1]+' data-en="'+html.escape(en,quote=True)+'" data-fa="'+html.escape(fa,quote=True)+'">'
            edits.append((n['start'],n['open_end'],opening))
    for start,end,new in reversed(edits):source=source[:start]+new+source[end:]
    return source

def wrap_flat(source):
    source='<div>'+source+'</div>'
    nodes=Nodes(source).nodes
    edits=[]
    for n in nodes:
        if n['tag']!='h2' or not n['end'] or any(p['tag'] in {'section','footer','pre'} for p in ancestors(n)): continue
        parent=n['parent']
        if not parent: continue
        following=[p for p in nodes if p['parent'] is parent and p['start']>n['start'] and p['tag'] in {'h2','section','footer'}]
        end=following[0]['start'] if following else source.rfind('</'+parent['tag']+'>',parent['start'],parent['end'])
        if end<0: raise ValueError('Unclosed flat heading parent')
        block=source[n['start']:end]
        id=n['attrs'].get('id','')
        if id: block=re.sub(r'^(<h2\b[^>]*?)\s+id=["\'][^"\']+["\']',r'\1',block,count=1)
        edits.append((n['start'],end,'<section'+(' id="'+id+'"' if id else '')+' class="article-section">'+block+'</section>'))
    for start,end,new in reversed(edits): source=source[:start]+new+source[end:]
    return source[5:-6]

def normalize_markdown(source,slug):
    policy=json.loads(POLICY.read_text(encoding='utf-8'))
    # Fence content remains exact. Section title order is provided by the bilingual source.
    source=''.join(p if p.startswith('```') else clean_text(p,policy['review_patterns']) for p in re.split(r'(```[^\n]*\n.*?```)',source,flags=re.S))
    slug={'truenas-zfs-enterprise-nas':'truenas-zfs-enterprise'}.get(slug,slug)
    article=(ROOT/'resources/legacy/articles'/f'{slug}.html').read_text(encoding='utf-8')
    article=re.search(r'<article\b[^>]*class="article-body"[^>]*>(.*?)</article>',article,re.S)[1]
    headings=[]
    for n in section_nodes(article):
        m=re.search(r'<h2\b([^>]*)>(.*?)</h2>',article[n['start']:n['end']],re.S)
        a=dict(re.findall(r'(data-(?:en|fa))="([^"]*)"',m[1]))
        headings.append({re.sub(r'^[0-9۰-۹٠-٩]+[.)]\s+','',html.unescape(v).strip()):html.unescape(v).strip() for v in list(a.values())+[re.sub('<[^>]*>','',m[2]).strip()]})
    # Ignore h2-looking text in fences.
    masked=re.sub(r'```[^\n]*\n.*?```',lambda m:' '*(len(m[0])),source,flags=re.S)
    matches=list(re.finditer(r'^## ([^\n]+)\n',masked,re.M))
    if not matches: return source
    blocks=[source[m.start():(matches[i+1].start() if i+1<len(matches) else len(source))] for i,m in enumerate(matches)]
    mapping={};ranked=[]
    for m,block in zip(matches,blocks):
        old=m[1].strip();key=re.sub(r'^[0-9۰-۹٠-٩]+[.)]\s+','',old)
        rank=next((i for i,titles in enumerate(headings) if key in titles),len(headings))
        new=headings[rank][key] if rank<len(headings) else old
        a,b=heading_number(old),heading_number(new)
        if a and b:mapping[int(a[1])]=int(b[1])
        block='## '+new+'\n'+block[block.index('\n')+1:]
        ranked.append((rank,block))
    result=source[:matches[0].start()]+''.join(block for _,block in sorted(ranked,key=lambda p:p[0]))
    return ''.join(p if p.startswith('```') else section_references(p,mapping) for p in re.split(r'(```[^\n]*\n.*?```)',result,flags=re.S)).rstrip()+'\n'

