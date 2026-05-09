const CACHE_NAME = 'montia-v2';
const ASSETS = [
  'index.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Instalación y cacheo de estáticos
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
  );
});

// Estrategia: Network First para la API y archivos dinámicos
self.addEventListener('fetch', (e) => {
  // No cachear llamadas a la API
  if (e.request.url.includes('api.php')) {
    return;
  }
  
  e.respondWith(
    fetch(e.request).catch(() => caches.match(e.request))
  );
});
