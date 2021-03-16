<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Session\session as s;

s::comprobar_sesion();

if (isset($_POST['roomId'])) {
    
    $roomId = $_POST['roomId']; // Obtenemos el ID de la habitación a borrar
   
    $con = new db($_SESSION['rol']); // Establecemos la conexión

    $borradoCorrecto = $con->borrarHabitacion($roomId); // Procedemos a borrar la habitación
 
    unset($con);

    if ($borradoCorrecto === 1) {
        echo json_encode([
            'result' => 'success',
            'code' => 0
        ]);
    } else {
        echo json_encode([
            'result' => 'fail',
            'code' => 2
        ]);
    }
} else {
    echo json_encode([
        'result' => 'fail',
        'code' => 1
    ]);
}
