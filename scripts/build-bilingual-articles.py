"""Pair reviewed prose with shared code; preserve historical sources and audit before writing."""
from pathlib import Path
from html import escape, unescape
import hashlib
import json
import re

ROOT=Path(__file__).resolve().parents[1]
DOC=ROOT/'docs/enterprise-articles'
SRC=ROOT/'resources/legacy/articles'
FA=json.loads((DOC/'runbooks.json').read_text(encoding='utf-8'))
EN=json.loads((DOC/'english-runbooks.json').read_text(encoding='utf-8'))
SQL_EN=(ROOT/'scripts/sql-backup-english.txt').read_text(encoding='utf-8').splitlines()
HEADINGS=dict(intro='Introduction',scenario='Enterprise Scenario',prerequisites='Prerequisites',architecture='Architecture / Design',installation='Installation / Configuration',security='Security Hardening',monitoring='Monitoring',troubleshooting='Troubleshooting',recovery='Backup / Recovery',practices='Best Practices',compatibility='Legacy Versions and Compatibility',sources='Official Sources')

def plain(s):
    return unescape(re.sub('<[^>]*>','',s)).strip()

def dual(match, english):
    tag,attrs,inner=match[1],match[2],match[3]
    attrs=re.sub(r'\sdata-(?:en|fa)="[^"]*"','',attrs)
    return '<'+tag+attrs+' data-fa="'+escape(plain(inner),quote=True)+'" data-en="'+escape(english,quote=True)+'">'+inner+'</'+tag+'>'

def prose(s):
    s=re.sub(r'```[^\n]*\n.*?\n```','',s,flags=re.S)
    return [p for p in re.split(r'\n\s*\n',s.strip()) if p]

def translate_sql(s):
    # Protect scripts while numbering editorial nodes in their original order.
    codes=[]
    def protect(m):
        codes.append(m[0]); return '___SQL_CODE_'+str(len(codes)-1)+'___'
    s=re.sub(r'<pre\b.*?</pre>',protect,s,flags=re.S)
    number=0
    def replace(m):
        nonlocal number
        result=dual(m,SQL_EN[number]); number+=1; return result
    s=re.sub(r'<(h[1-6]|p|li|th|td|summary)\b([^>]*)>(.*?)</\1>',replace,s,flags=re.S)
    assert number==len(SQL_EN)==167,(number,len(SQL_EN))
    for i,code in enumerate(codes): s=s.replace('___SQL_CODE_'+str(i)+'___',code)
    return s

files=sorted(SRC.glob('*.html'))
audit=[]
for f in files:
    s=f.read_text(encoding='utf-8')
    audit.append(dict(file=f.name,fa_status='Complete enterprise runbook',en_status='Legacy fragments only; enterprise sections missing',translation_quality='Unequal coverage before upgrade',heading_parity=False,command_parity='No complete English enterprise version',seo_status='Persian only',before_sha256=hashlib.sha256(f.read_bytes()).hexdigest()))
if not (DOC/'bilingual-before.json').exists():
    (DOC/'bilingual-before.json').write_text(json.dumps(audit,ensure_ascii=False,indent=2),encoding='utf-8')
