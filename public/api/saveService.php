<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();
// Solo los administradores pueden crear o modificar servicios
s::exigirAdminApi();

header('content-type: application/json; charset=utf-8');

// Sin id se crea un servicio nuevo
$id = null;

if (($_POST['id'] ?? '') !== '') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: false;
}

$datos = (new tools)->validarDatosServicio($_POST);

if ($id === false || $datos === null) {
    http_response_code(400);
    echo json_encode([
        'result' => 'fail',
        'code' => 1,
        'message' => 'Check the name, the description, the price and the icon (for example "fas fa-spa").'
    ]);
    exit;
}

$guardado = (new db($_SESSION['rol']))->guardarServicio($id, $datos);

echo json_encode([
    'result' => $guardado > 0 ? 'success' : 'fail',
    'code' => $guardado > 0 ? 0 : 2,
    'service' => $guardado
]);
