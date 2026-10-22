<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

if (!s::esAdmin()) {
    // Solo los administradores pueden modificar habitaciones
    http_response_code(403);
    echo json_encode([
        'result' => 'fail',
        'code' => 3
    ]);
    exit;
}

// Validamos los datos recibidos
$roomId = filter_input(INPUT_POST, 'roomId', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$tipo = trim((string) filter_input(INPUT_POST, 'tipo'));
$m2 = filter_input(INPUT_POST, 'm2', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 1, 'max_range' => 9999]]);
$precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);

// Las casillas llegan como 'true' o 'false'
$casilla = function ($nombre) {
    return filter_input(INPUT_POST, $nombre, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
};
$ventana = $casilla('ventana');
$internet = $casilla('internet');
$limpieza = $casilla('limpieza');

if ($roomId === false || $roomId === null || $tipo === '' || $m2 === false || $m2 === null
        || $precio === false || $precio === null || $ventana === null || $internet === null || $limpieza === null) {
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
    exit;
}

$con = new db($_SESSION['rol']);

$actualizado = $con->actualizarHabitacion($roomId, $tipo, $m2, $ventana, $internet, $limpieza, $precio);

echo json_encode([
    'result' => $actualizado ? 'success' : 'fail',
    'code' => $actualizado ? 0 : 2
]);