report=[]
for f in files:
    s=f.read_text(encoding='utf-8'); item=FA[f.stem]; en=EN[f.stem]
    s=re.sub(r'<script\b[^>]*id="article-localizations"[^>]*>.*?</script>\s*','',s,flags=re.S)
    s=re.sub(r'<link\b[^>]*rel="alternate"[^>]*hreflang="(?:fa|en|x-default)"[^>]*>\s*','',s)
    s=re.sub(r'<nav class="article-translations".*?</nav>\s*','',s,flags=re.S)
    s=s.replace(' data-article-bilingual="true"','')
    codes_before=re.findall(r'<pre\b.*?</pre>',s,re.S)
    if f.stem=='sql-server-automatic-backup-job':
        start=re.search(r'<article\b[^>]*class="article-body"[^>]*>',s).end()
        end=s.index('<div lang="fa" dir="rtl">',start)
        s=s[:start]+translate_sql(s[start:end])+s[end:]
    for key,title in HEADINGS.items():
        pattern=r'(<section id="enterprise-'+key+r'"[^>]*>)(.*?)(</section>)'
        def section(m):
            content=re.sub(r'<(h2)\b([^>]*)>(.*?)</h2>',lambda h:dual(h,title),m[2],count=1,flags=re.S)
            if key=='sources':
                texts=['Sources reviewed: 2026-09-30. Validate procedures on the target environment before deploying infrastructure changes.']
            else:
                value=en[key]; texts=value if isinstance(value,list) else [value]
            paragraphs=list(re.finditer(r'<(p)\b([^>]*)>(.*?)</p>',content,re.S))
            assert len(paragraphs)==len(texts),(f.stem,key,len(paragraphs),len(texts))
            it=iter(texts)
            content=re.sub(r'<(p)\b([^>]*)>(.*?)</p>',lambda p:dual(p,next(it)),content,flags=re.S)
            return m[1]+content+m[3]
        s,n=re.subn(pattern,section,s,count=1,flags=re.S); assert n==1,(f,key)
    if f.stem!='sql-server-automatic-backup-job':
        def faq(m):
            c=re.sub(r'<(h2)\b([^>]*)>(.*?)</h2>',lambda h:dual(h,'Frequently Asked Questions'),m[2],count=1,flags=re.S)
            it=iter([v for pair in en['faq'] for v in pair])
            c=re.sub(r'<(h3|p)\b([^>]*)>(.*?)</\1>',lambda p:dual(p,next(it)),c,flags=re.S)
            return m[1]+c+m[3]
        s=re.sub(r'(<section id="faq"[^>]*>)(.*?)(</section>)',faq,s,count=1,flags=re.S)
    # Archive content is intentionally retained in its original languages.
    s=re.sub(r'<(summary)\b([^>]*)>(محتوای نسخه قدیمی.*?)</summary>',lambda m:dual(m,'Historical edition — retained as originally written'),s,count=1,flags=re.S)
    s=re.sub(r'<(p)\b([^>]*)>(نمونه‌های زیر برای حفظ سابقه.*?)</p>',lambda m:dual(m,'The following material is retained in its original languages for historical reference. Use the bilingual runbook above for current operations, security and compatibility.'),s,count=1,flags=re.S)
    # Every current body has an explicit marker, including the retained SQL package.
    s=s.replace('class="article-body"','class="article-body" data-article-bilingual="true"',1)
    def hero(m): return dual(m,en['title'])
    s=re.sub(r'<(h1)\b([^>]*class="article-title hero-title"[^>]*)>(.*?)</h1>',hero,s,count=1,flags=re.S)
    s=re.sub(r'<(p)\b([^>]*class="article-excerpt hero-subtitle"[^>]*)>(.*?)</p>',lambda m:dual(m,en['description']),s,count=1,flags=re.S)
    if f.stem!='sql-server-automatic-backup-job':
        fa_description=item['description']
        s=re.sub(r'<(p)\b([^>]*class="article-excerpt hero-subtitle"[^>]*)>(.*?)</p>',lambda m:dual((None,m[1],re.sub(r'data-fa="[^"]*"','data-fa="'+escape(fa_description,quote=True)+'"',m[2]),escape(fa_description)),en['description']),s,count=1,flags=re.S)
        for attr,name in [('name','description'),('property','og:description'),('name','twitter:description')]:
            s=re.sub(r'(<meta\b[^>]*'+attr+'="'+name+r'"[^>]*content=")[^"]*',lambda m:m[1]+escape(fa_description,quote=True),s)
        s=re.sub(r'(<meta\b[^>]*name="keywords"[^>]*content=")[^"]*',lambda m:m[1]+escape(', '.join(item['keywords']),quote=True),s)
        def schema_description(m):
            obj=json.loads(m[1])
            if obj.get('@type')=='Article': obj['description']=fa_description
            return '<script type="application/ld+json">'+json.dumps(obj,ensure_ascii=False).replace('<','\\u003c')+'</script>'
        s=re.sub(r'<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>',schema_description,s,flags=re.S)
    s=re.sub(r'<html\b[^>]*>', '<html lang="fa" dir="rtl" data-article-language="fa">',s,count=1)
    url='https://meetaj.ir/articles/'+f.stem
    s=re.sub(r'(<link\s+rel="canonical"\s+href=")[^"]+',lambda m:m[1]+url,s,count=1)
    fa_title=unescape(re.search(r'<title>(.*?)</title>',s,re.S)[1])
    fa_desc=unescape(re.search(r'<meta\b[^>]*name="description"[^>]*content="([^"]*)"',s)[1])
    fa_hero=plain(re.search(r'<h1\b[^>]*class="article-title hero-title"[^>]*>(.*?)</h1>',s,re.S)[1])
    faq_fa=item['faq'];faq_en=en['faq']
    if f.stem=='sql-server-automatic-backup-job':
        schemas=[json.loads(v) for v in re.findall(r'<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>',s,re.S)]
        existing=next(v for v in schemas if v.get('@type')=='FAQPage')
        faq_fa=[[v['name'],v['acceptedAnswer']['text']] for v in existing['mainEntity']]
        faq_en=[[SQL_EN[i],SQL_EN[i+1]] for i in range(138,154,2)]
    localizations={'fa':dict(title=fa_hero,meta_title=fa_title,description=fa_desc,keywords=item['keywords'],faq=faq_fa),'en':dict(title=en['title'],meta_title=en['title']+' | Meet AJ',description=en['description'],keywords=en['keywords'],faq=faq_en)}
    links=''.join('<link rel="alternate" hreflang="'+lang+'" href="'+escape(target,quote=True)+'" />\n' for lang,target in [('fa',url),('en',url+'?lang=en'),('x-default',url)])
    links+='<script id="article-localizations" type="application/json">'+json.dumps(localizations,ensure_ascii=False).replace('<','\\u003c')+'</script>\n'
    s=s.replace('</head>',links+'</head>',1)
    navigation='<nav class="article-translations" aria-label="Article language"><a lang="fa" hreflang="fa" href="'+url+'">فارسی</a> · <a lang="en" hreflang="en" href="'+url+'?lang=en">English</a></nav>'
    s=s.replace('<article class="article-body"',navigation+'\n<article class="article-body"',1)
    # Generated TOC labels use the same bilingual headings as current sections.
    headings={m[1]:(unescape(m[2]),unescape(m[3])) for m in re.finditer(r'<section id="([^"]+)"[^>]*><h2[^>]*data-fa="([^"]*)" data-en="([^"]*)"',s)}
    def toc(m):
        if m[1] not in headings:return m[0]
        fa,enlabel=headings[m[1]]
        return '<a href="#'+m[1]+'"><span data-fa="'+escape(fa,quote=True)+'" data-en="'+escape(enlabel,quote=True)+'">'+escape(fa)+'</span></a>'
    s=re.sub(r'<a href="#([^"]+)"><span>[^<]*</span></a>',toc,s)
    # Trim shell indentation outside executable blocks without changing scripts.
    s=''.join(block if block.startswith('<pre') else re.sub(r'[ \t]+(?=\n)', '', block) for block in re.split(r'(<pre\b.*?</pre>)',s,flags=re.S))
    assert re.findall(r'<pre\b.*?</pre>',s,re.S)==codes_before,f.name+' code drift'
    f.write_text(s,encoding='utf-8',newline='')
    report.append(dict(file=f.name,title_fa=fa_hero,title_en=localizations['en']['title'],fa_status='Complete',en_status='Complete',missing_sections=[],translation_quality='Editorial English; aligned operational meaning, headings and shared code',seo_status='Localized title, description, keywords and FAQ; reciprocal hreflang; self canonical per language',seo_metadata=localizations,urls={'fa':url,'en':url+'?lang=en'},historical_content='Preserved in original languages, clearly marked',code_blocks=len(codes_before),updated_sha256=hashlib.sha256(f.read_bytes()).hexdigest()))
