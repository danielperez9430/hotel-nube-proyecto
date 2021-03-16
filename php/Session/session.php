<?php

namespace Session;

class session {

    /**
     * Función que comprueba si tenemos la sessión iniciada
     */
    static function comprobar_sesion() {

        if (session_status() == PHP_SESSION_NONE) {  // Comprobamos si NO tiene una sesión activa
            session_start(); // Iniciamos sesión
            
            // Si no existe un rol asignado le asignamos el estandar
            if(!isset($_SESSION['rol'])) {
            $_SESSION['rol'] = 'estandar'; // Asiganamos el rol
            }
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
     * 
     * 
     * @param number $userID número de id del usuario
     * @param string $userEmail cadena de texto con el email del usuario
     * @param string $userName cadena de texto con el nombre del usuario
     * @param string $rol cadena de texto con el rol del usuario
     */
    static function asignarDatosSession($userID, $userEmail, $userName, $rol) {
        $_SESSION['userId'] = $userID;
        $_SESSION['userEmail'] = $userEmail;
        $_SESSION['userName'] = $userName;
        $_SESSION['rol'] = $rol;
    }
    
    static function comprobarAdmin() {
        if($_SESSION['rol'] != 'admin'){
            header("Location: /");
        }
    }

}
