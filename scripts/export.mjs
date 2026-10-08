import { spawnSync } from 'node:child_process';
import { existsSync, readdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = fileURLToPath(new URL('../', import.meta.url));
let php = process.env.PHP_BINARY || 'php';
if (!process.env.PHP_BINARY && process.platform === 'win32') {
  const directory = path.resolve(root, '../../bin/php');
  if (existsSync(directory)) {
    const versions = readdirSync(directory).sort((a, b) => b.localeCompare(a, undefined, { numeric: true }));
    const candidate = versions.map(v => path.join(directory, v, 'php.exe')).find(binary => {
      if (!existsSync(binary)) return false;
      const probe = spawnSync(binary, ['-d', 'xdebug.mode=off', '-v'], { encoding: 'utf8' });
      return probe.status === 0 && !/Failed loading/i.test(probe.stderr || '');
    });
    if (candidate) php = candidate;
  }
}
const result = spawnSync(php, ['-d', 'xdebug.mode=off', path.join(root, 'scripts/export-static.php')], {
  cwd: root, stdio: 'inherit', env: process.env
});
if (result.error) console.error('Cannot start PHP. Install PHP 8 with DOM or set PHP_BINARY to your WAMP php.exe.');
process.exit(result.status ?? 1);