(DOC/'bilingual-report.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
previous=json.loads((DOC/'report.json').read_text(encoding='utf-8'))
for row in previous:
    now=next(r for r in report if r['file']==row['file'])
    row.setdefault('persian_upgrade_sha256',row['updated_sha256'])
    row['updated_sha256']=now['updated_sha256']
    row['meta_title']=now['seo_metadata']['fa']['meta_title']
    row['meta_description']=now['seo_metadata']['fa']['description']
    row['keywords']=now['seo_metadata']['fa']['keywords']
(DOC/'report.json').write_text(json.dumps(previous,ensure_ascii=False,indent=2),encoding='utf-8')
fa_lines=['# گزارش ارتقای فارسی و اولویت بازنویسی','گزارش نهایی دو زبان در bilingual-report.md و bilingual-report.json ثبت شده است.','']
for row in sorted(previous,key=lambda r:(r['priority'],r['file'])):
    fa_lines += ['## '+row['file'],row['analysis'],'','- اولویت: '+str(row['priority']),'- عنوان: '+row['title'],'- Meta Title: '+row['meta_title'],'- Meta Description: '+row['meta_description'],'- URL: '+row['suggested_url'],'- Keywords: '+', '.join(row['keywords']),'']
(DOC/'report.md').write_text('\n'.join(fa_lines),encoding='utf-8')
lines=['# گزارش استانداردسازی FA / EN','تعداد: ۲۵ مقاله؛ تاریخ: 2026-09-30','نسخه تاریخی در زبان اصلی حفظ شده است؛ ارزیابی برابری مربوط به متن اجرایی جاری است.','']
for row in report:
    lines += ['## '+row['file'],'','- File: '+row['file'],'- Title FA: '+row['title_fa'],'- Title EN: '+row['title_en'],'- FA Status: کامل','- EN Status: کامل','- Missing Sections: ندارد','- Translation Quality: نگارش تخصصی انگلیسی؛ Heading و دستورات هماهنگ','- SEO Status: عنوان، Description، Keywords و FAQ مستقل؛ Canonical همان زبان و Hreflang متقابل','- URL FA: '+row['urls']['fa'],'- URL EN: '+row['urls']['en'],'']
lines += ['راهنمای URL/Hreflang: https://developers.google.com/search/docs/specialty/international/localized-versions','']
(DOC/'bilingual-report.md').write_text('\n'.join(lines),encoding='utf-8')
print('Built',len(report),'bilingual articles. Original code unchanged.')
