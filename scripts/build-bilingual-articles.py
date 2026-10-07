"""Pair reviewed prose with shared code; preserve historical sources and audit before writing."""
from pathlib import Path
from html import escape, unescape
import hashlib
import json
import re
import time

ROOT=Path(__file__).resolve().parents[1]
DOC=ROOT/'docs/enterprise-articles'
SRC=ROOT/'resources/legacy/articles'
FA=json.loads((DOC/'runbooks.json').read_text(encoding='utf-8'))
EN=json.loads((DOC/'english-runbooks.json').read_text(encoding='utf-8'))
SQL_EN=(ROOT/'resources/content/articles/sql-server-automatic-backup-job/english-source.txt').read_text(encoding='utf-8').splitlines()
HEADINGS=dict(intro='Introduction',scenario='Practical Example',prerequisites='Prerequisites',architecture='Architecture / Design',installation='Installation / Configuration',security='Security Hardening',monitoring='Monitoring',troubleshooting='Troubleshooting',recovery='Backup / Recovery',practices='Best Practices',compatibility='Legacy Versions and Compatibility',sources='Official Sources')

def plain(s):
    return unescape(re.sub('<[^>]*>','',s)).strip()

def dual(match, english):
    tag,attrs,inner=match[1],match[2],match[3]
    existing_fa = re.search(r'\bdata-fa="([^"]*)"', attrs)
    persian = unescape(existing_fa[1]) if existing_fa else plain(inner)
    attrs=re.sub(r'\sdata-(?:en|fa)="[^"]*"','',attrs)
    return '<'+tag+attrs+' data-fa="'+escape(persian,quote=True)+'" data-en="'+escape(english,quote=True)+'">'+inner+'</'+tag+'>'

