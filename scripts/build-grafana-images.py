"""Render precise original technical diagrams to optimized 1920x1080 PNGs."""
from pathlib import Path
import argparse
import hashlib
import json
import math
import shutil
from PIL import Image, ImageDraw, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parents[1]
SLUG='grafana-installation-zabbix-integration'
DEST=ROOT/'resources/assets/img/articles/content'
DEST.mkdir(parents=True,exist_ok=True)
BG='#091321';CARD='#122438';LINE='#28445f';TEXT='#edf4fc';MUTED='#9fb5cb'
ORANGE='#ff9a32';CYAN='#3ad2e4';GREEN='#57d8a1';RED='#ff6571'
FONTDIR=Path('C:/Windows/Fonts')
def font(size,bold=False):return ImageFont.truetype(str(FONTDIR/('segoeuib.ttf' if bold else 'segoeui.ttf')),size)
def canvas(title,subtitle):
    im=Image.new('RGB',(1920,1080),BG);d=ImageDraw.Draw(im)
    for x in range(0,1920,60):d.line((x,0,x,1080),fill='#0d1d2e')
    for y in range(0,1080,60):d.line((0,y,1920,y),fill='#0d1d2e')
    d.rounded_rectangle((64,48,75,160),radius=5,fill=ORANGE)
    d.text((100,48),title,font=font(49,True),fill=TEXT)
    d.text((100,117),subtitle,font=font(27),fill=MUTED)
    d.line((64,180,1856,180),fill=LINE,width=2)
    d.text((64,1027),'MEET AJ  /  ENTERPRISE OPERATIONS',font=font(23,True),fill=MUTED)
    d.text((1220,1027),'Grafana OSS  +  Zabbix 7.0 LTS',font=font(23),fill=MUTED)
    return im,d
def box(d,rect,title,lines,accent=CYAN):
    x,y,x2,y2=rect;d.rounded_rectangle(rect,radius=20,fill=CARD,outline=LINE,width=2)
    d.rounded_rectangle((x+22,y+24,x+29,y2-24),radius=3,fill=accent)
    d.text((x+48,y+26),title,font=font(31,True),fill=accent)
    for i,line in enumerate(lines):d.text((x+48,y+83+i*39),line,font=font(25),fill=TEXT)
def arrow(d,a,b,label='',color=CYAN,label_pos=None):
    d.line((a,b),fill=color,width=5);angle=math.atan2(b[1]-a[1],b[0]-a[0])
    tip=[b,(b[0]-19*math.cos(angle-.45),b[1]-19*math.sin(angle-.45)),(b[0]-19*math.cos(angle+.45),b[1]-19*math.sin(angle+.45))]
    d.polygon(tip,fill=color)
    if label:
        pos=label_pos or ((a[0]+b[0])/2+12,(a[1]+b[1])/2-38)
        d.text(pos,label,font=font(24,True),fill=color)
def note(d,y,title,text):
    d.rounded_rectangle((64,y,1856,y+98),radius=16,fill='#152b3d',outline=LINE)
    d.text((90,y+15),title,font=font(25,True),fill=ORANGE)
    d.text((90,y+53),text,font=font(24),fill=TEXT)
def save(im,name):
    path=DEST/name;im.save(path,optimize=True)
    pub=ROOT/'public/assets/img/articles/content'/name;pub.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(path,pub)
    return path

