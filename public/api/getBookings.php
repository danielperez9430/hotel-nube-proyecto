<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;
use \Config\config as config;

s::comprobar_sesion();
// Solo los administradores pueden ver las reservas de todos los clientes
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

$filtro = in_array($_GET['filter'] ?? '', ['proximas', 'canceladas', 'todas'], true) ? $_GET['filter'] : 'proximas';
// Fecha de hoy en la zona horaria del hotel
$hoy = (new \DateTimeImmutable('now', new \DateTimeZone(config::get('TIMEZONE', 'Europe/Madrid'))))->format('Y-m-d');

$con = new db($_SESSION['rol']);

echo json_encode([
    'result' => 'success',
    'code' => 0,
    'today' => $hoy,
    'occupancy' => $con->obtenerResumenOcupacion($hoy, 30),
    'bookings' => $con->obtenerReservasAdmin($filtro, $hoy)
]);