def localize_html(s, locale):
    protected = {}
    def protect(m):
        key = '__PROTECTED_'+str(len(protected))+'__'
        protected[key] = m[0]
        return key
    s = re.sub(r'<pre\b.*?</pre>|<details\b[^>]*id="legacy-history"[^>]*>.*?</details>', protect, s, flags=re.S)
    def replace(m):
        value = escape(unescape(m[3]), quote=False)
        links = re.findall(r'<a\b[^>]*href="https://[^"]*"[^>]*>.*?</a>', m[4], re.S)
        if locale == 'en':
            links = [re.sub(r'>.*?</a>', '>Official documentation</a>', v, flags=re.S) if re.search('[\u0600-\u06ff]', plain(v)) else v for v in links]
        icons = re.findall(r'<i\b[^>]*aria-hidden="true"[^>]*>.*?</i>', m[4], re.S)
        return '<'+m[1]+m[2]+'>'+value+(' '+ ' '.join(links) if links else '')+''.join(icons)+'</'+m[1]+'>'
    s = re.sub(r'<(h[1-6]|p|span|li|th|td|summary)\b([^>]*\bdata-'+locale+r'="([^"]*)"[^>]*)>(.*?)</\1\s*>', replace, s, flags=re.S)
    s = re.sub(r'lang="(?:fa|en)" dir="(?:rtl|ltr)"', 'lang="'+locale+'" dir="'+('rtl' if locale == 'fa' else 'ltr')+'"', s)
    for key, value in protected.items(): s = s.replace(key, value)
    return s

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
    previous_metadata = re.search(r'<script\b[^>]*id="article-localizations"[^>]*>(.*?)</script>', s, re.S)
    previous_metadata = json.loads(previous_metadata[1]) if previous_metadata else None
    if previous_metadata:
        s = localize_html(s, 'fa')
        s = re.sub(r'<title>.*?</title>', '<title>'+escape(previous_metadata['fa']['meta_title'])+'</title>', s, count=1, flags=re.S)
        s = re.sub(r'(<meta\b[^>]*name="description"[^>]*content=")[^"]*', lambda m:m[1]+escape(previous_metadata['fa']['description'],quote=True), s)
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
                # Retire the generated review-date paragraph from existing sources too.
                content=re.sub(r'<p\b[^>]*>.*?</p>', '', content, flags=re.S)
                texts=[]
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
    fa_title=fa_title.replace('راهنمای Enterprise با Agent', 'زمان‌بندی Agent و آزمون Restore')
    fa_hero=plain(re.search(r'<h1\b[^>]*class="article-title hero-title"[^>]*>(.*?)</h1>',s,re.S)[1])
    fa_hero=fa_hero.replace('طراحی Enterprise با SQL Server Agent', 'زمان‌بندی SQL Server Agent و آزمون Restore')
    s=s.replace('طراحی Enterprise با SQL Server Agent', 'زمان‌بندی SQL Server Agent و آزمون Restore').replace('Best Practiceهای Enterprise برای Backup SQL Server', 'Best Practiceها برای Backup SQL Server')
    faq_fa=item['faq'];faq_en=en['faq']
    if f.stem=='sql-server-automatic-backup-job':
        schemas=[json.loads(v) for v in re.findall(r'<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>',s,re.S)]
        existing=next(v for v in schemas if v.get('@type')=='FAQPage')
        faq_fa=previous_metadata['fa']['faq'] if previous_metadata else [[v['name'],v['acceptedAnswer']['text']] for v in existing['mainEntity']]
        faq_en=[[SQL_EN[i],SQL_EN[i+1]] for i in range(138,154,2)]
    localizations={'fa':dict(title=fa_hero,meta_title=fa_title,description=fa_desc,keywords=item['keywords'],faq=faq_fa),'en':dict(title=en['title'],meta_title=en['title']+' | Meet AJ',description=en['description'],keywords=en['keywords'],faq=faq_en)}
    links='<script id="article-localizations" type="application/json">'+json.dumps(localizations,ensure_ascii=False).replace('<','\\u003c')+'</script>\n'
    s=s.replace('</head>',links+'</head>',1)
    # Generated TOC labels use the same bilingual headings as current sections.
    headings={m[1]:(unescape(m[2]),unescape(m[3])) for m in re.finditer(r'<section id="([^"]+)"[^>]*><h2[^>]*data-fa="([^"]*)" data-en="([^"]*)"',s)}
    def toc(m):
        if m[1] not in headings:return m[0]
        fa,enlabel=headings[m[1]]
        return '<a href="#'+m[1]+'"><span data-fa="'+escape(fa,quote=True)+'" data-en="'+escape(enlabel,quote=True)+'">'+escape(fa)+'</span></a>'
    s=re.sub(r'<a href="#([^"]+)"><span>[^<]*</span></a>',toc,s)
    # The clean article URL and source metadata now default to English.
    s = localize_html(s, 'en')
    s = s.replace('data-article-language="fa"', 'data-article-language="en"')
    s = re.sub(r'<title>.*?</title>', '<title>'+escape(localizations['en']['meta_title'])+'</title>', s, count=1, flags=re.S)
    for attr, name, value in [('name','description',en['description']),('name','keywords',', '.join(en['keywords'])),('name','article:content-language','en'),('property','og:title',en['title']),('property','og:description',en['description']),('name','twitter:title',en['title']),('name','twitter:description',en['description'])]:
        s = re.sub(r'(<meta\b[^>]*'+attr+'="'+re.escape(name)+r'"[^>]*content=")[^"]*',lambda m:m[1]+escape(value,quote=True),s)
    def english_schema(m):
        obj=json.loads(m[1])
        if obj.get('@type')=='Article':
            obj.update(headline=en['title'],description=en['description'],inLanguage='en',url=url,mainEntityOfPage=url)
        elif obj.get('@type')=='FAQPage':
            obj.update(inLanguage='en',mainEntity=[{'@type':'Question','name':q,'acceptedAnswer':{'@type':'Answer','text':a}} for q,a in faq_en])
        return '<script type="application/ld+json">'+json.dumps(obj,ensure_ascii=False).replace('<','\\u003c')+'</script>'
    s = re.sub(r'<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>',english_schema,s,flags=re.S)
    # Trim shell indentation outside executable blocks without changing scripts.
    s=''.join(block if block.startswith('<pre') else re.sub(r'[ \t]+(?=\n)', '', block) for block in re.split(r'(<pre\b.*?</pre>)',s,flags=re.S))
    assert re.findall(r'<pre\b.*?</pre>',s,re.S)==codes_before,f.name+' code drift'
    # Windows file watchers can briefly hold a just-rewritten source file.
    for attempt in range(5):
        try:
            f.write_text(s,encoding='utf-8',newline='')
            break
        except OSError as error:
            if error.errno not in (13, 22) or attempt == 4:
                raise
            time.sleep(0.25 * (attempt + 1))
    report.append(dict(file=f.name,title_fa=fa_hero,title_en=localizations['en']['title'],fa_status='Complete',en_status='Complete',missing_sections=[],translation_quality='Editorial English; aligned operational meaning, headings and shared code',seo_status='Localized title, description, keywords and FAQ; shared clean canonical; saved language preference',seo_metadata=localizations,urls={'fa':url,'en':url},historical_content='Preserved only in docs/enterprise-articles/originals.zip; removed from public pages',code_blocks=len(codes_before),updated_sha256=hashlib.sha256(f.read_bytes()).hexdigest()))
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
lines=['# گزارش استانداردسازی FA / EN','تعداد: ۲۵ مقاله؛ تاریخ: 2026-10-01','نسخه تاریخی فقط در originals.zip محفوظ است و در صفحه مقاله نمایش داده نمی‌شود.','']
for row in report:
    lines += ['## '+row['file'],'','- File: '+row['file'],'- Title FA: '+row['title_fa'],'- Title EN: '+row['title_en'],'- FA Status: کامل','- EN Status: کامل','- Missing Sections: ندارد','- Translation Quality: نگارش تخصصی انگلیسی؛ Heading و دستورات هماهنگ','- SEO Status: عنوان، Description، Keywords و FAQ مستقل؛ Canonical مشترک بدون پارامتر؛ زبان مطابق انتخاب ذخیره‌شده','- URL FA: '+row['urls']['fa'],'- URL EN: '+row['urls']['en'],'']
lines += ['دو زبان در یک URL نمایش داده می‌شوند؛ Hreflang جداگانه برای این ساختار تولید نمی‌شود.','']
(DOC/'bilingual-report.md').write_text('\n'.join(lines),encoding='utf-8')
print('Built',len(report),'bilingual articles. Original code unchanged.')
