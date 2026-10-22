<?php

namespace DB;

use \Config\config as config;

class operacionesDB {

    // Roles de base de datos con credenciales propias en el .env
    const ROLES = ['admin', 'conexion', 'estandar'];

    private $nombreBBDD;
    private $usuario;
    private $pwd;
    private $servidor;
    private $puerto;
    private $PDO;

    /**
     * Constructor
     * 
     * @param string $rol cadena de texto con el rol de la conexión
     */
    function __construct($rol) {
        $datos = $this->leer_configuracion($rol);
        $this->usuario = $datos[0];
        $this->pwd = $datos[1];

        $this->servidor = config::get('DB_HOST', 'localhost');
        $this->puerto = config::get('DB_PORT', '3306');
        $this->nombreBBDD = config::get('DB_NAME', 'hotel');

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
            return new \PDO("mysql:host={$this->servidor};port={$this->puerto};dbname={$this->nombreBBDD};charset=utf8mb4", $this->usuario, $this->pwd, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        } catch (\PDOException $e) {
            // Registramos el error en el log del servidor sin mostrarlo al usuario
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Método que obtiene del .env el usuario y la contraseña de la base de
     * datos para un rol (DB_USER_<ROL> y DB_PASSWORD_<ROL>)
     * 
     * @param string $rol cadena de texto con el rol cuya conexión buscamos
     * @return array devuelve un array con el usuario y la contraseña
     * @throws PDOException
     */
    function leer_configuracion($rol) {

        if (!in_array($rol, self::ROLES, true)) {
            throw new \PDOException("No existe configuración para el rol: " . $rol);
        }

        $sufijo = strtoupper($rol);

        return [
            config::requerida('DB_USER_' . $sufijo),
            config::get('DB_PASSWORD_' . $sufijo, '')
        ];
    }

    /**
     * Función que busca un usuario en la base de datos y nos retorna
     * su hash y rol en caso de existir
     * 
     * @param string $email cadena de texto el correo del usuario
     * @return mixed devuelve un array con el hash, el rol, el id y el nombre
     * del usuario o false en caso de que el usuario no exista
     */
    function buscarUsuario($email) {
        $stmt = "";
        $resultado = false;

        $sql = "SELECT password, nombre_rol, usuarios.id, nombre FROM usuarios
                 inner join roles
                  on usuarios.rol_usuario = roles.id
                   where email = ?;";

        $db = $this->PDO;

        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $email, \PDO::PARAM_STR);

            $stmt->execute(); // Ejecutamos la setencia preparada

            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {
                $resultado = array($row['password'], $row['nombre_rol'], (int) $row['id'], $row['nombre']);
            }
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);


        return $resultado;
    }

    /**
     * Función que cuenta los intentos de login fallidos recientes de un
     * email y de una IP
     * 
     * @param string $email cadena de texto con el email usado
     * @param string $ip cadena de texto con la IP del cliente
     * @param Number $minutos ventana de tiempo en minutos
     * @return array devuelve ['email' => n, 'ip' => n]
     */
    function contarIntentosLogin($email, $ip, $minutos) {

        $sql = "SELECT
                    COALESCE(SUM(email = ?), 0) AS por_email,
                    COALESCE(SUM(ip = ?), 0) AS por_ip
                  FROM intentos_login
                 WHERE fecha > NOW() - INTERVAL ? MINUTE
                   AND (email = ? OR ip = ?);";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([$email, $ip, (int) $minutos, $email, $ip]);
        $fila = $stmt->fetch(\PDO::FETCH_ASSOC);

        return ['email' => (int) $fila['por_email'], 'ip' => (int) $fila['por_ip']];
    }

    /**
     * Función que registra un intento de login fallido y borra los
     * registros de más de un día
     * 
     * @param string $email cadena de texto con el email usado
     * @param string $ip cadena de texto con la IP del cliente
     */
    function registrarIntentoFallido($email, $ip) {

        $this->PDO->exec("DELETE FROM intentos_login WHERE fecha < NOW() - INTERVAL 1 DAY;");

        $stmt = $this->PDO->prepare("INSERT INTO intentos_login (email, ip) VALUES (?, ?);");
        $stmt->execute([mb_substr($email, 0, 255), $ip]);
    }

    /**
     * Función que borra los intentos fallidos de un email tras un login correcto
     * 
     * @param string $email cadena de texto con el email
     */
    function borrarIntentosLogin($email) {

        $stmt = $this->PDO->prepare("DELETE FROM intentos_login WHERE email = ?;");
        $stmt->execute([$email]);
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
     * @param string $rol_usuario nombre del rol del usuario
     * @return Number devuelve 1 si se registró el usuario
     */
    function registrarUsuario($nombre, $email, $telf, $pais, $provincia, $ciudad, $direccion, $password, $rol_usuario = 'estandar') {

        $resultado = 0;

        $sql = "INSERT usuarios 
                 (nombre, email, telf, pais, provincia, ciudad, direccion, password, rol_usuario)
                    SELECT ?, ?, ?, ?, ?, ?, ?, ?, id FROM roles WHERE nombre_rol = ?;";


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
            $stmt->bindValue(9, $rol_usuario, \PDO::PARAM_STR);


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

        $sql = "SELECT * FROM habitacion_tipo";

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
     * Función que obtiene únicamente los nombres de los tipos de habitación
     * 
     * @return array devuelve un array de cadenas con los tipos de habitación
     */
    function obtenerNombresTiposHabitacion() {
        return array_column($this->obtenerTiposHabitacion(), 'tipo_habitacion');
    }

    /**
     * Función que obtiene el listado de todas las habitaciones
     * 
     * @return array devuelve un array asociativo con los datos de cada habitación
     */
    function obtenerListaHabitaciones() {

        $sql = "SELECT h.id, t.tipo_habitacion, h.m2, h.ventana, h.internet,
                       h.servicio_limpieza, h.precio
                  FROM habitaciones h
                 INNER JOIN habitacion_tipo t ON t.id = h.id_tipo
                 ORDER BY h.id;";

        return $this->PDO->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Función que borra una habitación
     * 
     * @param Number $id_habitacion número (id) de la habitación
     * @return Number devuelve 1 si se borró la habitación y 0 en caso contrario
     */
    function borrarHabitacion($id_habitacion) {

        $sql = "DELETE FROM habitaciones WHERE id = ?;";

        try {
            $stmt = $this->PDO->prepare($sql);
            $stmt->bindValue(1, $id_habitacion, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            // Por ejemplo, si la habitación tiene reservas asociadas
            error_log('Error al borrar la habitación: ' . $e->getMessage());
            return 0;
        }
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
