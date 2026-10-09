"""Normalize generated PNGs to the requested dimensions without cutting labels."""
from pathlib import Path
from PIL import Image, ImageOps
import argparse, hashlib, json
ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('manifest', help='JSON object mapping final image names to generated file paths')
args = parser.parse_args()
sources = json.loads(Path(args.manifest).read_text(encoding='utf-8'))
records = []
for name, source in sources.items():
    banner = name == 'zabbix-enterprise-monitoring-banner.png'
    size = (512, 512) if banner else (1920, 1080)
    destination = ROOT / 'resources/assets/img/articles' / ('banners' if banner else 'content') / name
    with Image.open(source) as original:
        original.load()
        normalized = ImageOps.contain(original.convert('RGB'), size, Image.Resampling.LANCZOS)
        canvas = Image.new('RGB', size, '#081323')
        canvas.paste(normalized, ((size[0]-normalized.width)//2, (size[1]-normalized.height)//2))
        destination.parent.mkdir(parents=True, exist_ok=True)
        canvas.save(destination, 'PNG', optimize=True)
    with Image.open(destination) as check:
        check.verify()
    records.append({'filename':name, 'dimensions':list(size), 'sha256':hashlib.sha256(destination.read_bytes()).hexdigest(), 'origin':'Built-in image generation; downsampled/padded without cropping'})
out = ROOT/'resources/content/articles/zabbix-server-linux-windows-agents-backup/images.json'
out.write_text(json.dumps(records, indent=2)+'\n', encoding='utf-8')
print(json.dumps(records, indent=2))
