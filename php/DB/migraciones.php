<?php

namespace DB;

class migraciones {

    private $pdo;
    private $directorio;

    /**
     * Constructor
     * 
     * @param \PDO $pdo conexión con permisos para cambiar el esquema
     * @param string $directorio carpeta con los ficheros NNN_descripcion.sql
     */
    function __construct(\PDO $pdo, $directorio) {
        $this->pdo = $pdo;
        $this->directorio = $directorio;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Función que crea la tabla que registra las migraciones aplicadas
     */
    private function prepararTabla() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migraciones (
                              nombre VARCHAR(255) NOT NULL,
                              aplicada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                              PRIMARY KEY (nombre)
                          );");
    }

    /**
     * Función que devuelve las migraciones que aún no se han aplicado, en orden
     * 
     * @return array devuelve un array con los nombres de los ficheros
     */
    function pendientes() {
        $this->prepararTabla();

        $aplicadas = $this->pdo->query("SELECT nombre FROM migraciones")->fetchAll(\PDO::FETCH_COLUMN);
        $ficheros = array_map('basename', glob($this->directorio . '/[0-9][0-9][0-9]_*.sql') ?: []);
        sort($ficheros, SORT_STRING);

        return array_values(array_diff($ficheros, $aplicadas));
    }

    /**
     * Función que aplica las migraciones pendientes en orden. Si una falla se
     * detiene y lanza la excepción: las anteriores quedan registradas
     * 
     * @param callable|null $informar función que recibe el nombre de cada migración aplicada
     * @return array devuelve los nombres de las migraciones aplicadas
     * @throws \PDOException si falla alguna sentencia
     */
    function aplicar($informar = null) {
        $aplicadas = [];

        foreach ($this->pendientes() as $fichero) {
            $sql = file_get_contents($this->directorio . '/' . $fichero);

            foreach (self::separarSentencias($sql) as $sentencia) {
                $this->pdo->exec($sentencia);
            }

            $stmt = $this->pdo->prepare("INSERT INTO migraciones (nombre) VALUES (?)");
            $stmt->execute([$fichero]);

            $aplicadas[] = $fichero;

            if ($informar !== null) {
                $informar($fichero);
            }
        }

        return $aplicadas;
    }

    /**
     * Función que separa un script SQL en sentencias por los ; que no están
     * dentro de cadenas, identificadores entre comillas invertidas o
     * comentarios. Se ejecutan una a una porque con varias sentencias en un
     * solo exec() PDO solo informa del error de la primera
     * 
     * @param string $sql cadena de texto con el script
     * @return array devuelve un array con las sentencias (sin comentarios)
     */
    static function separarSentencias($sql) {
        $sentencias = [];
        $actual = '';
        $longitud = strlen($sql);
        $comilla = null;

        for ($i = 0; $i < $longitud; $i++) {
            $c = $sql[$i];
            $siguiente = $sql[$i + 1] ?? '';

            if ($comilla !== null) {
                $actual .= $c;

                if ($c === '\\' && $comilla !== '`') {
                    // Carácter escapado dentro de una cadena
                    $actual .= $siguiente;
                    $i++;
                } elseif ($c === $comilla) {
                    $comilla = null;
                }
                continue;
            }

            if ($c === '-' && $siguiente === '-') {
                // Comentario hasta el final de la línea
                $fin = strpos($sql, "\n", $i);
                $i = $fin === false ? $longitud : $fin;
                $actual .= "\n";
                continue;
            }

            if ($c === '/' && $siguiente === '*') {
                $fin = strpos($sql, '*/', $i + 2);
                $i = $fin === false ? $longitud : $fin + 1;
                continue;
            }

            if ($c === "'" || $c === '"' || $c === '`') {
                $comilla = $c;
            }

            if ($c === ';') {
                if (trim($actual) !== '') {
                    $sentencias[] = trim($actual);
                }
                $actual = '';
                continue;
            }

            $actual .= $c;
        }

        if (trim($actual) !== '') {
            $sentencias[] = trim($actual);
        }

        return $sentencias;
    }

}
