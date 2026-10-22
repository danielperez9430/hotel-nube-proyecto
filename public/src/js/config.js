// Configuración que el servidor publica en etiquetas <meta>. Se lee desde
// un fichero porque la Content-Security-Policy no permite scripts en línea
window.API_URL = document.querySelector('meta[name="api-url"]').content;
