<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();
// Solo los administradores pueden crear habitaciones
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

$datos = (new tools)->validarDatosHabitacion($_POST);

if ($datos === null) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
    exit;
}

$con = new db($_SESSION['rol']);
$id = $con->crearHabitacion($datos['tipo'], $datos['m2'], $datos['ventana'], $datos['internet'], $datos['limpieza'], $datos['precio']);

if ($id === 0) {
    // El tipo de habitación no existe
    echo json_encode([
        'result' => 'fail',
        'code' => 2
    ]);
    exit;
}

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'room' => $id
]);
