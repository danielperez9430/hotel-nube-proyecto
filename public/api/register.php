<?php

require_once __DIR__ . '/../../autoload.php';

use \DB\operacionesDB as db;
use \Tools\tools as tools;
use \Session\session as s;
use \Email\email as email;

s::verificarCsrf();

header('content-type: application/json; charset=utf-8');

$campos = array('name', 'email', 'telf', 'pais', 'provincia', 'ciudad', 'direccion', 'password');

$tool = new tools;

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

    $idUsuario = 0;

    if ($tool->comprobarEmail($email)) {
        // El usuario estándar solo puede insertar en usuarios, suficiente para registrarse
        $con = new db('estandar');

        try {
            // Registramos al usario en la base de datos
            $idUsuario = $con->registrarUsuario($nombre, $email, $telf, $pais, $provincia, $ciudad, $direccion, $password);
        } catch (\PDOException $e) {
            // Por ejemplo, si el correo ya está registrado
            error_log('Error al registrar el usuario: ' . $e->getMessage());
        }
    }

    if ($idUsuario > 0) {
        // Iniciamos la sesión del nuevo usuario y le damos la bienvenida
        s::iniciarSesionUsuario($idUsuario, $nombre, $email, 'estandar');
        email::enviarBienvenida($email, $nombre);

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
