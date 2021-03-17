<?php

require_once __DIR__ . '/../../autoload.php';

use \Tools\tools as tools;
use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();

if (isset($_POST['email']) && isset($_POST['password']) && $_POST['keepLogged']) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $keepLogged = $_POST['keepLogged'];

    $tool = new tools;
    $con = new db('admin');

    $loginCorrect = false;

    // Comprobamos si el correo es valido
    if ($tool->comprobarEmail($email)) {
        // Buscamos el usuario en la base de datos nos devuelve un array en caso de existir
        $datos_user = $con->buscarUsuario($email);

        if (is_array($datos_user)) {
            // Comprobamos si la contraseña es correcta
            $loginCorrect = $tool->comprobarContraseña($password, $datos_user[0]);
        }
    }

    unset($tool);
    unset($con);

    if ($loginCorrect) {
        // start session
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
?>