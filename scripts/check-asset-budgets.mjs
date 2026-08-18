import { readFile, stat } from 'node:fs/promises';
import { gzipSync } from 'node:zlib';

const manifest = JSON.parse(await readFile('public/build/manifest.json', 'utf8'));
const budgets = {
    'resources/css/app.css': 30 * 1024,
    'resources/css/public.css': 30 * 1024,
    'resources/js/app.js': 100 * 1024,
};

let failed = false;
for (const [entry, budget] of Object.entries(budgets)) {
    const asset = manifest[entry];
    if (!asset?.file) throw new Error(`No se encontró ${entry} en el manifiesto de Vite.`);

    const path = `public/build/${asset.file}`;
    const contents = await readFile(path);
    const gzipBytes = gzipSync(contents).byteLength;
    const rawBytes = (await stat(path)).size;
    const status = gzipBytes <= budget ? 'OK' : 'EXCEDE';
    console.log(`${status} ${entry}: ${(gzipBytes / 1024).toFixed(1)} KB gzip (${(rawBytes / 1024).toFixed(1)} KB sin comprimir; presupuesto ${(budget / 1024).toFixed(0)} KB)`);
    failed ||= gzipBytes > budget;
}

if (failed) process.exitCode = 1;
