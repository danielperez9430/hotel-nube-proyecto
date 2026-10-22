<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;

s::comprobar_sesion();

header('content-type: application/json; charset=utf-8');

// Validamos las fechas de la estancia (DD-MM-YYYY o YYYY-MM-DD)
$estancia = (new tools)->validarEstancia($_GET['checkin'] ?? '', $_GET['checkout'] ?? '');

if (isset($estancia['error'])) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'code' => 1,
        'message' => $estancia['error']
    ]);
    exit;
}

$con = new db('estandar');

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'checkin' => $estancia['entrada'],
    'checkout' => $estancia['salida'],
    'nights' => $estancia['noches'],
    'rooms' => $con->buscarHabitaciones($estancia['entrada'], $estancia['salida'])
]);
