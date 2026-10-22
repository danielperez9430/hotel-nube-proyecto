<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();
// Solo los administradores pueden ver el listado completo
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

echo json_encode((new db($_SESSION['rol']))->obtenerListaHabitaciones());
