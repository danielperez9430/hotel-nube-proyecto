<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;

// Cabecera que le indica al navegador el tipo de contenido que espera recibir
header('content-type: application/json; charset=utf-8');

// Establecemos la conexión
$con = new db('estandar');
// Obtenemos los tipos de habitacion
$data = $con->obtenerTiposHabitacion();
// Convertimos a JSON y los mostramos
echo json_encode($data);