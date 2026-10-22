<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;
use \Tools\tools as tools;

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
$tool = new tools;
$reservas = $con->obtenerReservasUsuario($usuario['id']);

// Precio de la primera noche y número de habitaciones de cada reserva
$porReserva = [];
foreach ($reservas as $reserva) {
    $porReserva[$reserva['num_reserva']]['primera_noche'] = ($porReserva[$reserva['num_reserva']]['primera_noche'] ?? 0) + (float) $reserva['precio_noche'];
    $porReserva[$reserva['num_reserva']]['habitaciones'] = ($porReserva[$reserva['num_reserva']]['habitaciones'] ?? 0) + 1;
}

// Cargo que tendría cancelar ahora cada reserva confirmada (null si ya no se puede)
foreach ($reservas as &$reserva) {
    $datos = $porReserva[$reserva['num_reserva']];
    $reserva['cancelacion'] = $reserva['estado'] === 'confirmada'
        ? $tool->cargoCancelacion($reserva['fecha_entrada'], $datos['primera_noche'], $datos['habitaciones'])
        : null;
}
unset($reserva);

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'bookings' => $reservas
]);
