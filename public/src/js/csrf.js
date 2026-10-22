// Añade el token CSRF de la página a todas las peticiones Ajax de jQuery.
// El servidor lo comprueba en las peticiones POST (Session\session::verificarCsrf)
$.ajaxSetup({
    headers: {
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
    }
});
