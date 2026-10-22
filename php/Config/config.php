<?php

namespace Config;

class config {

    // Variables leídas del fichero .env
    private static $valores = null;

    // Ruta del fichero .env en la raíz del proyecto
    private static $fichero = __DIR__ . '/../../.env';

    /**
     * Función que obtiene el valor de una variable de configuración.
     * Las variables de entorno del sistema tienen prioridad sobre el .env
     * 
     * @param string $clave nombre de la variable
     * @param mixed $porDefecto valor devuelto si la variable no existe
     * @return mixed devuelve el valor de la variable o $porDefecto
     */
    static function get($clave, $porDefecto = null) {

        $entorno = getenv($clave);

        if ($entorno !== false) {
            return $entorno;
        }

        if (self::$valores === null) {
            self::$valores = self::leerFichero(self::$fichero);
        }

        return self::$valores[$clave] ?? $porDefecto;
    }

    /**
     * Función que obtiene una variable obligatoria
     * 
     * @param string $clave nombre de la variable
     * @return string devuelve el valor de la variable
     * @throws \RuntimeException si la variable no está definida
     */
    static function requerida($clave) {

        $valor = self::get($clave);

        if ($valor === null || $valor === '') {
            throw new \RuntimeException("Falta la variable de configuración $clave (revisa el fichero .env)");
        }

        return $valor;
    }

    /**
     * Función que lee un fichero .env con líneas CLAVE=valor. Ignora las
     * líneas vacías y los comentarios (#) y quita las comillas del valor
     * 
     * @param string $fichero cadena de texto con la ruta del fichero
     * @return array devuelve un array asociativo con las variables
     */
    private static function leerFichero($fichero) {

        $valores = [];

        if (!is_readable($fichero)) {
            return $valores;
        }

        foreach (file($fichero, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);

            if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) {
                continue;
            }

            list($clave, $valor) = explode('=', $linea, 2);
            $valor = trim($valor);

            // Quitamos las comillas que rodean el valor
            if (strlen($valor) >= 2 && ($valor[0] === '"' || $valor[0] === "'") && substr($valor, -1) === $valor[0]) {
                $valor = substr($valor, 1, -1);
            }

            $valores[trim($clave)] = $valor;
        }

        return $valores;
    }

}
