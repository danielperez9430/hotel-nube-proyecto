<?php

require_once __DIR__ . '/../../autoload.php';
use \Session\session as s;
s::comprobar_sesion();

// Eliminamos los datos de la varible superglobal $_SESSION
unset($_SESION);
// Destruimos la sessión
session_destroy();

echo json_encode(['result' => true])
?>