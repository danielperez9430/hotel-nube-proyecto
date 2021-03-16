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

    // Variable que usaremos para controlar si todo el proceso fue correcto
    $loginCorrect = false;

    // Comprobamos si el correo es valido
    if ($tool->comprobarEmail($email)) {
        // Buscamos el usuario en la base de datos nos devuelve un array en caso de existir
        $user = $con->buscarUsuario($email);

        // Comprobamos si obtuvimos un resultado de la base de datos
        if ($user != false) {
            // Comprobamos si la contraseña es correcta
            $loginCorrect = $tool->comprobarContraseña($password, $user['password']);
            
            // Si el login fue correcto asignamos los datos del usuario a la sesión actual
            if($loginCorrect) {
                
            // Añadimos los datos a la sessión del usuario
            s::asignarDatosSession($user['id'], $user['email'], $user['nombre'], $user['nombre_rol']);
            
            $con->actualizarUltimoAcceso($user['id']); // Actualizamos la fecha de último acceso
            }
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