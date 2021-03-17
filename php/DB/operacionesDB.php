<?php

namespace DB;

class operacionesDB {

    private $nombreBBDD = 'hotel';
    private $usuario = 'juan';
    private $pwd = '1234';
    private $servidor = "localhost";
    private $PDO;
    // Ficheros de configuración
    private $xml_file = __DIR__ . '/../../config/configuracion.xml';
    private $xsd = __DIR__ . '/../../config/configuracion.xsd';

    /**
     * Constructor
     */
    function __construct($rol) {
        $datos = $this->leer_configuracion($this->xml_file, $this->xsd, $rol);
        $this->usuario = $datos[0];
        $this->pwd = $datos[1];

        $this->PDO = $this->conexion();
    }

    /**
     * Destructor
     */
    public function __destruct() {
        unset($this->PDO);
    }

    /**
     * Función para conectarnos a la base de datos
     * 
     * @return \PDO
     */
    protected function conexion() {
        try {

            $pdo = new \PDO("mysql:host={$this->servidor};dbname={$this->nombreBBDD};charset=utf8", $this->usuario, $this->pwd);

            return $pdo;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Método que lee el fichero de configuración y obtiene los datos de conexión
     * de la base de datos 
     * 
     * @param string $xml_file cadana de texto con la ruta del xml
     * @param string $xsd  cadana de texto con la ruta del xsd
     * @return array devuelve un array con el usuario y la contraseña
     * @throws PDOException
     */
    function leer_configuracion($xml_file, $xsd, $rol) {

        //Devuelve un array de 2 strings: nombre y password
        $conf = new \DOMDocument();
        $conf->load($xml_file);

        if (!$conf->schemaValidate($xsd)) {
            throw new \PDOException("Ficheiro de usuarios no valido");
        }


        $xml = simplexml_load_file($xml_file);
        // Conversión a cadena de texto "" evitando que xpath devuelva un objecto
        $array = [
            "" . $xml->xpath('//nombre[../rol="' . $rol . '"]')[0],
            "" . $xml->xpath('//password[../rol="' . $rol . '"]')[0]
        ];
        return $array;
    }

    /**
     * Función que busca un usuario en la base de datos y nos retorna
     * su hash y rol en caso de existir
     * 
     * @param string $email cadena de texto el correo del usuario
     * @return mixed devuelve un array con el hash y el rol del usuario o un 
     * false en caso de que el usuario no exista
     */
    function buscarUsuario($email) {
        $stmt = "";
        $resultado = false;

        $sql = "SELECT password, nombre_rol FROM hotel.usuarios
                 inner join roles
                  on usuarios.rol_usuario = roles.id
                   where email = ?;";

        $db = $this->PDO;

        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $email, \PDO::PARAM_STR);

            $stmt->execute(); // Ejecutamos la setencia preparada

            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {
                $resultado = array($row['password'], $row['nombre_rol']);
            }
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);


        return $resultado;
    }

    /**
     * Función que actualiza los datos de un usuario
     * 
     * @param Number $id número del id del usuario
     * @param string $nombre cadena de texto con el nombre del usuario
     * @param string $email cadena de texto con el email del usuario
     * @param string $telf cadena de texto con número de telf del usuario
     * @param string $pais cadena de texto con el pais del usuario
     * @param string $provincia cadena de texto con la provincia
     * @param string $ciudad cadena de texto con la ciudad
     * @param type $direccion
     * @return Number devuelve 0 si no se modificaron los datos en la base de datos
     * y 1 en caso de que se actualizará
     */
    function actualizarDatosUsuario($id, $nombre, $email, $telf, $pais, $provincia, $ciudad, $direccion) {

        $resultado = 0;

        $sql = "UPDATE usuarios
                SET `nombre` = ?, `email` = ?,
                `telf` = ?, `pais` = ?, `provincia` = ?,
                `ciudad` = ?, `direccion` = ?
                    WHERE (`id` = ?);";


        $db = $this->PDO;

        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $nombre, \PDO::PARAM_STR);
            $stmt->bindValue(2, $email, \PDO::PARAM_STR);
            $stmt->bindValue(3, $telf, \PDO::PARAM_STR);
            $stmt->bindValue(4, $pais, \PDO::PARAM_STR);
            $stmt->bindValue(5, $provincia, \PDO::PARAM_STR);
            $stmt->bindValue(6, $ciudad, \PDO::PARAM_STR);
            $stmt->bindValue(7, $direccion, \PDO::PARAM_STR);
            $stmt->bindValue(8, $id, \PDO::PARAM_INT);

