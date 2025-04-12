self.addEventListener('install', function(event) {
  // Fuerza al SW a activarse inmediatamente
  self.skipWaiting();
});

self.addEventListener('activate', function(event) {
  // Toma el control de todos los clients inmediatamente
  event.waitUntil(clients.claim());
});

self.addEventListener('fetch', function(event) {
  const requestUrl = new URL(event.request.url);

  // Excluir las solicitudes al servidor de impresión
  if (requestUrl.pathname.includes('/print_server/') || 
      requestUrl.pathname.includes('novabox_printer_server.php')) {
    console.log('Solicitud de impresión detectada, no manejada por SW');
    return fetch(event.request); // Pasar directamente la solicitud
  }

  // Excluir solicitudes POST y otras no-GET
  if (event.request.method !== 'GET') {
    console.log('Solicitud no-GET detectada, no manejada por SW');
    return fetch(event.request);
  }

  // Para solicitudes GET, simplemente las pasa a la red
  event.respondWith(fetch(event.request));
});