def architecture():
    im,d=canvas('Grafana + Zabbix enterprise architecture','Zabbix collects and evaluates  /  Grafana queries and visualizes')
    box(d,(70,235,510,465),'MONITORED ESTATE',['Linux servers  /  Agent 2','Windows servers  /  Agent 2','Network devices  /  SNMP'],CYAN)
    box(d,(675,235,1175,465),'ZABBIX SERVER 7.0 LTS',['Collection  +  trigger evaluation','Frontend  +  JSON-RPC API','Problems  +  notification actions'],RED)
    arrow(d,(510,350),(675,350),'TLS agents',label_pos=(525,296))
    box(d,(675,635,1175,835),'ZABBIX POSTGRESQL',['Items  /  events  /  configuration','History  +  hourly trends'],RED)
    arrow(d,(925,465),(925,635),'Read / write',label_pos=(945,581))
    box(d,(1380,235,1850,465),'GRAFANA OSS',['Signed Zabbix app plugin','Server-side API queries','Linux  /  Windows  /  NOC'],ORANGE)
    arrow(d,(1380,353),(1175,353),'HTTPS API 443',label_pos=(1190,292))
    box(d,(1380,635,1850,835),'GRAFANA METADATA',['SQLite, default dedicated host','Dashboards  /  users  /  secrets'],ORANGE)
    arrow(d,(1615,465),(1615,635),'Local metadata',label_pos=(1635,527))
    box(d,(70,635,510,835),'IT OPERATIONS',['Browser  /  NOC wallboard','Nginx HTTPS reverse proxy'],GREEN)
    d.line((510,738,600,738,600,510,1320,510,1320,400),fill=GREEN,width=5)
    arrow(d,(1320,400),(1380,400),'',GREEN)
    d.text((635,524),'HTTPS 443  ->  Nginx  ->  127.0.0.1:3000',font=font(25,True),fill=GREEN)
    note(d,890,'OPTIONAL DIRECT DATABASE READS','History / trends only, read-only role. API remains required for metadata and Problems.')
    return save(im,'grafana-zabbix-enterprise-architecture.png')

def workflow():
    im,d=canvas('Grafana installation on Ubuntu 24.04 LTS','Stable packages  /  verified trust  /  repeatable service checks')
    cards=[('01  PREPARE UBUNTU',['Update security packages','Verify DNS and time sync']),
           ('02  VERIFY APT KEY',['Official HTTPS repository','Match primary fingerprint']),
           ('03  INSTALL STABLE OSS',['stable main  /  grafana','Record installed version']),
           ('04  CONFIGURE',['127.0.0.1:3000 only','Protect unique secret_key']),
           ('05  START + ENABLE',['systemctl enable --now','grafana-server']),
           ('06  VERIFY HEALTH',['active (running)  /  enabled','journalctl  /  /api/health'])]
    for i,(title,lines) in enumerate(cards):
        col=i%3;row=i//3;x=70+col*620;y=240+row*320
        box(d,(x,y,x+530,y+220),title,lines,ORANGE if i<3 else CYAN)
        if col<2:arrow(d,(x+530,y+110),(x+620,y+110))
    d.line((1570,460,1570,510,335,510),fill=CYAN,width=5);arrow(d,(335,510),(335,560))
    note(d,890,'PRODUCTION HANDOVER','Enable HTTPS, rotate the initial admin password, install the signed app, then verify actual Zabbix data.')
    return save(im,'grafana-ubuntu-installation-workflow.png')

def integration():
    im,d=canvas('Zabbix plugin and API-token integration','Dedicated identity  /  verified API path  /  no direct database requirement')
    box(d,(75,245,620,490),'ZABBIX AUTHORIZATION',['Dedicated integration user','Read access to approved host groups','API role: required read methods','Expiring token stored in a vault'],RED)
    box(d,(75,630,620,845),'VERIFIED API ENDPOINT',['https://zabbix.example.com/','api_jsonrpc.php','Or /zabbix/api_jsonrpc.php'],CYAN)
    arrow(d,(348,490),(348,630),'Verify path',label_pos=(373,545))
    box(d,(795,245,1355,490),'GRAFANA DATA SOURCE',['Enable signed Zabbix app','Name: Zabbix  /  Auth: API token','secureJsonData.apiToken','TLS certificate verification ON'],ORANGE)
    arrow(d,(795,350),(620,350),'Read scope',label_pos=(637,298))
    arrow(d,(620,740),(795,740),'HTTPS',label_pos=(642,685))
    box(d,(795,630,1355,845),'SAVE & TEST',['Expected: Zabbix API version 7.0.x','Then query a real authorized item','Confirm recent metric points'],GREEN)
    arrow(d,(1075,490),(1075,630),'Test',label_pos=(1095,545))
    box(d,(1480,245,1845,490),'QUERY TUNING',['Trends: on','After: 7d  /  Range: 4d','Cache TTL: 1h','API timeout: 30s'],CYAN)
    box(d,(1480,630,1845,845),'SECURE FLOW',['Token stays server-side','No token in screenshots','No skip-TLS workaround'],GREEN)
    note(d,890,'INTEGRATION ACCEPTANCE','API version alone is insufficient: test permissions, a numeric item query, variables and current Problems.')
    return save(im,'grafana-zabbix-plugin-integration.png')

