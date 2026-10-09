"""Audit every maintained article, in both language attributes, without a database.

Usage: python scripts/audit-article-structure.py [--fix] [--baseline JSON]
The optional baseline is the inventory captured before repairs, for integrity checks.
"""
from collections import Counter
from pathlib import Path
import argparse, html, json, re
from article_structure import ROOT, POLICY, Nodes, ancestors, section_nodes, normalize_html, normalize_markdown, clean_text, write_text, heading_number
from article_technical_content import technical_issues

def body(source):
    return re.search(r'<article\b[^>]*class=["\']article-body["\'][^>]*>(.*?)</article>',source,re.S)[1]

def labels(source):
    result=[]
    for n in section_nodes(body(source)):
        block=body(source)[n['start']:n['end']]
        h=re.search(r'<h2\b([^>]*)>(.*?)</h2>',block,re.S)
        attrs=dict(re.findall(r'(data-(?:en|fa))=["\']([^"\']*)["\']',h[1]))
        text=html.unescape(re.sub('<[^>]*>','',h[2])).strip()
        result.append({'id':n['attrs'].get('id',''),'en':attrs.get('data-en',text),'fa':attrs.get('data-fa',text)})
    return result

def audit(source,slug,policy):
    content=body(source);nodes=Nodes(content).nodes
    ids=[n['attrs']['id'] for n in Nodes(source).nodes if 'id' in n['attrs']]
    all_ids={n['attrs']['id'] for n in Nodes(source).nodes if 'id' in n['attrs']}
    issues={}
    duplicates=[id for id,count in Counter(ids).items() if count>1]
    if duplicates: issues['duplicate_ids']=duplicates
    broken=sorted({html.unescape(n['attrs']['href'][1:]) for n in Nodes(source).nodes if n['attrs'].get('href','').startswith('#') and n['attrs']['href'][1:] not in all_ids})
    if broken: issues['broken_anchors']=broken
    if normalize_html(source,slug)!=source: issues['structure_or_review_cleanup']='Source is not normalized'
    article_policy=policy['articles'].get(slug,{})
    if not article_policy:issues['missing_order_policy']=slug
    order=article_policy.get('order',policy['default_order'])
    unknown=[n['attrs'].get('id') for n in section_nodes(content) if n['attrs'].get('id') not in order]
    if unknown: issues['unclassified_sections']=unknown
    positions={n['attrs']['id']:n['start'] for n in nodes if 'id' in n['attrs']}
    canonical=article_policy.get('canonical_sections',{})
    dependencies=[]
    for earlier,later in [('introduction','conclusion'),('prerequisites','configuration'),('architecture','configuration'),('architecture','troubleshooting'),('architecture','conclusion'),('conclusion','faq'),('faq','official-references')]:
        a=canonical.get(earlier,earlier);b=canonical.get(later,later)
        if a in positions and b in positions and positions[a]>positions[b]:dependencies.append(f'{earlier} after {later}')
    if dependencies:issues['dependency_order']=dependencies
    for a,b in article_policy.get('dependencies',[]):
        if a in positions and b in positions and positions[a]>positions[b]:issues.setdefault('dependency_order',[]).append(a+' after '+b)
    sequences={'en':[],'fa':[]};alignment=[];visible=[]
    for n in nodes:
        if n['tag']!='h2' or not n['end'] or any(p['tag'] in {'pre','code','footer'} for p in ancestors(n)):continue
        text=html.unescape(re.sub('<[^>]*>','',content[n['open_end']:n['end']])).strip();numbers={}
        visible.append(re.sub(r'^[0-9۰-۹٠-٩]+[.)]\s+','',text).casefold())
        for locale in sequences:
            m=heading_number(n['attrs'].get('data-'+locale,text));numbers[locale]=int(m[1]) if m else None
            if m:sequences[locale].append(int(m[1]))
        if numbers['en']!=numbers['fa']:alignment.append(text)
    for locale,sequence in sequences.items():
        if sequence and sequence!=list(range(1,len(sequence)+1)):issues[locale+'_heading_sequence']=sequence
        if len(sequence)!=len(set(sequence)):issues[locale+'_duplicate_numbers']=sequence
        if any(a>b for a,b in zip(sequence,sequence[1:])):issues[locale+'_backward_numbers']=sequence
    if alignment:issues['bilingual_number_alignment']=alignment
    duplicates=[v for v,c in Counter(visible).items() if c>1]
    if duplicates:issues['duplicate_heading_labels']=duplicates
    target_numbers={}
    for section in section_nodes(content):
        for n in nodes:
            if n['tag']=='h2' and section['start']<n['start']<section['end']:
                target_numbers[section['attrs'].get('id')]=[int(m[1]) if (m:=heading_number(n['attrs'].get('data-'+locale,html.unescape(re.sub('<[^>]*>','',content[n['open_end']:n['end']])).strip()))) else None for locale in ['en','fa']]
                break
    toc_issues=[];source_nodes=Nodes(source).nodes
    for anchor in source_nodes:
        if anchor['tag']!='a' or not anchor['end'] or not any('article-nav-item' in p['attrs'].get('class','').split() for p in ancestors(anchor)):continue
        target=anchor['attrs'].get('href','')[1:]
        if target not in target_numbers:continue
        for n in [anchor]+[c for c in source_nodes if anchor['start']<c['start']<anchor['end'] and c['end']]:
            text=html.unescape(re.sub('<[^>]*>','',source[n['open_end']:n['end']])).strip()
            for i,locale in enumerate(['en','fa']):
                m=heading_number(n['attrs'].get('data-'+locale,text))
                if m and int(m[1])!=target_numbers[target][i]:toc_issues.append(target+': '+locale)
    if toc_issues:issues['toc_heading_number_mismatch']=sorted(set(toc_issues))
    missing=[];reviews=[];hierarchy=[];empty=[]
    previous=1
    for n in nodes:
        if any(p['tag'] in {'pre','code','script','style','footer'} for p in ancestors(n)): continue
        if n['tag'] in {'h2','h3','h4','h5','h6'}:
            level=int(n['tag'][1])
            if level>previous+1: hierarchy.append(n['attrs'].get('id',content[n['open_end']:n['end']]))
            previous=level
        if n['tag'] in {'p','span','li','h2','h3','h4','th','td','figcaption','summary'} and n['end']:
            block=content[n['open_end']:n['end']]; text=html.unescape(re.sub('<[^>]*>','',block)).strip()
            if re.search('[\u0600-\u06ff]',text) and not n['attrs'].get('data-en') and not any(p['attrs'].get('data-en') for p in ancestors(n)) and not re.search(r'\bdata-en=',block):
                missing.append(re.sub(r'\s+',' ',text)[:100])
            if ('data-en' in n['attrs']) != ('data-fa' in n['attrs']): missing.append(n['attrs'].get('data-en',n['attrs'].get('data-fa'))[:100])
            for locale in ['en','fa']:
                value=n['attrs'].get('data-'+locale,text)
                if clean_text(value,policy['review_patterns'])!=value: reviews.append(value[:100])
        if n['tag']=='section' and n['end']:
            inner=content[n['open_end']:n['end']]
            remainder=re.sub(r'<h2\b[^>]*>.*?</h2>','',inner,flags=re.S)
            if not html.unescape(re.sub('<[^>]*>','',remainder)).strip() and not re.search(r'<(?:img|pre|table)\b',remainder): empty.append(n['attrs'].get('id'))
    for key,value in [('missing_language_variants',missing),('editorial_review_dates',reviews),('heading_hierarchy',hierarchy),('empty_sections',empty)]:
        if value: issues[key]=list(dict.fromkeys(value))
    return issues

