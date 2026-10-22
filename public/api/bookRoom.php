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

$usuario = s::usuario();

if ($usuario === null) {
    // Hay que iniciar sesión para reservar
    http_response_code(401);
    echo json_encode([
        'result' => 'fail',
        'code' => 6
    ]);
    exit;
}

$roomId = filter_input(INPUT_POST, 'roomId', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$estancia = (new tools)->validarEstancia($_POST['checkin'] ?? '', $_POST['checkout'] ?? '');

// Servicios extra elegidos (opcional): una lista de ids
$idsServicios = filter_input(INPUT_POST, 'services', FILTER_VALIDATE_INT, ['flags' => FILTER_REQUIRE_ARRAY, 'options' => ['min_range' => 1]]) ?? [];

if (!is_array($idsServicios) || in_array(false, $idsServicios, true)) {
    $estancia = ['error' => 'Invalid services'];
}

if (!$roomId || isset($estancia['error'])) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'code' => 1,
        'message' => $estancia['error'] ?? 'Invalid room'
    ]);
    exit;
}

// Las reservas son una acción de cliente: usan el usuario de BD estándar
$con = new db('estandar');
$servicios = $con->obtenerServiciosDisponibles($idsServicios);

if (count($servicios) !== count(array_unique($idsServicios))) {
    // Algún servicio no existe o ya no está disponible
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'code' => 1,
        'message' => 'Some of the selected extras are no longer available'
    ]);
    exit;
}

$reserva = $con->crearReserva($usuario['id'], $roomId, $estancia['entrada'], $estancia['salida'], $servicios);

if ($reserva === false) {
    // La habitación no existe o la ha reservado otra persona
    http_response_code(409);
    echo json_encode([
        'result' => 'fail',
        'code' => 2
    ]);
    exit;
}

$extras = round(array_sum(array_column($servicios, 'precio_servicio')), 2);
$total = round($reserva['precio_noche'] * $estancia['noches'] + $extras, 2);

// Correo de confirmación (si falla, la reserva sigue siendo válida)
$sitio = rtrim(config::get('SITE_URL', 'http://localhost:8000'), '/');

email::enviarConfirmacionReserva($usuario['email'], [
    'id' => $reserva['num_reserva'],
    'order_date' => date('Y-m-d'),
    'nombre' => $usuario['nombre'],
    'habitaciones' => [[
        'room_name' => $reserva['tipo_habitacion'] . ' (room ' . $roomId . ')',
        'room_img' => $sitio . '/' . ltrim((string) $reserva['imagen'], '/'),
        'fecha_entrada' => $estancia['entrada'],
        'fecha_salida' => $estancia['salida'],
        'precio' => round($reserva['precio_noche'] * $estancia['noches'], 2)
    ]],
    'servicios' => array_map(fn($servicio) => [
        'nombre' => $servicio['nombre_servicio'],
        'precio' => (float) $servicio['precio_servicio']
    ], $servicios)
]);

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'booking' => $reserva['num_reserva'],
    'nights' => $estancia['noches'],
    'extras' => $extras,
    'total' => $total
]);
