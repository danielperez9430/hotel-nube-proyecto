<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

$token = isset($_POST['token']) ? (string) $_POST['token'] : '';
$password = isset($_POST['password']) ? (string) $_POST['password'] : '';

$tool = new tools;
$error = $tool->validarPassword($password);

if ($error !== null) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'message' => $error
    ]);
    exit;
}

$cambiada = false;

try {
    $con = new db('conexion');
    $cambiada = $con->restablecerPassword($token, $tool->cifrarContraseña($password));
} catch (\PDOException $e) {
    error_log('Error al restablecer la contraseña: ' . $e->getMessage());
}

if (!$cambiada) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'message' => 'This link is not valid or has expired. Request a new one.'
    ]);
    exit;
}

echo json_encode([
    'result' => 'success',
    'message' => 'Your password has been changed. You can now log in.'
]);
