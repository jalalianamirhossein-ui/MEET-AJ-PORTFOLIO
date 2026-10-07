"""Syntax-only checks: never run the Linux deployment commands or MongoDB JS."""
from pathlib import Path
from html.parser import HTMLParser
import argparse
import json
import re
import subprocess

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'mongodb-installation-configuration-production-deployment'

class Blocks(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.blocks, self.current = [], None

    def handle_starttag(self, tag, attrs):
        if tag == 'code' and dict(attrs).get('class', '').startswith('language-'):
            self.current = [dict(attrs)['class'][9:], '']

    def handle_data(self, value):
        if self.current is not None: self.current[1] += value

    def handle_endtag(self, tag):
        if tag == 'code' and self.current is not None:
            self.blocks.append(self.current)
            self.current = None

args = argparse.ArgumentParser()
args.add_argument('--bash', required=True)
args.add_argument('--node', required=True)
opts = args.parse_args()
parser = Blocks()
parser.feed((ROOT / 'resources/legacy/articles' / (SLUG + '.html')).read_text(encoding='utf-8'))
destination = ROOT / 'storage/app/mongodb-snippet-checks'
destination.mkdir(parents=True, exist_ok=True)
counts = {'bash': 0, 'javascript': 0}
for number, (language, text) in enumerate(parser.blocks):
    if language not in counts: continue
    executable = opts.bash if language == 'bash' else opts.node
    suffix = '.sh' if language == 'bash' else '.js'
    if language == 'javascript':
        # 'use appdb' is a mongosh helper, not JavaScript syntax.
        text = re.sub(r'^use ([A-Za-z0-9_]+)\s*$', r'db = db.getSiblingDB("\1");', text, flags=re.M)
    file = destination / (str(number) + suffix)
    file.write_text(text + '\n', encoding='utf-8')
    result = subprocess.run([executable, '-n' if language == 'bash' else '--check', file.as_posix()], capture_output=True, text=True)
    if result.returncode:
        raise SystemExit(f'{file.name}: {result.stderr}')
    counts[language] += 1
report = {'checks': counts, 'result': 'pass', 'scope': 'Bash and JavaScript syntax only; commands were not executed on Linux or a MongoDB deployment.'}
(destination / 'report.json').write_text(json.dumps(report, indent=2), encoding='utf-8')
print(json.dumps(report))