def spark(d,rect,color,seed=0):
    x,y,w,h=rect
    for step in range(4):d.line((x,y+step*h/3,x+w,y+step*h/3),fill=LINE,width=1)
    points=[(x+i*w/34,y+h*(.5+.20*math.sin(i*.61+seed)+.10*math.cos(i*1.7))) for i in range(35)]
    d.polygon([(x,y+h)]+points+[(x+w,y+h)],fill='#17384b')
    d.line(points,fill=color,width=4)
def chart(d,rect,title,unit,color=CYAN,seed=0):
    x,y,x2,y2=rect;d.rounded_rectangle(rect,radius=15,fill=CARD,outline=LINE,width=2)
    d.text((x+25,y+20),title,font=font(28,True),fill=TEXT)
    d.text((x+25,y+64),unit,font=font(23),fill=MUTED)
    spark(d,(x+28,y+115,x2-x-56,y2-y-155),color,seed)
    d.text((x+30,y2-31),'09:00                         12:00                         15:00',font=font(19),fill=MUTED)
def stat(d,rect,title,value,detail,color=GREEN):
    x,y,x2,y2=rect;d.rounded_rectangle(rect,radius=16,fill=CARD,outline=LINE,width=2)
    d.text((x+24,y+16),title,font=font(24,True),fill=MUTED)
    d.text((x+24,y+54),value,font=font(47,True),fill=color)
    d.text((x+24,y2-40),detail,font=font(22),fill=TEXT)
def dashboards():
    im,d=canvas('Linux + Windows monitoring dashboards','Illustrative example values  /  real item mappings in downloadable JSON')
    d.text((78,204),'HOST GROUP: Linux servers     HOST: linux-app-01',font=font(25,True),fill=CYAN)
    d.text((995,204),'HOST GROUP: Windows servers     HOST: windows-app-01',font=font(25,True),fill=ORANGE)
    for offset,label,color,seed in [(0,'LINUX',CYAN,0),(920,'WINDOWS',ORANGE,3)]:
        chart(d,(70+offset,265,510+offset,565),label+' CPU utilization','Percent  /  utilization',color,seed)
        chart(d,(530+offset,265,970+offset,565),'Memory utilization','Percent  /  utilization',color,seed+1)
        chart(d,(70+offset,585,510+offset,885),'Network throughput','Bits/sec  /  received and sent',color,seed+2)
        stat(d,(530+offset,585,970+offset,725),'Filesystem usage','62%','FS space: used, in %',ORANGE)
        stat(d,(530+offset,745,970+offset,885),'Agent availability','Available','Verify recent sample / Problems',GREEN)
    note(d,907,'OS-SPECIFIC PANELS','Linux: CPU load and available memory. Windows: discovered service states and configured active Event Log.')
    return save(im,'grafana-linux-windows-monitoring-dashboard.png')

