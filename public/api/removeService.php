<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();
// Solo los administradores pueden borrar servicios
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$borrado = $id && (new db($_SESSION['rol']))->borrarServicio($id);

echo json_encode([
    'result' => $borrado ? 'success' : 'fail',
    'code' => $borrado ? 0 : 2
]);
