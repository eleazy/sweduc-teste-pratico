
const CACHE_NAME = 'v1';
const CACHE_FILES = [];

// Evento de instalação: adiciona os arquivos ao cache
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
    .then(cache => {
      console.log('Opened cache');
      return cache.addAll(CACHE_FILES);
    })
  );
});

// Evento de busca: serve os arquivos do cache quando offline
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Serve o arquivo do cache, ou faz uma requisição se não estiver no cache
        return response || fetch(event.request);
      })
  );
});

// Evento de ativação: remove caches antigos
self.addEventListener('activate', event => {
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
