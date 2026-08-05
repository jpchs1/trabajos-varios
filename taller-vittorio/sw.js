/* Service worker mínimo: deja la app disponible sin conexión.
   Estrategia: se sirve lo cacheado y en paralelo se actualiza desde la red. */
const CACHE = 'vittorio-v2';
const ASSETS = [
  './',
  'index.html',
  'assets/styles.css',
  'assets/datos.js',
  'assets/app.js',
  'assets/vistas.js',
  'assets/editor.js',
  'assets/documento.js',
  'assets/icono.svg',
  'manifest.webmanifest'
];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(ASSETS)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET' || new URL(req.url).origin !== location.origin) return;

  e.respondWith(
    caches.match(req).then((cacheado) => {
      const red = fetch(req)
        .then((res) => {
          if (res && res.ok) {
            const copia = res.clone();
            caches.open(CACHE).then((c) => c.put(req, copia));
          }
          return res;
        })
        .catch(() => cacheado || caches.match('index.html'));
      return cacheado || red;
    })
  );
});