            // Ejecutamos la consulta
            $stmt->execute();

            // Obtenemos el número de filas modificadas
            $resultado = $stmt->rowCount();
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $resultado;
    }

    /**
     * Función que registra a un nuevo usuario en la base de datos
     * 
     * @param string $nombre cadena de texto con el nombre del usuario
     * @param string $email cadena de texto con el email del usuario
     * @param string $telf cadena de texto con número de telf del usuario
     * @param string $pais cadena de texto con el pais del usuario
     * @param string $provincia cadena de texto con la provincia
     * @param string $ciudad cadena de texto con la ciudad
     * @param string $direccion cadena de texto con la dirección
     * @param string $password cadena de texto con la contraseña
     * @param string $rol_usuario id del rol del usuario
     * @return 
     */
    function registrarUsuario($nombre, $email, $telf, $pais, $provincia, $ciudad, $direccion, $password, $rol_usuario = 2) {

        $resultado = 0;

        $sql = "INSERT usuarios 
                 (nombre, email, telf, pais, provincia, ciudad, direccion, password, rol_usuario)
                    values(?, ?, ?, ?, ?, ?, ?, ? ,?);";


        $db = $this->PDO;

        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $nombre, \PDO::PARAM_STR);
            $stmt->bindValue(2, $email, \PDO::PARAM_STR);
            $stmt->bindValue(3, $telf, \PDO::PARAM_STR);
            $stmt->bindValue(4, $pais, \PDO::PARAM_STR);
            $stmt->bindValue(5, $provincia, \PDO::PARAM_STR);
            $stmt->bindValue(6, $ciudad, \PDO::PARAM_STR);
            $stmt->bindValue(7, $direccion, \PDO::PARAM_STR);
            $stmt->bindValue(8, $password, \PDO::PARAM_STR);
            $stmt->bindValue(9, $rol_usuario, \PDO::PARAM_INT);


            // Ejecutamos la consulta
            $stmt->execute();

            // Obtenemos el número de filas modificadas
            $resultado = $stmt->rowCount();
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $resultado;
    }

    /**
     * Función que obtiene los tipos de habitación
     * 
     * @return array devuelve un array asociatipo con el id, tipo_habitacion
     * y descripcion de las mismas
     */
    function obtenerTiposHabitacion() {

        $result = array();

        $sql = "SELECT * FROM hotel.habitacion_tipo";

        $db = $this->PDO;

        if (($stmt = $db->query($sql))) {

            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {
                array_push($result, array('id' => $row['id'], 'tipo_habitacion' => $row['tipo_habitacion'],
                    'descripcion' => $row['descripcion']));
            }
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $result;
    }

    /**
     * 
     * @param type $fecha_entrada
     * @param type $fecha_salida
     */
    function buscarHabitaciones($fecha_entrada, $fecha_salida) {
        
    }

    /**
     * Función que obtiene todas las imagenes de una habitación en concreto
     * 
     * @param Number $id_habitacion_tipo número (id) del tipo de habitación
     * @return Array devuelve un array con los datos de las imagenes
     */
    function buscarImagenesHabitacion($id_habitacion_tipo) {

        $imagenes = array();

        $sql = "SELECT *
                 FROM imagenes_habitaciones
                  where id_habitacion_tipo = ?;";

        $db = $this->PDO; // Puntero de conexión a la base de datos


        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $id_habitacion_tipo, \PDO::PARAM_INT);

            $stmt->execute(); // Ejecutamos la setencia preparada

            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {
                // Cargamos los datos de las imagenes
                array_push($imagenes, array($row['imagen_habitacion'], $row['descripcion_imagen']));
            }
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $imagenes;
    }

}
