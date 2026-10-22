<?php

namespace Session;

class session {

    /**
     * Función que comprueba si tenemos la sessión iniciada
     */
    static function comprobar_sesion() {

        if (session_status() == PHP_SESSION_NONE) {  // Comprobamos si NO tiene una sesión activa
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
