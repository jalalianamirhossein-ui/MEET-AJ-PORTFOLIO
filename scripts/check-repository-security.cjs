// High-confidence signatures only; not exhaustive for secret types or history.
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '..');
const files = execFileSync('git', ['ls-files', '-z', '--cached', '--others', '--exclude-standard'], { cwd: root })
  .toString().split('\0').filter(Boolean);
const signatures = [
  ['private key', /-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----\r?\n[A-Za-z0-9+/=]{20}/],
  ['AWS access key', /\bAKIA[A-Z0-9]{16}\b/],
  ['GitHub token', /\bgh[pousr]_[A-Za-z0-9]{30,}\b/],
  ['GitHub fine-grained token', /\bgithub_pat_[A-Za-z0-9_]{60,}\b/],
];
const errors = [];
const seen = new Map();
let scanned = 0;
for (const file of new Set(files)) {
  const absolute = path.join(root, file);
  if (!fs.existsSync(absolute)) continue; // A staged rename may still have its old index path.
  const previous = seen.get(file.toLowerCase());
  if (previous && previous !== file) errors.push(`Case collision: ${previous} / ${file}`);
  seen.set(file.toLowerCase(), file);
  if (/(?:^|\/)\.env(?:$|\.)/.test(file) && !/\.example$/.test(file)) errors.push(`Tracked environment: ${file}`);
  if (/^(?:\.runtime|vendor|node_modules|storage\/(?:logs|app|framework))\//.test(file) && !file.endsWith('.gitignore')) {
    errors.push(`Private/generated file tracked: ${file}`);
  }
  const bytes = fs.readFileSync(absolute);
  if (bytes.includes(0) || bytes.length > 2_000_000) continue;
  scanned++;
  for (const [kind, signature] of signatures) {
    if (signature.test(bytes.toString('utf8'))) errors.push(`${file}: suspected ${kind} (value redacted)`);
  }
}
for (const error of errors) console.error(error);
if (process.argv.includes('--history')) {
  let history;
  try {
    history = execFileSync('git', ['log', '--all', '--format=', '--patch', '--no-ext-diff', '--no-textconv'], { cwd: root, maxBuffer: 128 * 1024 * 1024 }).toString('utf8');
  } catch {
    // Child-process exception objects can include captured source/secret bytes.
    console.error('Git history scan failed; captured content withheld. Check Git access and rerun.');
    process.exit(1);
  }
  for (const [kind, signature] of signatures) {
    if (signature.test(history)) {
      errors.push(`Available Git history: suspected ${kind} (value redacted)`);
      console.error(errors.at(-1));
    }
  }
  console.log('Available Git text history checked for the same high-confidence signatures.');
}
console.log(`${seen.size} files inventoried; ${scanned} text files scanned; ${errors.length} security/layout errors.`);
process.exitCode = errors.length ? 1 : 0;
