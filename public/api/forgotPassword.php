<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;
use \Email\email as email;
use \Config\config as config;

s::comprobar_sesion();
s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';

$tool = new tools;

if (!$tool->comprobarEmail($email)) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'message' => 'Enter a valid email address'
    ]);
    exit;
}

try {
    $con = new db('conexion');
    $restablecimiento = $con->crearRestablecimiento($email);

    if ($restablecimiento !== null) {
        $sitio = rtrim(config::get('SITE_URL', 'http://localhost:8000'), '/');
        $enlace = $sitio . '/reset-password.php?token=' . $restablecimiento['token'];

        email::enviarRestablecimiento($email, $restablecimiento['nombre'], $enlace);
    }
} catch (\PDOException $e) {
    error_log('Error al crear el restablecimiento de contraseña: ' . $e->getMessage());
}

// Siempre la misma respuesta, para no revelar qué emails están registrados
echo json_encode([
    'result' => 'success',
    'message' => 'If the email is registered, you will receive a link to reset your password in a few minutes.'
]);
