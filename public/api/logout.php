<?php

require_once __DIR__ . '/../../autoload.php';

use \Session\session as s;

s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

// Destruimos la sesión
s::cerrarSesion();

echo json_encode(['result' => true]);