def noc():
    im,d=canvas('Enterprise NOC  /  Production overview','Illustrative example values  /  scoped API collector  /  current Problems')
    values=[('MONITORED HOSTS','120','Configured environment scope',CYAN),('AVAILABLE','112','Transport availability',GREEN),
            ('UNAVAILABLE','5','No available transport',RED),('UNKNOWN','3','No known availability',ORANGE),
            ('ACTIVE PROBLEMS','12','Current permitted events',ORANGE),('DISASTER / HIGH','2 / 4','Exact severity 5 / 4',RED)]
    for i,(title,val,detail,col) in enumerate(values):
        x=68+(i%3)*610;y=225+(i//3)*188
        stat(d,(x,y,x+574,y+165),title,val,detail,col)
    chart(d,(68,630,638,895),'Top CPU / memory consumers','Latest values per host  /  Top 10',ORANGE,4)
    chart(d,(665,630,1235,895),'Disk / network utilization','Per filesystem / interface',CYAN,7)
    d.rounded_rectangle((1262,630,1857,895),radius=16,fill=CARD,outline=LINE,width=2)
    d.text((1285,650),'LATEST ZABBIX PROBLEMS',font=font(27,True),fill=TEXT)
    rows=[('Disaster','db-prod-01','Unacknowledged',RED),('High','win-app-02','Acknowledged',ORANGE),('Warning','linux-app-01','Unacknowledged',CYAN)]
    for i,(sev,host,ack,col) in enumerate(rows):
        yy=713+i*51;d.ellipse((1286,yy+6,1299,yy+19),fill=col)
        d.text((1315,yy),sev+'  /  '+host,font=font(23,True),fill=col)
        d.text((1315,yy+25),ack,font=font(19),fill=MUTED)
    note(d,912,'COUNTS AND FRESHNESS','120 = 112 available + 5 unavailable + 3 unknown. Install collector; stale/no data never means healthy.')
    return save(im,'grafana-enterprise-noc-dashboard.png')

def security():
    im,d=canvas('Grafana security, backup and disaster recovery','Trusted HTTPS ingress  /  protected secrets  /  consistent restore evidence')
    box(d,(65,235,590,475),'OPERATORS / IDENTITY',['Approved management network','Named users  +  least privilege','MFA at approved identity provider'],GREEN)
    box(d,(715,235,1240,475),'NGINX HTTPS 443',['Valid certificate chain + SAN','Renewal + reload hook','WebSocket /api/live/ proxy'],CYAN)
    box(d,(1365,235,1855,475),'GRAFANA LOOPBACK',['127.0.0.1:3000 only','Anonymous access disabled','Unique protected secret_key'],ORANGE)
    arrow(d,(590,360),(715,360),'HTTPS',label_pos=(600,304))
    arrow(d,(1240,360),(1365,360),'Local',label_pos=(1270,304))
    box(d,(65,655,590,875),'CONSISTENT BACKUP',['Brief service maintenance window','SQLite .backup / pg_dump','Configuration + plugins + TLS'],ORANGE)
    box(d,(715,655,1240,875),'ENCRYPTED OFF-SITE',['Checksums + atomic publication','Restricted mounted local storage','Separate recovery-key vault'],CYAN)
    box(d,(1365,655,1855,875),'ISOLATED RECOVERY',['Same approved versions','Original encryption material','Verify queries + measured RPO/RTO'],GREEN)
    d.line((1600,475,1600,555,325,555),fill=ORANGE,width=5);arrow(d,(325,555),(325,655),'Backup',ORANGE,label_pos=(348,585))
    arrow(d,(590,760),(715,760),'Encrypt',label_pos=(592,702))
    arrow(d,(1240,760),(1365,760),'Restore',label_pos=(1250,702))
    note(d,912,'RECOVERY VALIDATION','Block notification egress in isolation. Verify decryption, login, permissions, real metrics and backup alarms.')
    return save(im,'grafana-security-backup-architecture.png')

def main():
    parser=argparse.ArgumentParser();parser.add_argument('--banner',type=Path);args=parser.parse_args()
    banner=ROOT/'resources/assets/img/articles/banners/grafana-zabbix-enterprise-monitoring-banner.png'
    if args.banner:
        im=Image.open(args.banner).convert('RGB')
        # User requested exact dimensions and optimization; no generated content changes.
        ImageOps.pad(im,(1000,1000),method=Image.Resampling.LANCZOS,color=BG).save(banner,optimize=True)
    assert banner.exists(),'Generate the banner with image_gen before building image manifest'
    paths=[banner,architecture(),workflow(),integration(),dashboards(),noc(),security()]
    public_banner=ROOT/'public/assets/img/articles/banners'/banner.name
    public_banner.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(banner,public_banner)
    manifest=[]
    for path in paths:
        im=Image.open(path);im.verify();im=Image.open(path)
        manifest.append({'filename':path.name,'dimensions':list(im.size),'bytes':path.stat().st_size,
                         'sha256':hashlib.sha256(path.read_bytes()).hexdigest(),
                         'provenance':'Built-in image_gen; optimized and resized to requested dimensions' if path==banner else 'Original deterministic Pillow technical rendering; no live metric claims'})
    package=ROOT/'resources/content/articles'/SLUG
    (package/'images.json').write_text(json.dumps(manifest,indent=2)+'\n',encoding='utf-8')
    tiles=[]
    for path in paths:
        im=ImageOps.pad(Image.open(path).convert('RGB'),(600,338),color=BG)
        tiles.append(im)
    sheet=Image.new('RGB',(1800,1014),BG)
    for i,tile in enumerate(tiles):sheet.paste(tile,((i%3)*600,(i//3)*338))
    sheet.save(ROOT/'storage/app/grafana-contact-sheet.png')
    print(json.dumps(manifest,indent=2))

if __name__=='__main__':main()
