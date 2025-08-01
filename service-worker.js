const CACHE_NAME = 'fit40plus-v3';
const urlsToCache = [
  './',
  './index.php',
  './style.css',
  './script.js',
  './manifest.json',
  './favicon.ico',
  './js/achievements.js',
  './js/todo.js',
  './js/dark-mode.js'
];

// Kurulum: Gerekli dosyaları cache'e al
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
  );
});

// Fetch: Cache varsa onu kullan, yoksa internetten al
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        return response || fetch(event.request);
      })
  );
});

// Cache'i güncelle
self.addEventListener('activate', (event) => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});