"""Render exact English technical diagrams and export the generated banner as PNG."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont
import argparse
import hashlib
import json
import math
import shutil

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'oracle-database-26ai-installation-oracle-linux'
SOURCE = ROOT / 'resources/content/articles' / SLUG
FONT = Path('C:/Windows/Fonts/segoeui.ttf')
BOLD = Path('C:/Windows/Fonts/segoeuib.ttf')
NAVY, PANEL, EDGE = '#081426', '#12273f', '#294662'
WHITE, MUTED, CYAN, RED, GREEN = '#edf4fc', '#afc3da', '#52d8ee', '#ff6468', '#6edbaa'
manifest = []

def font(size, bold=False):
    return ImageFont.truetype(str(BOLD if bold else FONT), size)

def canvas(title, subtitle, number):
    im = Image.new('RGB', (1920, 1080), NAVY)
    d = ImageDraw.Draw(im)
    for x in range(0, 1920, 80):
        d.line((x, 0, x, 1080), fill='#0d1c2f')
    for y in range(0, 1080, 80):
        d.line((0, y, 1920, y), fill='#0d1c2f')
    d.rounded_rectangle((80, 64, 160, 126), 15, fill=RED)
    d.text((120, 94), f'{number:02}', font=font(29, True), fill=NAVY, anchor='mm')
    d.text((190, 64), title, font=font(49, True), fill=WHITE)
    d.text((190, 133), subtitle, font=font(28), fill=MUTED)
    d.line((80, 202, 1840, 202), fill=EDGE, width=2)
    d.text((80, 1025), 'ORACLE DATABASE 26ai  /  ENTERPRISE EDITION  /  ORACLE LINUX 9', font=font(21, True), fill=MUTED)
    return im, d

def box(d, rect, title, lines=(), accent=CYAN, size=31):
    x1,y1,x2,y2=rect
    d.rounded_rectangle(rect, 22, fill=PANEL, outline=EDGE, width=2)
    d.rounded_rectangle((x1, y1, x1+7, y2), 3, fill=accent)
    d.text(((x1+x2)/2, y1+44), title, font=font(size, True), fill=WHITE, anchor='mm')
    for i,line in enumerate(lines):
        d.text(((x1+x2)/2, y1+91+i*39), line, font=font(25), fill=MUTED, anchor='mm')

def arrow(d, points, color=CYAN):
    d.line(points, fill=color, width=5, joint='curve')
    x,y=points[-1]; px,py=points[-2]
    a=math.atan2(y-py,x-px)
    d.polygon([(x,y),(x-18*math.cos(a-.5),y-18*math.sin(a-.5)),(x-18*math.cos(a+.5),y-18*math.sin(a+.5))],fill=color)

def note(d, y, text):
    d.text((960,y),text,font=font(26),fill=MUTED,anchor='mm')

def save(im, filename, description):
    path=ROOT/'resources/assets/img/articles/content'/filename
    path.parent.mkdir(parents=True,exist_ok=True)
    im.save(path,format='PNG',optimize=True)
    manifest.append({'asset':path.relative_to(ROOT).as_posix(),'dimensions':[1920,1080], 'bytes':path.stat().st_size,'sha256':hashlib.sha256(path.read_bytes()).hexdigest(),'method':'Deterministic Pillow technical diagram','specification':description})

im,d=canvas('Oracle Database Architecture','One Linux host, one instance, multiple container boundaries',1)
box(d,(80,280,490,520),'Oracle Linux 9',('x86_64 host','Certified OS + kernel','Storage, network, time'))
box(d,(620,280,1060,520),'Oracle Instance',('SGA + background processes','Control files + online redo','Data files on /oradata'))
arrow(d,[(490,400),(620,400)])
box(d,(1190,280,1840,740),'Container Database (CDB)',('CDB$ROOT: administration','PDB$SEED: read-only template'))
box(d,(1240,505,1790,690),'ORCLPDB1',('Local application schemas','PDB service: ORCLPDB1'),accent=GREEN)
arrow(d,[(1060,400),(1190,400)])
box(d,(240,675,930,875),'Applications',('Authenticate to the PDB service','Listener routes the connection'),accent=GREEN)
arrow(d,[(930,775),(1100,775),(1100,600),(1240,600)],GREEN)
note(d,950,'PDBs share instance resources and host failure risk; a PDB is not a separate HA node.')
save(im,'oracle-database-architecture.png','Oracle Linux hosts Oracle instance/CDB; root and seed are separate from application PDB; applications connect through PDB services.')

im,d=canvas('Installation Workflow','Verified Enterprise Edition RPM on Oracle Linux 9 x86_64',2)
steps=[('01  Verify platform',('Certified OS / kernel / RU','RAM, disk, DNS, time')),('02  Prepare Linux',('Update packages + reboot','Keep SELinux + firewall')),('03  Preinstall RPM',('Dependencies + limits','oracle / oinstall / dba')),('04  Verify EE media',('OL9 x86_64 download','SHA-256 integrity check')),('05  Install EE RPM',('Inspect packaged config','Select approved data paths')),('06  Configure database',('Official RPM configure script','ORCLCDB + ORCLPDB1'))]
for i,(title,lines) in enumerate(steps):
    row=i//3; col=i%3; x=80+col*600; y=260+row*300
    box(d,(x,y,x+560,y+200),title,lines,size=30)
    if col<2: arrow(d,[(x+560,y+100),(x+600,y+100)])
arrow(d,[(1840,460),(1865,510),(60,510),(60,660),(80,660)])
box(d,(330,850,1590,990),'07  Validate before production',('SQL*Plus, listener, PDB state, remote login and installed RU',),accent=GREEN)
arrow(d,[(1560,760),(1560,810),(960,810),(960,850)],GREEN)
save(im,'oracle-database-installation-workflow.png','Sequential platform, Linux, preinstall RPM, media verification, EE install, official CDB/PDB configuration, acceptance validation.')

im,d=canvas('Database Service Lifecycle','RPM boot integration and Oracle Restart are distinct management paths',3)
box(d,(80,275,540,475),'Host boot',('Required filesystems mount','Service enablement verified'))
box(d,(700,275,1220,475),'RPM-provided integration',('Inspect package provenance','systemd / SysV compatibility'))
box(d,(1380,275,1840,475),'Database startup',('Listener + instance','PDB saved open state'))
arrow(d,[(540,375),(700,375)]);arrow(d,[(1220,375),(1380,375)])
box(d,(130,590,890,830),'Operational lifecycle',('Start  /  Stop  /  Restart','Journal + ADR logs','Reboot and remote-query verification'))
box(d,(1030,590,1790,830),'Optional Oracle Restart',('Standalone Grid Infrastructure','Monitors registered resources','Manage ownership with SRVCTL'),accent=GREEN)
note(d,910,'Do not run competing startup owners. Verify instance, PDB and listener health separately.')
note(d,960,'Oracle Restart improves local availability; it does not provide host failover by itself.')
save(im,'oracle-database-systemd-service.png','Boot integration derived only from the official RPM; lifecycle operations; Oracle Restart is an alternative monitoring owner, not multi-host failover.')

im,d=canvas('Security Hardening Architecture','Defense in depth across network, identity, database and keys',4)
box(d,(80,300,470,555),'Application network',('Private subnet allowlist','Named application identity','Secret rotation'),accent=GREEN,size=29)
box(d,(600,300,990,555),'Firewall boundary',('Approved sources only','TCP 1521 diagnostic','TCPS 2484 production'),size=29)
box(d,(1120,300,1510,555),'Protected listener',('Private address binding','Local registration checks','CA trust + DN matching'),size=29)
box(d,(1575,300,1840,555),'PDB',('Least privilege','Object grants','No app DBA'),size=29)
arrow(d,[(470,427),(600,427)]);arrow(d,[(990,427),(1120,427)]);arrow(d,[(1510,427),(1575,427)])
box(d,(130,685,630,900),'Host controls',('SELinux Enforcing','Restricted files + OSDBA','Patched OS + Oracle'))
box(d,(710,685,1210,900),'Audit + monitoring',('Failed logins + DDL','Central protected collector','Capacity + expiry alerts'))
box(d,(1290,685,1790,900),'Data + backup keys',('TDE / RMAN encryption','Protected wallet + key backup','Verify option licensing'),accent=RED)
note(d,970,'Encryption needs recoverable keys; network encryption and TDE protect different boundaries.')
save(im,'oracle-database-security-hardening.png','Allowlisted applications cross firewalld to CA-verified listener and restricted PDB users, backed by SELinux, auditing and licensed TDE/key protection.')

im,d=canvas('RMAN Backup & Recovery','Preserve a complete recovery chain and prove an isolated restore',5)
box(d,(80,300,460,555),'Production CDB',('Data + control file + SPFILE','ARCHIVELOG enabled','FRA monitored'),size=28)
box(d,(590,300,970,555),'RMAN jobs',('Weekly level 0 baseline','Daily level 1 changes','Redo every 15 minutes'),size=29)
box(d,(1100,300,1480,555),'Local backup mount',('/backup/oracle/','Pieces + restricted logs','Validation + retention'),size=29)
box(d,(1610,300,1840,555),'Off-host',('Independent','Immutable copies','Verify transfer'),accent=GREEN,size=29)
arrow(d,[(460,427),(590,427)]);arrow(d,[(970,427),(1100,427)]);arrow(d,[(1480,427),(1610,427)],GREEN)
box(d,(140,690,700,900),'Separate key + config backup',('Wallet, password file, net config','Recoverable keys, separate custody'),accent=RED,size=29)
box(d,(840,690,1770,900),'Isolated recovery target',('RESTORE baseline -> RECOVER increments + redo','Complete recovery or approved point-in-time recovery','Validate PDBs, applications, achieved RPO / RTO'),accent=GREEN,size=30)
arrow(d,[(1725,555),(1725,690)],GREEN)
arrow(d,[(700,795),(840,795)],RED)
note(d,965,'An ordinary full backup is not a level 1 parent. RMAN validation does not replace a restore drill.')
save(im,'oracle-database-rman-backup-recovery.png','Weekly level 0 and daily level 1 plus archived redo go to dedicated backup mount and independently verified off-host storage; isolated restore requires keys/config.')

parser=argparse.ArgumentParser()
parser.add_argument('--banner-source',required=True, type=Path)
args=parser.parse_args()
target=ROOT/'resources/assets/img/articles/banners/oracle-database-26ai-banner.png'
with Image.open(args.banner_source) as generated:
    native=generated.size
    generated.convert('RGB').resize((1000,1000),Image.Resampling.LANCZOS).save(target,format='PNG',optimize=True)
manifest.insert(0,{'asset':target.relative_to(ROOT).as_posix(),'dimensions':[1000,1000],'native_dimensions':list(native),'bytes':target.stat().st_size,'sha256':hashlib.sha256(target.read_bytes()).hexdigest(),'method':'Built-in imagegen; delivery-size resampling only','original':args.banner_source.name})
SOURCE.mkdir(parents=True,exist_ok=True)
(SOURCE/'image-manifest.json').write_text(json.dumps(manifest,indent=2)+'\n',encoding='utf-8')
(SOURCE/'image-prompts.json').write_text(json.dumps({'banner': {'tool':'built-in image_gen','prompt':'Professional 1000 x 1000 dark navy enterprise editorial banner. Crisp isometric Linux server, database cylinder, secure shield, backup storage, recovery arrow. Cyan highlights and Oracle red accents. Only English text: Oracle Database 26ai; Install • Secure • Recover; Oracle Linux. No stock imagery, watermark or vendor logo.'},'diagrams':manifest[1:]},ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
for entry in manifest:
    path=ROOT/entry['asset']
    with Image.open(path) as check:
        check.verify()
    with Image.open(path) as check:
        assert list(check.size)==entry['dimensions']
    published=ROOT/'public'/Path(entry['asset']).relative_to('resources')
    published.parent.mkdir(parents=True,exist_ok=True)
    shutil.copyfile(path,published)
print('Six real PNGs generated, optimized, dimension-checked and published.')
