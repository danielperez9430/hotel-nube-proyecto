<?php

namespace Session;

class session {

    /**
     * Función que comprueba si tenemos la sessión iniciada
     */
    static function comprobar_sesion() {

        if (session_status() == PHP_SESSION_NONE) {  // Comprobamos si NO tiene una sesión activa
            // Cookie de sesión inaccesible desde JavaScript, no enviada desde
            // otras webs y solo por HTTPS cuando la web usa HTTPS
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
            ]);
            session_start(); // Iniciamos sesión
        }

        // Solo asignamos el rol por defecto si la sesión aún no tiene uno,
        // para no perder el rol obtenido al hacer login
        if (!isset($_SESSION['rol'])) {
            $_SESSION['rol'] = 'estandar';
        }
    }

    /**
     * Función que modifica el rol acutual del usuario
     * 
     * @param string $rol cadena de texto con el nombre del rol
     */
    static function cambiarRol($rol) {
        $_SESSION['rol'] = $rol;
    }

    /**
     * Función que inicia la sesión de un usuario tras el login o el registro.
     * Regenera el id de sesión para evitar ataques de fijación de sesión
     * 
     * @param Number $id id del usuario
     * @param string $nombre nombre del usuario
     * @param string $email email del usuario
     * @param string $rol nombre del rol del usuario
     */
    static function iniciarSesionUsuario($id, $nombre, $email, $rol) {
        self::comprobar_sesion();
        session_regenerate_id(true);

        $_SESSION['id_usuario'] = (int) $id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['email'] = $email;
        self::cambiarRol($rol);
    }

    /**
     * Función que devuelve los datos del usuario con sesión iniciada
     * 
     * @return array|null devuelve ['id', 'nombre', 'email', 'rol'] o null si
     * no hay ningún usuario identificado
     */
    static function usuario() {
        self::comprobar_sesion();

        if (empty($_SESSION['id_usuario'])) {
            return null;
        }

        return [
            'id' => $_SESSION['id_usuario'],
            'nombre' => $_SESSION['nombre'],
            'email' => $_SESSION['email'],
            'rol' => $_SESSION['rol']
        ];
    }

    /**
     * Función que indica si el usuario actual es administrador
     * 
     * @return boolean devuelve true si el rol de la sesión es admin
     */
    static function esAdmin() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
    }

    /**
     * Función que redirige a la página principal si el usuario
     * no es administrador
     */
    static function comprobarAdmin() {
        if (!self::esAdmin()) {
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Función que devuelve el token CSRF de la sesión, creándolo si no existe
     * 
     * @return string cadena de texto con el token
     */
    static function tokenCsrf() {
        self::comprobar_sesion();

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    /**
     * Función que comprueba el token CSRF de las peticiones POST. El token
     * llega en la cabecera X-CSRF-Token (la añade src/js/csrf.js). Si no es
     * válido responde 403 y termina la ejecución
     */
    static function verificarCsrf() {
        self::comprobar_sesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (empty($_SESSION['csrf']) || !is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
            http_response_code(403);
            header('content-type: application/json; charset=utf-8');
            echo json_encode(['result' => 'fail', 'code' => 4]);
            exit;
        }
    }

    /**
     * Función que cierra la sesión actual
     */
    static function cerrarSesion() {
        self::comprobar_sesion();
        $_SESSION = [];

        // Borramos también la cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

}
