const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const root = path.resolve(__dirname, '..');
const source = path.join(root, 'resources/assets/img');
const published = path.join(root, 'public/assets/img');
const extensions = /\.(png|jpe?g|svg|gif|webp|ico|avif)$/i;
const hash = file => crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
function walk(dir) {
    return fs.readdirSync(dir, { withFileTypes: true }).flatMap(entry =>
        entry.isDirectory() ? walk(path.join(dir, entry.name)) : [path.join(dir, entry.name)]);
}

const errors = [];
const images = walk(source).filter(file => extensions.test(file));
for (const file of images) {
    const relative = path.relative(source, file);
    const output = path.join(published, relative);
    if (!fs.existsSync(output) || hash(file) !== hash(output)) {
        errors.push(`Missing or different published image: ${relative}`);
    }
    if (!/^(banners|articles|avatars|brand|icons|screenshots)[\\/]/.test(relative)) {
        errors.push(`Unclassified source image: ${relative}`);
    }
}
const sourceSet = new Set(images.map(file => path.relative(source, file)));
for (const file of walk(published).filter(file => extensions.test(file))) {
    if (!sourceSet.has(path.relative(published, file))) {
        errors.push(`Published image without maintained source: ${path.relative(published, file)}`);
    }
}

let references = 0;
const inputs = ['app', 'resources', 'scripts', 'docs/current'].flatMap(dir => walk(path.join(root, dir)))
    .filter(file => /\.(php|py|js|css|html|json|md)$/i.test(file));
for (const file of inputs) {
    for (const match of fs.readFileSync(file, 'utf8').matchAll(/\bimg\/([a-z0-9_./-]+\.(?:png|jpe?g|svg|gif|webp|ico|avif))\b/gi)) {
        references++;
        if (!fs.existsSync(path.join(source, match[1]))) {
            errors.push(`${path.relative(root, file)}: missing image ${match[1]}`);
        }
    }
}
for (const error of [...new Set(errors)]) console.error(error);
console.log(`${images.length} source images; ${references} image references; ${errors.length} errors.`);
process.exitCode = errors.length ? 1 : 0;
