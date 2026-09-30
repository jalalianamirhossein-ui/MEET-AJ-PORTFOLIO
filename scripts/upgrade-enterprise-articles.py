"""Build curated Persian runbooks into the legacy HTML shell. No infrastructure commands run."""
from pathlib import Path
import hashlib
import html
import json
import re
import zipfile
from article_comparisons import render_sections

ROOT = Path(__file__).resolve().parents[1]
DOC = ROOT / 'docs/enterprise-articles'
SRC = ROOT / 'resources/legacy/articles'

def render(text):
    blocks = re.split(r'(```[^\n]*\n.*?\n```)', text, flags=re.S)
    out = []
    for block in blocks:
        if block.startswith('```'):
            first, code = block[3:-3].split('\n', 1)
            out.append('<pre dir="ltr"><code class="language-'+html.escape(first.strip())+'">'+html.escape(code.rstrip())+'</code></pre>')
        else:
            for paragraph in re.split(r'\n\s*\n', block.strip()):
                if paragraph:
                    out.append('<p>'+html.escape(paragraph).replace('\n','<br>')+'</p>')
    return '\n'.join(out)

def build():
    data = json.loads((DOC / 'runbooks.json').read_text(encoding='utf-8'))
    files = sorted(SRC.glob('*.html'))
    assert len(data) == len(files) == 25
    archive = DOC / 'originals.zip'
    if not archive.exists():
        with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED) as z:
            for file in files:
                z.write(file, file.name)
    report = []
    with zipfile.ZipFile(archive) as z:
        for file in files:
            old = z.read(file.name).decode('utf-8')
            item = data[file.stem]
            start = re.search(r'<article\b[^>]*class="article-body"[^>]*>', old)
            depth = 1
            end = None
            for token in re.finditer(r'<article\b[^>]*>|</article>', old[start.end():], re.I):
                depth += -1 if token[0].lower() == '</article>' else 1
                if depth == 0:
                    end = start.end() + token.start()
                    break
            assert end is not None, file.name
            sections = []
            for key, title in [('intro','مقدمه'),('scenario','مثال عملی'),('prerequisites','پیش‌نیازها'),('architecture','Architecture / Design'),('installation','Installation / Configuration'),('security','Security Hardening'),('monitoring','Monitoring'),('troubleshooting','Troubleshooting'),('recovery','Backup / Recovery'),('practices','Best Practices'),('compatibility','نسخه‌های قدیمی و Compatibility')]:
                sections.append(f'<section id="enterprise-{key}" class="article-section"><h2>{title}</h2>{render(item[key])}</section>')
                if key == 'intro':
                    sections.extend(render_sections(file.stem))
            faq = item['faq']
            if file.stem != 'sql-server-automatic-backup-job':
                sections.append('<section id="faq" class="article-section"><h2>پرسش‌های متداول</h2>'+''.join('<h3>'+html.escape(q)+'</h3><p>'+html.escape(a)+'</p>' for q,a in faq)+'</section>')
            sections.append('<section id="enterprise-sources"><h2>منابع رسمی</h2><ul>'+''.join('<li><a href="'+html.escape(url,quote=True)+'" rel="noopener">'+html.escape(label)+'</a></li>' for label,url in item['sources'])+'</ul><p>تاریخ بررسی منابع: 2026-09-30. اعتبارسنجی عملی روی محیط هدف باید پیش از انتشار تغییر زیرساخت انجام شود.</p></section>')
            if file.stem == 'sql-server-automatic-backup-job':
                # Existing editorial package is the executable implementation.
                body = old[start.end():end]+'\n<div lang="fa" dir="rtl">'+''.join(sections)+'</div>'
            else:
                body = '<div class="enterprise-runbook" lang="fa" dir="rtl">'+''.join(sections)+'</div>'
            updated = old[:start.end()]+body+old[end:]
            if file.stem == 'sql-server-automatic-backup-job' and not re.search(r'<meta\b[^>]*name="keywords"',updated):
                updated = updated.replace('</head>','<meta name="keywords" content="'+html.escape(', '.join(item['keywords']),quote=True)+'" />\n</head>',1)
            if file.stem != 'sql-server-automatic-backup-job':
                updated = re.sub(r'<title>.*?</title>', '<title>'+html.escape(item['title'])+' | Meet AJ</title>',updated,count=1,flags=re.S)
                for name,value in [('description',item['description']),('keywords',', '.join(item['keywords'])),('article:content-language','fa')]:
                    tag = '<meta name="'+name+'" content="'+html.escape(value,quote=True)+'" />'
                    pattern = r'<meta\b[^>]*\bname="'+re.escape(name)+r'"[^>]*>'
                    updated = re.sub(pattern,lambda m:tag,updated,count=1) if re.search(pattern,updated) else updated.replace('</head>',tag+'\n</head>',1)
                for prop,val in [('og:title',item['title']),('og:description',item['description']),('og:url','https://meetaj.ir/articles/'+file.stem)]:
                    updated = re.sub(r'<meta\s+property="'+prop+r'"[^>]*>',lambda m:'<meta property="'+prop+'" content="'+html.escape(val,quote=True)+'" />',updated,count=1)
                updated = re.sub(r'(<link\s+rel="canonical"\s+href=")[^"]+',lambda m:m[1]+'https://meetaj.ir/articles/'+file.stem,updated,count=1)
                updated = re.sub(r'(<h1\b[^>]*class="article-title hero-title"[^>]*>).*?(</h1>)', lambda m:re.sub(r'data-fa="[^"]*"','data-fa="'+html.escape(item['title'],quote=True)+'"',m[1])+html.escape(item['title'])+m[2],updated,count=1,flags=re.S)
                updated = re.sub(r'(class="article-excerpt hero-subtitle"[^>]*data-fa=")[^"]*',lambda m:m[1]+html.escape(item['description'],quote=True),updated,count=1)
                # Retire old FAQ schemas; the visible FAQs and JSON-LD stay identical.
                updated = re.sub(r'<script\b[^>]*type="application/ld\+json"[^>]*>.*?</script>', '', updated, flags=re.S)
                schema = {'@context':'https://schema.org','@type':'Article','headline':item['title'],'description':item['description'],'inLanguage':'fa','url':'https://meetaj.ir/articles/'+file.stem,'dateModified':'2026-09-30','author':{'@type':'Person','name':'AmirHossein Jalalian'}}
                faq_schema = {'@context':'https://schema.org','@type':'FAQPage','mainEntity':[{'@type':'Question','name':q,'acceptedAnswer':{'@type':'Answer','text':a}} for q,a in faq]}
                updated = updated.replace('</head>', ''.join('<script type="application/ld+json">'+json.dumps(s,ensure_ascii=False).replace('<','\\u003c')+'</script>\n' for s in [schema,faq_schema])+'</head>',1)
            # Replace navigation with current section anchors, retain legacy IDs in history.
            navkeys = [(m[1], html.unescape(m[2])) for m in re.finditer(r'<section id="([^"]+)"[^>]*><h2[^>]*>([^<]+)</h2>', ''.join(sections))]
            navs = re.findall(r'<li class="article-nav-item">.*?</li>', updated, flags=re.S)
            if navs:
                newnav = ''.join('<li class="article-nav-item"><a href="#'+k+'"><span>'+label+'</span></a></li>' for k,label in navkeys)
                first = True
                def nav_replace(m):
                    nonlocal first
                    if first:
                        first=False
                        return newnav
                    return ''
                updated = re.sub(r'<li class="article-nav-item">.*?</li>',nav_replace,updated,flags=re.S)
            updated = updated.replace('\r\n','\n').replace('\r','')
            file.write_text(updated,encoding='utf-8',newline='')
            actual_title = html.unescape(re.search(r'<title>(.*?)</title>',updated,re.S)[1])
            actual_description = html.unescape(re.search(r'<meta\b[^>]*name="description"[^>]*content="([^"]*)"',updated,re.S)[1])
            report.append({'file':file.name,'priority':item['priority'],'analysis':item['analysis'],'title':item['title'],'meta_title':actual_title,'meta_description':actual_description,'suggested_url':'/articles/'+file.stem,'keywords':item['keywords'],'original_sha256':hashlib.sha256(z.read(file.name)).hexdigest(),'updated_sha256':hashlib.sha256(updated.encode()).hexdigest()})
    (DOC/'report.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
    markdown = ['# گزارش نهایی ۲۵ مقاله','تاریخ: 2026-09-30؛ مسیر فایل‌ها: resources/legacy/articles.','']
    for row in sorted(report,key=lambda r:(r['priority'],r['file'])):
        markdown.extend(['## '+row['file'],row['analysis'],'','- اولویت: '+str(row['priority']),'- عنوان: '+row['title'],'- Meta Title: '+row['meta_title'],'- Meta Description: '+row['meta_description'],'- URL پیشنهادی: '+row['suggested_url'],'- Keywords: '+', '.join(row['keywords']),''])
    (DOC/'report.md').write_text('\n'.join(markdown),encoding='utf-8')
    print('Updated',len(report),'articles; original archive retained.')

if __name__ == '__main__':
    build()
