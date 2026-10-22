<?php

namespace Email;

require(__DIR__ . '/../../vendor/autoload.php');

use \PHPMailer\PHPMailer\PHPMailer; // Importamos la liberia PHPMailer
use \Config\config as config;

class email {

    private $email;
    private $pass;
      
    function __construct() {
        
        $datos = $this->leer_configCorreo();

        $this->email = $datos[0];
        $this->pass = $datos[1];
    }

    /**
     * Función que envia un corre electorinico
     * 
     * @param string $correo cadena de texto con la dirección del correo del usuario
     * @param string $cuerpo cadena de texto con la información del correo
     * @param sting $asunto cadena de texto opcional con el asunto del correo
     * @return mixed Duelve true si el correo fue envia con exito, en caso contrario
     * devuelve el error
     */
    function enviarCorreo($correo, $cuerpo, $asunto = "") {

        /*
         * Recibe un array de direcciones de correo, el cuerpo del correo y el asunto.
         * Envía el correo a todas las direcciones.
         */
        $mail = new PHPMailer();
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;  // cambiar a 1 o 2 para ver errores
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = config::get('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
        $mail->Host = config::get('MAIL_HOST', 'smtp.gmail.com');
        $mail->Port = (int) config::get('MAIL_PORT', 587);
        $mail->Username = $this->email;  // Cuenta del servidor SMTP
        $mail->Password = $this->pass; // Contraseña del servidor SMTP
        $mail->SetFrom(config::get('MAIL_FROM_ADDRESS', $this->email), config::get('MAIL_FROM_NAME', 'Hotel Nube'));
        $mail->Subject = $asunto;
        $mail->MsgHTML($cuerpo);
        $mail->isHTML(true);

        $mail->AddAddress($correo, $correo);

        if (!$mail->Send()) {
            return $mail->ErrorInfo;
        } else {
            return TRUE;
        }
    }

    /**
     * Función que obtiene de hotelnube-api el HTML del correo de bienvenida
     * 
     * @param string $nombre cadena de texto con el nombre del usuario
     * @return string|false devuelve el HTML del correo o false si falla
     */
    function crearContenidoCorreo($nombre) {

        $api = rtrim(config::get('API_URL', 'http://localhost:8001'), '/');

        return file_get_contents($api . '/email/' . rawurlencode($nombre));
    }

    /**
     * Función que lee del .env el usuario y la contraseña del servidor SMTP
     * (MAIL_USERNAME y MAIL_PASSWORD)
     * 
     * @return array devuelve un array con el usuario y la contraseña
     * @throws RuntimeException si falta alguna de las variables
     */
    function leer_configCorreo() {
        return [config::requerida('MAIL_USERNAME'), config::requerida('MAIL_PASSWORD')];
    }

}
