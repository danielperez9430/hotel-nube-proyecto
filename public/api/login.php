<?php

require_once __DIR__ . '/../../autoload.php';

use \Tools\tools as tools;
use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

// Límite de intentos fallidos en la ventana de tiempo
const MAX_INTENTOS_EMAIL = 5;
const MAX_INTENTOS_IP = 20;
const VENTANA_MINUTOS = 15;

if (isset($_POST['email'], $_POST['password'])) {
    $email = (string) $_POST['email'];
    $password = (string) $_POST['password'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $tool = new tools;
    // El rol 'conexion' solo lee usuarios y roles y registra los intentos fallidos
    $con = new db('conexion');

    // Comprobamos si se han superado los intentos permitidos
    $intentos = $con->contarIntentosLogin($email, $ip, VENTANA_MINUTOS);

    if ($intentos['email'] >= MAX_INTENTOS_EMAIL || $intentos['ip'] >= MAX_INTENTOS_IP) {
        http_response_code(429);
        header('Retry-After: ' . (VENTANA_MINUTOS * 60));
        echo json_encode([
            'result' => 'fail',
            'code' => 5
        ]);
        exit;
    }

    $datos_user = false;

    // Comprobamos si el correo es valido
    if ($tool->comprobarEmail($email)) {
        // Buscamos el usuario en la base de datos nos devuelve un array en caso de existir
        $datos_user = $con->buscarUsuario($email);
    }

    // Si el usuario no existe comprobamos contra un hash ficticio, para que
    // el tiempo de respuesta no revele qué emails están registrados
    $hash = is_array($datos_user) ? $datos_user[0] : '$2y$10$tuGRzW181DbkOJ50fTSVxe5A1Zd4krKt2RlJKEIPE3HuA.Ql0bL6y';
    $loginCorrect = $tool->comprobarContraseña($password, $hash) && is_array($datos_user);

    if ($loginCorrect) {
        $con->borrarIntentosLogin($email);

        // Regeneramos el id de sesión para evitar ataques de fijación de sesión
        session_regenerate_id(true);
        s::cambiarRol($datos_user[1]);
        $_SESSION['email'] = $email;

        echo json_encode([
            'result' => 'success',
            'code' => 0
        ]);
    } else {
        $con->registrarIntentoFallido($email, $ip);

        echo json_encode([
            'result' => 'fail',
            'code' => 2
        ]);
    }

    unset($tool, $con);
} else {
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
}
