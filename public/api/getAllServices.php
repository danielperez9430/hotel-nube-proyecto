<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();
// Solo los administradores ven también los servicios no disponibles
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'services' => (new db($_SESSION['rol']))->obtenerTodosServicios()
]);
