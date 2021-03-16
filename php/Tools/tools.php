<?php

namespace Tools;

use \DB\operacionesDB as DB;

class tools {

    /**
     * Función que comprueba si contraseña es correcta
     * 
     * @param string $password cadena de texto con la clave introducida por el usuario
     * @param string $hash cadena de texto con el hash de la clave
     * @return boolean devuelve true si es correcta y false en caso contrario
     */
    function comprobarContraseña($password, $hash) {

        return password_verify($password, $hash);
    }

    /**
     * Función que cifra la contraseña
     * 
     * @param string $password cadena de texto con la contraseña
     * @return string cadena de texto con la contraseña cifrada
     */
    function cifrarContraseña($password) {

        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Función para validar los datos de login de un usuario
     * 
     * @param string $email cadena de texto con el correo del usuario
     * @param string $password cadena de texto con la clave del usuario
     * @return boolean devuelve true si los datos del usuario son correctos y
     * en caso contrario devuelve false
     */
    function validarUsuario($email, $password) {

        $correcto = false;

        $db = new DB;
        $hash = $db->buscarUsuario($email);

        if ($this->comprobarContraseña($password, $hash)) {
            $correcto = true;
        }

        return $correcto;
    }

    /**
     * Función que sirve para asignar un rol a la sesión del usuario
     * 
     * @param string $rol cadena de texto con el rol actual
     */
    function asignarRol($rol) {

        $_SESION['rol'] = $rol;
    }

    /**
     * Función que comprueba si el correo introducido por un usuario es
     * correcto
     * 
     * @param string $email cadena de texto con el correo introducido por el usuario
     * @return mixed devuelve una string con el email o false en caso de no ser
     * correcto
     */
    function comprobarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Función que comprueba si los campos enviados por POST no están vacios
     * y existen
     * 
     * @param array $array Array con los nombre de las variables $_POST a buscar
     * @return boolean devuelve true si los datos son correctos y
     * false en caso contrario
     */
    function comprobarDatosRegistro($array) {
        $correcto = true;
        
        foreach ($array as $value) {
            // Comprobamos si algún campo no tiene su valor
            if(!isset($_POST[$value]) || empty($_POST[$value])) {
                $correcto = false;
            }
        }
        
        return $correcto;
    }
}
