<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();

header('content-type: application/json; charset=utf-8');

// Servicios disponibles, que se pueden añadir como extras a una reserva
echo json_encode([
    'result' => 'success',
    'code' => 0,
    'services' => (new db('estandar'))->obtenerServicios()
]);
