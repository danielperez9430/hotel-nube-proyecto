<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();

// Cabecera que le indica al navegador el tipo de contenido que espera recibir
header('content-type: application/json; charset=utf-8');

if (isset($_POST['checkin']) && isset($_POST['checkout'])) {
    $fecha_entrada = $_POST['checkin'];
    $fecha_salida = $_POST['checkout'];

// Establecemos la conexión
    $con = new db($_SESSION['rol']);

// Obtenemos los tipos de habitacion
    $data = $con->filtrarHabitaciones($fecha_entrada, $fecha_salida);
// Convertimos a JSON y los mostramos
    echo json_encode($data);
} else {
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
}
