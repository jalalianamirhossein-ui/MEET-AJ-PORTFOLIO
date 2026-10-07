"""Export generated artwork at the requested delivery sizes; retain generated originals."""
from pathlib import Path
from PIL import Image
import json

ROOT = Path(__file__).resolve().parents[1]
GENERATED = Path('C:/Users/Victus/.codex/generated_images/01a117ac-79be-7dd1-99a0-762deab8eae2')
ASSETS = [
    ('exec-94b5bf30-b7f5-4d02-8341-ce106d13b301.png', 'banners', 'redis-production-banner.png', (1000, 1000)),
    ('exec-d7a86f74-80f4-4e5c-823a-e59ca7bbb200.png', 'content', 'redis-production-architecture.png', (1920, 1080)),
    ('exec-49a40218-2128-4afc-9c56-8afa555a1d6a.png', 'content', 'redis-replication-architecture.png', (1920, 1080)),
    ('exec-c05f9ae7-067d-47b9-af70-161670a55e50.png', 'content', 'redis-sentinel-high-availability.png', (1920, 1080)),
    ('exec-27fc767a-e3b4-4d17-ad12-42cb1770c4c1.png', 'content', 'redis-monitoring-architecture.png', (1920, 1080)),
]
manifest = []
for original, folder, filename, size in ASSETS:
    target = ROOT / 'resources/assets/img/articles' / folder / filename
    target.parent.mkdir(parents=True, exist_ok=True)
    with Image.open(GENERATED / original) as img:
        native_size = img.size
        # Delivery-size resampling only: do not redraw, composite, or change artwork.
        img.convert('RGB').resize(size, Image.Resampling.LANCZOS).save(target, optimize=True)
    manifest.append({'asset': str(target.relative_to(ROOT)).replace('\\', '/'), 'original': original, 'native_dimensions': native_size, 'export_dimensions': size, 'bytes': target.stat().st_size, 'operation': 'delivery-size resampling only'})
(ROOT / 'resources/content/articles/redis-installation-configuration-replication/image-manifest.json').write_text(json.dumps(manifest, indent=2), encoding='utf-8')
print('Exported five Redis images at requested sizes; generated originals retained.')
