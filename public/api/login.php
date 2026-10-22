<?php

require_once __DIR__ . '/../../autoload.php';

use \Tools\tools as tools;
use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

if (isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $tool = new tools;
    $loginCorrect = false;

    // Comprobamos si el correo es valido
    if ($tool->comprobarEmail($email)) {
        // El rol 'conexion' solo tiene permisos de lectura, suficiente para el login
        $con = new db('conexion');
        // Buscamos el usuario en la base de datos nos devuelve un array en caso de existir
        $datos_user = $con->buscarUsuario($email);
        unset($con);

        if (is_array($datos_user)) {
            // Comprobamos si la contraseña es correcta
            $loginCorrect = $tool->comprobarContraseña($password, $datos_user[0]);
        }
    }

    unset($tool);

    if ($loginCorrect) {
        // Regeneramos el id de sesión para evitar ataques de fijación de sesión
        session_regenerate_id(true);
        s::cambiarRol($datos_user[1]);
        $_SESSION['email'] = $email;

        echo json_encode([
            'result' => 'success',
            'code' => 0
        ]);
    } else {
        echo json_encode([
            'result' => 'fail',
            'code' => 2
        ]);
    }
} else {
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
}