def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--fix',action='store_true')
    parser.add_argument('--baseline')
    args=parser.parse_args()
    policy=json.loads(POLICY.read_text(encoding='utf-8'))
    baseline={r['slug']:r for r in json.loads(Path(args.baseline).read_text(encoding='utf-8'))} if args.baseline else {}
    rows=[]
    for path in sorted((ROOT/'resources/legacy/articles').glob('*.html')):
        source=path.read_text(encoding='utf-8')
        if args.fix:
            new=normalize_html(source,path.stem)
            if new!=source: write_text(path,new)
            source=new
        issues=audit(source,path.stem,policy)
        technical=technical_issues(source,path.stem)
        if technical: issues['technical_content']=technical
        before=baseline.get(path.stem,{}).get('source')
        if before:
            for name,pattern in [('code',r'<pre\b[^>]*>.*?</pre>'),('images',r'<img\b[^>]*>'),('schemas',r'<script\b[^>]*type="application/ld\+json"[^>]*>.*?</script>')]:
                if Counter(re.findall(pattern,before,re.S))!=Counter(re.findall(pattern,source,re.S)): issues[name+'_drift']=True
        rows.append({'slug':path.stem,'headings':labels(source),'issues':issues})
    if args.fix:
        for path in (ROOT/'resources/content/articles').glob('*/article.*.md'):
            old=path.read_text(encoding='utf-8');new=normalize_markdown(old,path.parent.name)
            if new!=old: write_text(path,new)
    report=ROOT/'storage/app/article-structure-validation.json'
    report.write_text(json.dumps(rows,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    for row in rows:
        if row['issues']: print(row['slug']+': '+json.dumps({k:len(v) if isinstance(v,list) else v for k,v in row['issues'].items()}))
    print(f"Audited {len(rows)} bilingual article sources; {sum(bool(r['issues']) for r in rows)} with issues. Report: {report}")
    return int(any(r['issues'] for r in rows))

if __name__=='__main__': raise SystemExit(main())
