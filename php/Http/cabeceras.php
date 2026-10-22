<?php

namespace Http;

use \Config\config as config;

class cabeceras {

    // Evita enviarlas dos veces en la misma petición
    private static $enviadas = false;

    /**
     * Función que envía las cabeceras de seguridad HTTP de todas las
     * respuestas: política de seguridad de contenido (CSP), protección
     * contra clickjacking y sniffing, política de referer y de permisos
     */
    static function enviar() {

        if (self::$enviadas || headers_sent()) {
            return;
        }

        self::$enviadas = true;

        header('Content-Security-Policy: ' . self::politicaContenido());
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        header('Cross-Origin-Opener-Policy: same-origin');

        // HSTS solo tiene sentido (y solo se respeta) por HTTPS
        if (self::esHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Función que indica si la petición ha llegado por HTTPS. Detrás de un
     * proxy inverso que termina el TLS (TRUST_PROXY=true en el .env) se fía
     * de su cabecera X-Forwarded-Proto; sin él, la ignora, porque cualquier
     * cliente podría enviarla
     * 
     * @return boolean devuelve true si la conexión del navegador es HTTPS
     */
    static function esHttps() {

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        $confiarEnProxy = filter_var(config::get('TRUST_PROXY', 'false'), FILTER_VALIDATE_BOOLEAN);

        return $confiarEnProxy && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    /**
     * Función que construye la Content-Security-Policy. Todo se sirve desde la
     * propia web salvo las llamadas a hotelnube-api (API_URL). Los estilos en
     * línea se permiten porque Bootstrap y el HTML usan atributos style
     * 
     * @return string cadena de texto con la política
     */
    static function politicaContenido() {

        $conexiones = ["'self'"];
        $api = parse_url((string) config::get('API_URL', 'http://localhost:8001'));

        if (!empty($api['scheme']) && !empty($api['host'])) {
            $conexiones[] = $api['scheme'] . '://' . $api['host'] . (isset($api['port']) ? ':' . $api['port'] : '');
        }

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self'",
            'connect-src ' . implode(' ', $conexiones),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'"
        ]);
    }

}
