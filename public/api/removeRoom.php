<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();
// Solo los administradores pueden borrar habitaciones
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

$roomId = filter_input(INPUT_POST, 'roomId', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$roomId) {
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
    exit;
}

// Devuelve 0 si no existe o tiene reservas asociadas
$borrado = (new db($_SESSION['rol']))->borrarHabitacion($roomId) === 1;

echo json_encode([
    'result' => $borrado ? 'success' : 'fail',
    'code' => $borrado ? 0 : 2
]);
