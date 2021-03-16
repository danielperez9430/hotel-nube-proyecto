<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Email\email as email;
use \Session\session as s;

s::comprobar_sesion();

$campos = array('name', 'email', 'telf', 'pais', 'provincia', 'ciudad', 'direccion', 'password');

$tool = new tools;
$correo = new email;

if ($tool->comprobarDatosRegistro($campos)) {

    $nombre = $_POST['name'];
    $email = $_POST['email'];
    $telf = $_POST['telf'];
    $pais = $_POST['pais'];
    $provincia = $_POST['provincia'];
    $ciudad = $_POST['ciudad'];
    $direccion = $_POST['direccion'];
    // Ciframos la contraseña
    $password = $tool->cifrarContraseña($_POST['password']);

    $con = new db('admin');
    // Registramos al usario en la base de datos
   $resultado = $con->registrarUsuario($nombre, $email, $telf, $pais, $provincia, $ciudad, $direccion, $password);

   if($resultado === 1) {
       $registerCorrect = true;
       
       // TODO: Moverlo a la transacción
       $cuerpo = $correo->crearContenidoCorreo($_POST['name']);
       $correo->enviarCorreo($email, $cuerpo, "Alta Usuario");
       
       $user = $con->buscarUsuario($email);
       s::asignarDatosSession($user['id'], $user['email'], $user['nombre'], $user['nombre_rol']);
       
   } else {
       $registerCorrect = false;
   }
   
    if ($registerCorrect) {
        // start session
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
?>