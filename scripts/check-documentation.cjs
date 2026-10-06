const fs=require('fs'),path=require('path');
const root=path.resolve(__dirname,'..');
const folders=['docs','deploy','resources'];
function walk(dir){return fs.readdirSync(dir,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(dir,e.name)):[path.join(dir,e.name)]);}
const files=[path.join(root,'README.md'),...folders.flatMap(f=>walk(path.join(root,f)))].filter(f=>/\.md$/i.test(f)).sort();
const rel=f=>path.relative(root,f).replaceAll('\\','/');
const index=path.join(root,'docs','DOCUMENTATION-INDEX.md');
if(process.argv.includes('--write-index')){
  const list=files.filter(f=>f!==index).map(f=>{const title=fs.readFileSync(f,'utf8').match(/^#\s+(.+)$/m)?.[1]||path.basename(f);return `| [${rel(f)}](${path.relative(path.dirname(index),f).replaceAll('\\','/')}) | ${title.replaceAll('|','/')} |`;});
  fs.writeFileSync(index,'# Complete documentation inventory\n\nGenerated from the checkout with `node scripts/check-documentation.cjs --write-index`. Current operational authority: [PROJECT-STATUS.md](current/PROJECT-STATUS.md). Dated reports, archives and implementation phases retain their original evidence dates.\n\n| File | Title |\n|---|---|\n'+list.join('\n')+'\n');
  if(!files.includes(index))files.push(index);
}
let count=0,broken=[];
for(const file of files){
  const source=fs.readFileSync(file,'utf8').replace(/```[^\n]*\n[\s\S]*?```/g,'');
  for(const m of source.matchAll(/!?\[[^\]\n]*\]\(([^)\n]+)\)/g)){
    let target=m[1].split(/\s+"/)[0].replace(/^<|>$/g,'').split('#')[0];
    if(!target||/^[a-z][a-z0-9+.-]*:/i.test(target))continue;
    try{target=decodeURIComponent(target)}catch{broken.push(`${rel(file)}: invalid encoded link ${target}`);continue;}
    count++;
    if(!fs.existsSync(path.resolve(path.dirname(file),target)))broken.push(`${rel(file)}: missing ${target}`);
  }
}
for(const issue of broken)console.error(issue);
console.log(`${files.length} Markdown documents; ${count} local file links; ${broken.length} broken links.`);
process.exitCode=broken.length?1:0;
