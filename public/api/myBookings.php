<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();

header('content-type: application/json; charset=utf-8');

$usuario = s::usuario();

if ($usuario === null) {
    http_response_code(401);
    echo json_encode([
        'result' => 'fail',
        'code' => 6
    ]);
    exit;
}

$con = new db('estandar');

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'bookings' => $con->obtenerReservasUsuario($usuario['id'])
]);
