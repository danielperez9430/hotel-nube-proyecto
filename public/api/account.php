<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;

s::comprobar_sesion();
s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

$usuario = s::usuario();

if ($usuario === null) {
    http_response_code(401);
    echo json_encode([
        'result' => 'fail',
        'code' => 6
    ]);
    exit;
}

// Los datos de la cuenta se gestionan con el usuario de BD estándar, que
// solo puede leer y editar las columnas permitidas (nunca la contraseña)
$con = new db('estandar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['nombre', 'telf', 'pais', 'provincia', 'ciudad', 'direccion'];
    $datos = [];

    foreach ($campos as $campo) {
        $datos[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    $errores = (new tools)->validarDatosCuenta($datos);

    if (!empty($errores)) {
        http_response_code(400);
        echo json_encode([
            'result' => 'fail',
            'code' => 1,
            'errors' => $errores
        ]);
        exit;
    }

    $con->actualizarDatosUsuario($usuario['id'], $datos['nombre'], $datos['telf'], $datos['pais'],
            $datos['provincia'], $datos['ciudad'], $datos['direccion']);

    // El nombre se muestra en la cabecera
    $_SESSION['nombre'] = $datos['nombre'];
}

$cuenta = $con->obtenerUsuario($usuario['id']);

echo json_encode([
    'result' => $cuenta !== false ? 'success' : 'fail',
    'code' => $cuenta !== false ? 0 : 2,
    'account' => $cuenta
]);
