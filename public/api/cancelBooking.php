<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;
use \Email\email as email;

s::comprobar_sesion();
s::verificarCsrf();

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

$numReserva = filter_input(INPUT_POST, 'booking', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
// Cargo que el cliente ha visto y aceptado
$cargoAceptado = filter_input(INPUT_POST, 'charge', FILTER_VALIDATE_FLOAT);

if (!$numReserva || $cargoAceptado === false || $cargoAceptado === null) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
    exit;
}

$con = new db('estandar');
$reserva = $con->obtenerReservaUsuario($numReserva, $usuario['id']);

if ($reserva === false) {
    // No existe o es de otro usuario: misma respuesta
    http_response_code(404);
    echo json_encode([
        'result' => 'fail',
        'code' => 2,
        'message' => 'Booking not found'
    ]);
    exit;
}

$cancelacion = (new tools)->cargoCancelacion($reserva['fecha_entrada'], (float) $reserva['primera_noche'], (int) $reserva['habitaciones']);

if ($reserva['estado'] !== 'confirmada' || $cancelacion === null) {
    http_response_code(409);
    echo json_encode([
        'result' => 'fail',
        'code' => 2,
        'message' => $reserva['estado'] !== 'confirmada'
            ? 'This booking is already cancelled'
            : 'This booking can no longer be cancelled online. Please contact us.'
    ]);
    exit;
}

// Si mientras tanto se ha pasado a otro tramo de la política, el cliente
// tiene que ver y aceptar el cargo nuevo
if (abs($cancelacion['cargo'] - $cargoAceptado) > 0.001) {
    http_response_code(409);
    echo json_encode([
        'result' => 'fail',
        'code' => 7,
        'charge' => $cancelacion['cargo'],
        'message' => 'The cancellation charge has changed. Please review it and confirm again.'
    ]);
    exit;
}

if (!$con->cancelarReserva($numReserva, $usuario['id'], $cancelacion['cargo'])) {
    http_response_code(409);
    echo json_encode([
        'result' => 'fail',
        'code' => 2,
        'message' => 'This booking is already cancelled'
    ]);
    exit;
}

// Correo de confirmación de la cancelación (si falla, la cancelación sigue siendo válida)
email::enviarCancelacion($usuario['email'], [
    'nombre' => $usuario['nombre'],
    'reserva' => $numReserva,
    'entrada' => $reserva['fecha_entrada'],
    'salida' => $reserva['fecha_salida'],
    'cargo' => $cancelacion['cargo']
]);

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'charge' => $cancelacion['cargo']
]);
