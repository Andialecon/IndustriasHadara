/**
 * Auditoría del front con Edge en modo headless a través del protocolo de
 * DevTools: mide el desbordamiento horizontal en móvil, lista los elementos que
 * lo provocan y guarda capturas de pantalla completas.
 *
 * Uso: node audit.mjs [ancho] [url ...]
 */

import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const HERE = dirname(fileURLToPath(import.meta.url));
const SHOTS = join(HERE, 'shots');
const PORT = 9333;

const EDGE = [
	'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
	'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
];

const width = Number(process.argv[2] || 390);
const urls = process.argv.slice(3);

if (!urls.length) {
	urls.push('http://industrias-hadara.local/');
}

mkdirSync(SHOTS, { recursive: true });

const edge = spawn(EDGE[0], [
	'--headless=new',
	'--disable-gpu',
	`--remote-debugging-port=${PORT}`,
	`--user-data-dir=${join(process.env.TEMP, 'edge-cdp')}`,
	'--no-first-run',
	'about:blank',
]);

edge.on('error', (error) => {
	console.error('No se pudo abrir Edge:', error.message);
	process.exit(1);
});

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function target() {
	for (let attempt = 0; attempt < 40; attempt += 1) {
		try {
			const list = await fetch(`http://127.0.0.1:${PORT}/json/list`).then((r) => r.json());
			const page = list.find((item) => item.type === 'page');

			if (page) {
				return page.webSocketDebuggerUrl;
			}
		} catch {
			// El navegador todavía no responde.
		}

		await sleep(250);
	}

	throw new Error('Edge no expuso el puerto de depuración.');
}

class Session {
	constructor(socket) {
		this.socket = socket;
		this.id = 0;
		this.pending = new Map();
		this.events = [];

		socket.addEventListener('message', (event) => {
			const message = JSON.parse(event.data);

			if (message.id && this.pending.has(message.id)) {
				const { resolve, reject } = this.pending.get(message.id);
				this.pending.delete(message.id);
				message.error ? reject(new Error(message.error.message)) : resolve(message.result);
			} else if (message.method) {
				this.events.push(message.method);
			}
		});
	}

	send(method, params = {}) {
		this.id += 1;
		const id = this.id;

		return new Promise((resolve, reject) => {
			this.pending.set(id, { resolve, reject });
			this.socket.send(JSON.stringify({ id, method, params }));
		});
	}

	async evaluate(expression) {
		const result = await this.send('Runtime.evaluate', {
			expression,
			returnByValue: true,
			awaitPromise: true,
		});

		return result.result.value;
	}
}

const MEASURE = `(() => {
	const doc = document.documentElement;
	const offenders = [];

	document.querySelectorAll('*').forEach((el) => {
		const rect = el.getBoundingClientRect();

		if (rect.width && rect.right > doc.clientWidth + 1) {
			offenders.push({
				tag: el.tagName.toLowerCase(),
				cls: (el.className || '').toString().slice(0, 70),
				right: Math.round(rect.right),
				width: Math.round(rect.width),
			});
		}
	});

	return JSON.stringify({
		viewport: doc.clientWidth,
		scrollWidth: doc.scrollWidth,
		offenders: offenders.slice(0, 12),
	});
})()`;

const socketUrl = await target();
const socket = new WebSocket(socketUrl);

await new Promise((resolve) => socket.addEventListener('open', resolve));

const session = new Session(socket);
await session.send('Page.enable');
await session.send('Runtime.enable');

for (const url of urls) {
	await session.send('Emulation.setDeviceMetricsOverride', {
		width,
		height: 900,
		deviceScaleFactor: 1,
		mobile: width < 700,
	});

	await session.send('Page.navigate', { url });
	await sleep(2200);

	const report = JSON.parse(await session.evaluate(MEASURE));
	console.log(url, JSON.stringify(report, null, 1));

	const name = (new URL(url).pathname.replace(/\W+/g, '-').replace(/^-|-$/g, '') || 'home') + `-${width}.png`;
	const shot = await session.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true });
	writeFileSync(join(SHOTS, name), Buffer.from(shot.data, 'base64'));
	console.log('captura:', join(SHOTS, name));
}

socket.close();
edge.kill();
