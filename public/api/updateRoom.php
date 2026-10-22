<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();
// Solo los administradores pueden modificar habitaciones
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

$roomId = filter_input(INPUT_POST, 'roomId', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$datos = (new tools)->validarDatosHabitacion($_POST);

if (!$roomId || $datos === null) {
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
    exit;
}

$con = new db($_SESSION['rol']);

$actualizado = $con->actualizarHabitacion($roomId, $datos['tipo'], $datos['m2'], $datos['ventana'], $datos['internet'], $datos['limpieza'], $datos['precio']);

echo json_encode([
    'result' => $actualizado ? 'success' : 'fail',
    'code' => $actualizado ? 0 : 2
]);
