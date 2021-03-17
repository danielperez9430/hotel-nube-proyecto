<?php

namespace Session;

class session {

    /**
     * Función que comprueba si tenemos la sessión iniciada
     */
    static function comprobar_sesion() {

        if (session_status() == PHP_SESSION_NONE) {  // Comprobamos si NO tiene una sesión activa
            session_start(); // Iniciamos sesión
            $_SESSION['rol'] = 'estandar'; // Asiganamos el rol
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

}
