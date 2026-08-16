#!/usr/bin/env node
/**
 * Rehuq preview server — runs the Laravel backend through PHP.wasm (Node).
 * This harness is only for sandboxed previews; on a real machine you run
 * `php artisan serve` (see backend/README.md).
 *
 * Usage: node preview/server.mjs [--port 8080] [--reset]
 */
import { loadNodeRuntime, createNodeFsMountHandler } from '@php-wasm/node';
import { PHP, PHPRequestHandler, sandboxedSpawnHandlerFactory } from '@php-wasm/universal';
import { createServer } from 'node:http';
import { existsSync, mkdirSync, unlinkSync, writeFileSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const BACKEND = join(ROOT, 'backend');
const PORT = Number(process.env.PORT || process.argv[2] || '8080');
const RESET = process.argv.includes('--reset');

const LOG = (...a) => console.log(`[preview]`, ...a);

const toBuf = (chunk) => (chunk instanceof Uint8Array ? Buffer.from(chunk) : Buffer.from(String(chunk)));

function bytesToU8(result) {
  if (result?.bytes instanceof Uint8Array) return result.bytes;
  if (result?.bytes && typeof result.bytes === 'object') {
    return Uint8Array.from(Object.values(result.bytes));
  }
  return new Uint8Array(0);
}

let instanceCounter = 0;

/**
 * Creates a fresh PHP instance with the backend mounted at /app.
 * A fresh instance per CLI command is required because php-wasm accumulates
 * CLI argv between `cli()` calls and offers no way to reset it.
 */
async function createPhpInstance() {
  const runtime = await loadNodeRuntime('8.3', {
    emscriptenOptions: { processId: 100 + instanceCounter++ },
  });
  const php = new PHP(runtime);
  await php.ready;
  php.setSpawnHandler(sandboxedSpawnHandlerFactory(async () => ({ php, reap: async () => {} })));
  await php.mount('/app', createNodeFsMountHandler(BACKEND));
  php.chdir('/app');
  const composerPhar = join(__dirname, 'composer.phar');
  if (existsSync(composerPhar)) {
    php.writeFile('/home/composer.phar', new Uint8Array(readFileSync(composerPhar)));
  }
  return php;
}

async function runCli(php, args) {
  const proc = await php.cli(['php', ...args], { env: process.env });
  const out = [];
  for await (const chunk of proc.stdout) out.push(toBuf(chunk));
  const err = [];
  for await (const chunk of proc.stderr) err.push(toBuf(chunk));
  const text = Buffer.concat(out).toString('utf8');
  const errText = Buffer.concat(err).toString('utf8');
  const code = await proc.exitCode;
  return { code, out: text, err: errText };
}

async function main() {
  LOG('booting PHP.wasm (PHP 8.3)…');
  const php = await createPhpInstance();

  // ---- setup steps -------------------------------------------------------
  mkdirSync(join(BACKEND, 'database'), { recursive: true });

  // Auto-create .env from .env.example when missing (fresh clones)
  const envPath = join(BACKEND, '.env');
  if (!existsSync(envPath) && existsSync(join(BACKEND, '.env.example'))) {
    LOG('creating .env from .env.example…');
    writeFileSync(envPath, readFileSync(join(BACKEND, '.env.example')));
  }
  if (existsSync(envPath) && !readFileSync(envPath, 'utf8').includes('APP_KEY=base64')) {
    LOG('generating application key…');
    const keyPhp = await createPhpInstance();
    await runCli(keyPhp, ['/app/artisan', 'key:generate', '--force']);
  }

  const dbPath = join(BACKEND, 'database/database.sqlite');
  if (RESET && existsSync(dbPath)) {
    LOG('resetting database…');
    unlinkSync(dbPath);
  }
  if (!existsSync(dbPath)) {
    writeFileSync(dbPath, '');
  }

  if (!existsSync(join(BACKEND, 'vendor/composer/autoload_static.php'))) {
    LOG('running composer dump-autoload…');
    const c = await runCli(php, ['/home/composer.phar', 'dump-autoload', '--no-scripts']);
    LOG('composer:', (c.out + c.err).split('\n').filter(Boolean).slice(-3).join(' | '));
    if (c.code !== 0) throw new Error('composer dump-autoload failed: ' + c.err.slice(0, 1500));
  }

  LOG('running migrations…');
  const migratePhp = await createPhpInstance();
  const mig = await runCli(migratePhp, ['/app/artisan', 'migrate', '--force']);
  LOG('migrate:', (mig.out + mig.err).split('\n').filter(Boolean).slice(-3).join(' | '));
  if (mig.code !== 0) throw new Error('migrate failed: ' + (mig.err + mig.out).slice(0, 1500));

  LOG('seeding database…');
  const seedPhp = await createPhpInstance();
  const seed = await runCli(seedPhp, ['/app/artisan', 'db:seed', '--force']);
  LOG('seed:', (seed.out + seed.err).split('\n').filter(Boolean).slice(-3).join(' | '));
  if (seed.code !== 0) throw new Error('seed failed: ' + (seed.err + seed.out).slice(0, 1500));

  // ---- HTTP server --------------------------------------------------------
  const requestHandler = new PHPRequestHandler({
    php,
    documentRoot: '/app/public',
    absoluteUrl: `http://localhost:${PORT}`,
    getFileNotFoundAction: () => ({ type: 'internal-redirect', uri: '/index.php' }),
  });

  const server = createServer(async (req, res) => {
    const url = new URL(req.url, `http://${req.headers.host || 'localhost'}`);
    const chunks = [];
    for await (const chunk of req) chunks.push(chunk);
    const body = Buffer.concat(chunks);

    try {
      const response = await requestHandler.request({
        method: req.method,
        url: url.pathname + url.search,
        headers: {
          host: req.headers.host || 'localhost',
          'content-type': req.headers['content-type'],
          ...(req.headers.cookie ? { cookie: req.headers.cookie } : {}),
          ...(req.headers['x-session-token'] ? { 'x-session-token': req.headers['x-session-token'] } : {}),
          ...(req.headers.authorization ? { authorization: req.headers.authorization } : {}),
          'x-forwarded-for': req.headers['x-forwarded-for'],
          ...(req.headers.origin ? { origin: req.headers.origin } : {}),
        },
        body: body.length ? new Uint8Array(body) : undefined,
      });

      const out = bytesToU8(response);
      const status = response.httpStatusCode || 200;
      const headers = {};
      for (const [k, v] of Object.entries(response.headers || {})) {
        headers[k] = Array.isArray(v) ? v.join(', ') : v;
      }
      res.writeHead(status, { ...headers, 'content-type': headers['content-type'] || 'application/json; charset=UTF-8' });
      res.end(out);
    } catch (e) {
      LOG('request error:', e.message);
      res.writeHead(500, { 'content-type': 'application/json' });
      res.end(JSON.stringify({ error: e.message }));
    }
  });

  server.listen(PORT, '0.0.0.0', () => {
    LOG(`Rehuq API listening on http://0.0.0.0:${PORT}`);
  });

  const shutdown = async () => {
    LOG('shutting down…');
    server.close();
    process.exit(0);
  };
  process.on('SIGTERM', shutdown);
  process.on('SIGINT', shutdown);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
