<?php

namespace Email;

require(__DIR__ . '/../../vendor/autoload.php');

use \PHPMailer\PHPMailer\PHPMailer; // Importamos la liberia PHPMailer
use \Config\config as config;

class email {

    // Segundos máximos de espera al servidor SMTP y a hotelnube-api
    const TIMEOUT = 10;

    private $email;
    private $pass;

    function __construct() {

        $datos = $this->leer_configCorreo();

        $this->email = $datos[0];
        $this->pass = $datos[1];
    }

    /**
     * Función que indica si el envío de correos está configurado (MAIL_HOST)
     *
     * @return boolean devuelve true si hay un servidor SMTP configurado
     */
    static function estaConfigurado() {
        return config::get('MAIL_HOST', '') !== '';
    }

    /**
     * Función que envia un corre electorinico
     *
     * @param string $correo cadena de texto con la dirección del correo del usuario
     * @param string $cuerpo cadena de texto con la información del correo
     * @param string $asunto cadena de texto opcional con el asunto del correo
     * @return mixed Duelve true si el correo fue envia con exito, en caso contrario
     * devuelve el error
     */
    function enviarCorreo($correo, $cuerpo, $asunto = "") {

        $mail = new PHPMailer();
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;  // cambiar a 1 o 2 para ver errores
        $mail->Timeout = self::TIMEOUT;
        $mail->Host = config::get('MAIL_HOST');
        $mail->Port = (int) config::get('MAIL_PORT', 587);

        // Sin cifrado (por ejemplo, un servidor de pruebas local como Mailpit)
        $cifrado = config::get('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
        $mail->SMTPSecure = $cifrado;
        $mail->SMTPAutoTLS = $cifrado !== '';

        // Solo nos autenticamos si hay usuario configurado
        $mail->SMTPAuth = $this->email !== '';
        $mail->Username = $this->email;  // Cuenta del servidor SMTP
        $mail->Password = $this->pass; // Contraseña del servidor SMTP

        $mail->SetFrom(config::get('MAIL_FROM_ADDRESS', $this->email ?: 'no-reply@hotelnube.local'), config::get('MAIL_FROM_NAME', 'Hotel Nube'));
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
        return $this->pedirApi('/email/' . rawurlencode($nombre));
    }

    /**
     * Función que obtiene de hotelnube-api el HTML del correo de confirmación
     * de una reserva
     *
     * @param array $reserva array con id, order_date, nombre y habitaciones
     * (room_name, room_img, fecha_entrada, fecha_salida, precio)
     * @return string|false devuelve el HTML del correo o false si falla
     */
    function crearContenidoReserva($reserva) {
        return $this->pedirApi('/email/reserve/', $reserva);
    }

    /**
     * Función que envía el correo de bienvenida a un usuario nuevo. Los
     * errores se registran en el log sin interrumpir el registro
     *
     * @param string $correo cadena de texto con el email del usuario
     * @param string $nombre cadena de texto con el nombre del usuario
     * @return boolean devuelve true si el correo se envió
     */
    static function enviarBienvenida($correo, $nombre) {
        return self::enviarSeguro($correo, 'Welcome to Hotel Nube', function ($mail) use ($nombre) {
            return $mail->crearContenidoCorreo($nombre);
        });
    }

    /**
     * Función que envía el correo de confirmación de una reserva. Los errores
     * se registran en el log sin interrumpir la reserva
     *
     * @param string $correo cadena de texto con el email del usuario
     * @param array $reserva datos de la reserva (ver crearContenidoReserva)
     * @return boolean devuelve true si el correo se envió
     */
    static function enviarConfirmacionReserva($correo, $reserva) {
        return self::enviarSeguro($correo, 'Your Hotel Nube booking #' . $reserva['id'], function ($mail) use ($reserva) {
            return $mail->crearContenidoReserva($reserva);
        });
    }

    /**
     * Función que envía el enlace para restablecer la contraseña. Los errores
     * se registran en el log
     * 
     * @param string $correo cadena de texto con el email del usuario
     * @param string $nombre cadena de texto con el nombre del usuario
     * @param string $enlace URL completa para restablecer la contraseña
     * @return boolean devuelve true si el correo se envió
     */
    static function enviarRestablecimiento($correo, $nombre, $enlace) {
        return self::enviarSeguro($correo, 'Reset your Hotel Nube password', function () use ($nombre, $enlace) {
            return self::plantilla('restablecer', ['nombre' => $nombre, 'enlace' => $enlace]);
        });
    }

    /**
     * Función que genera el HTML de una plantilla de correo de views/correos
     * 
     * @param string $plantilla nombre de la plantilla (sin .php)
     * @param array $variables variables que recibe la plantilla
     * @return string devuelve el HTML del correo
     */
    static function plantilla($plantilla, $variables) {
        $fichero = __DIR__ . '/../../views/correos/' . basename($plantilla) . '.php';
        $variables['sitio'] = rtrim(config::get('SITE_URL', 'http://localhost:8000'), '/');

        // La plantilla se incluye en una función aparte y EXTR_SKIP impide que
        // sus variables pisen los parámetros
        $renderizar = static function ($__fichero, $__variables) {
            extract($__variables, EXTR_SKIP);
            ob_start();
            include $__fichero;
            return ob_get_clean();
        };

        return $renderizar($fichero, $variables);
    }

    /**
     * Función que genera el contenido con $crearCuerpo y envía el correo,
     * registrando en el log cualquier error
     *
     * @param string $correo dirección de destino
     * @param string $asunto asunto del correo
     * @param callable $crearCuerpo función que recibe la instancia y devuelve el HTML
     * @return boolean devuelve true si el correo se envió
     */
    private static function enviarSeguro($correo, $asunto, $crearCuerpo) {

        if (!self::estaConfigurado()) {
            error_log('Correo no enviado: MAIL_HOST no está configurado');
            return false;
        }

        try {
            $mail = new email();
            $cuerpo = $crearCuerpo($mail);

            if ($cuerpo === false || $cuerpo === '') {
                error_log('Correo no enviado: hotelnube-api no ha devuelto la plantilla');
                return false;
            }

            $resultado = $mail->enviarCorreo($correo, $cuerpo, $asunto);

            if ($resultado !== true) {
                error_log('Error al enviar el correo: ' . $resultado);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            error_log('Error al enviar el correo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Función que hace una petición a hotelnube-api. Si recibe datos los envía
     * por POST como JSON
     *
     * @param string $ruta ruta del endpoint
     * @param array|null $datos datos a enviar por POST
     * @return string|false devuelve la respuesta o false si falla
     */
    private function pedirApi($ruta, $datos = null) {

        // Dentro de Docker el servidor llega a la API por otra dirección que el navegador
        $api = rtrim(config::get('API_INTERNAL_URL', '') ?: config::get('API_URL', 'http://localhost:8001'), '/');

        $opciones = ['timeout' => self::TIMEOUT, 'ignore_errors' => false];

        if ($datos !== null) {
            $opciones['method'] = 'POST';
            $opciones['header'] = "Content-Type: application/json\r\n";
            $opciones['content'] = json_encode($datos);
        }

        return @file_get_contents($api . $ruta, false, stream_context_create(['http' => $opciones]));
    }

    /**
     * Función que lee del .env el usuario y la contraseña del servidor SMTP
     * (MAIL_USERNAME y MAIL_PASSWORD). Pueden estar vacíos si el servidor
     * no requiere autenticación
     *
     * @return array devuelve un array con el usuario y la contraseña
     */
    function leer_configCorreo() {
        return [config::get('MAIL_USERNAME', ''), config::get('MAIL_PASSWORD', '')];
    }

}
