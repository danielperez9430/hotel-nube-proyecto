<?php

namespace Tools;

use \DB\operacionesDB as DB;
use \Config\config as config;

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

        $db = new DB('conexion');
        $datos_user = $db->buscarUsuario($email);

        // buscarUsuario devuelve [hash, rol] o false si el usuario no existe
        return is_array($datos_user) && $this->comprobarContraseña($password, $datos_user[0]);
    }

    /**
     * Función que sirve para asignar un rol a la sesión del usuario
     * 
     * @param string $rol cadena de texto con el rol actual
     */
    function asignarRol($rol) {

        $_SESSION['rol'] = $rol;
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

    // Número máximo de noches de una reserva
    const MAX_NOCHES = 30;

    /**
     * Función que convierte una fecha en formato DD-MM-YYYY (el del selector
     * de fechas) o YYYY-MM-DD en un objeto fecha
     * 
     * @param string $texto cadena de texto con la fecha
     * @return \DateTimeImmutable|null devuelve la fecha o null si no es válida
     */
    function parsearFecha($texto) {

        foreach (['!d-m-Y', '!Y-m-d'] as $formato) {
            $fecha = \DateTimeImmutable::createFromFormat($formato, trim((string) $texto));

            // Comprobamos que la fecha exista (createFromFormat acepta 31-02)
            if ($fecha !== false && $fecha->format(substr($formato, 1)) === trim((string) $texto)) {
                return $fecha;
            }
        }

        return null;
    }

    /**
     * Función que valida las fechas de una estancia
     * 
     * @param string $entrada cadena de texto con la fecha de entrada
     * @param string $salida cadena de texto con la fecha de salida
     * @return array devuelve ['entrada' => 'Y-m-d', 'salida' => 'Y-m-d',
     * 'noches' => n] o ['error' => mensaje] si no son válidas
     */
    function validarEstancia($entrada, $salida) {

        $fechaEntrada = $this->parsearFecha($entrada);
        $fechaSalida = $this->parsearFecha($salida);
        // Fecha de hoy en la zona horaria del hotel
        $hoy = (new \DateTimeImmutable('now', new \DateTimeZone(config::get('TIMEZONE', 'Europe/Madrid'))))->format('Y-m-d');

        if ($fechaEntrada === null || $fechaSalida === null) {
            return ['error' => 'Invalid dates'];
        }

        if ($fechaEntrada->format('Y-m-d') < $hoy) {
            return ['error' => 'The check-in date has already passed'];
        }

        $noches = (int) $fechaEntrada->diff($fechaSalida)->format('%r%a');

        if ($noches < 1) {
            return ['error' => 'The check-out date must be after the check-in date'];
        }

        if ($noches > self::MAX_NOCHES) {
            return ['error' => 'The maximum stay is ' . self::MAX_NOCHES . ' nights'];
        }

        return [
            'entrada' => $fechaEntrada->format('Y-m-d'),
            'salida' => $fechaSalida->format('Y-m-d'),
            'noches' => $noches
        ];
    }

    /**
     * Función que valida los datos personales de una cuenta según los
     * límites de la tabla usuarios
     * 
     * @param array $datos array con nombre, telf, pais, provincia, ciudad y direccion
     * @return array devuelve un array campo => mensaje con los errores
     * (vacío si los datos son válidos)
     */
    function validarDatosCuenta($datos) {

        // Longitud máxima de cada campo en la base de datos
        $limites = ['nombre' => 255, 'telf' => 13, 'pais' => 255, 'provincia' => 255, 'ciudad' => 255, 'direccion' => 60];
        $errores = [];

        foreach ($limites as $campo => $maximo) {
            $valor = trim((string) ($datos[$campo] ?? ''));

            if ($valor === '') {
                $errores[$campo] = 'Required';
            } elseif (mb_strlen($valor) > $maximo) {
                $errores[$campo] = "Maximum $maximo characters";
            }
        }

        // Prefijo internacional y 9 cifras, como en el formulario: +34 612345678
        if (!isset($errores['telf']) && !preg_match('/^\+[1-9]\d{0,2} \d{9}$/', trim($datos['telf']))) {
            $errores['telf'] = 'Invalid phone number';
        }

        return $errores;
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
            if (empty($_POST[$value])) {
                $correcto = false;
            }
        }
        
        return $correcto;
    }
}
