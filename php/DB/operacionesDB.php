<?php

namespace DB;

require_once __DIR__ . '/../../autoload.php';

use \Habitacion\habitacion as habitacion;
use \Habitacion\servicios as servicios;

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
     * @return mixed devuelve un array asociativo con el id, nombre, email,
     * hash y el rol del usuario o un false en caso de que el usuario no exista
     */
    function buscarUsuario($email) {
        $stmt = "";
        $resultado = false;

        $sql = "SELECT usuarios.id, usuarios.nombre, usuarios.email, usuarios.password, roles.nombre_rol
                 FROM hotel.usuarios
                  inner join roles
                   on usuarios.rol_usuario = roles.id
                    where email = ?;";

        $db = $this->PDO;

        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $email, \PDO::PARAM_STR);

            $stmt->execute(); // Ejecutamos la setencia preparada

            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $resultado = $row;
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
     * 
     * @param type $fecha_entrada
     * @param type $fecha_salida
     */
    function buscarHabitaciones($fecha_entrada, $fecha_salida) {
        
    }

    /**
     * Función que obtiene todas las imagenes de una habitación en concreto
     * 
     * @param Number $tipo_habitacion cadena de texto con el tipo de la habitacion
     * @return Array devuelve un array con los datos de las imagenes
     */
    function buscarImagenesHabitacion($tipo_habitacion) {

        $imagenes = array();

        $sql = "SELECT *
                 FROM imagenes_habitaciones
                  inner join habitacion_tipo
                    on imagenes_habitaciones.id_habitacion_tipo = habitacion_tipo.id
	             where habitacion_tipo.tipo_habitacion = ?;";

        $db = $this->PDO; // Puntero de conexión a la base de datos


        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $tipo_habitacion, \PDO::PARAM_STR);

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

    /**
     * Función que obtiene el listado de todas las habitaciones y sus servicios
     * 
     * @return array con los datos de todas las habitaciones
     */
    function obtenerListaHabitaciones() {

        $habitaciones = array();
        $sql = "select * from habitaciones";

        $db = $this->PDO;

        if (($stmt = $db->query($sql))) {

            // Bucle para obtener todos los datos de las habitaciones
            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {

                $imagenes = $this->buscarImagenesHabitacion($row['tipo_de_habitacion']);
                $servicios = $this->buscarServiciosHabitacion($row['id']);
                $descripcion = $this->obtenerDescripcion($row['tipo_de_habitacion']);
                
                $habitacion = new habitacion($row['id'], $row['m2'], $row['ventana'],
                        $row['tipo_de_habitacion'],$descripcion,  $row['servicio_limpieza'], $row['internet'],
                        $row['precio'], $servicios, $imagenes);

                array_push($habitaciones, $habitacion);
            }
        } else {
            echo "Se a producido un error: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $habitaciones;
    }

    /**
     * Función que obtiene todos los servicios de una habitación asociada
     * 
     * @param Number $id número (id) de la habitación
     * @return Array devuelve un array con objectos servicio o un array vacio
     * si no hay servicios asociados
     */
    function buscarServiciosHabitacion($id) {

        $servicios = array();

        $sql = "select servicios.id, servicios.nombre_servicio, servicios.precio_servicio,
                 servicios.descripcion, servicios.disponibilidad
                    from servicios
                      inner join habitacion_servicio
                        on servicios.id  = habitacion_servicio.id_servicio
                         where habitacion_servicio.id_habitacion = ?;";

        $db = $this->PDO; // Puntero de conexión a la base de datos


        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $id, \PDO::PARAM_INT);

            $stmt->execute(); // Ejecutamos la setencia preparada

            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {
                // Cargamos los datos de los servicios
                $servicio = new servicios($row['id'], $row['nombre_servicio'],
                        $row['precio_servicio'], $row['descripcion'], $row['disponibilidad']);

                array_push($servicios, $servicio);
            }
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $servicios;
    }

    /**
     * Función para borrar una habitación
     * 
     * @param Number $id número con el id de la habitación
     * @return number devuelve 0 si no se borre nada o 1 si se borro la habitación
     * @throws \Exception
     */
    function borrarHabitacion($id) {

        $db = $this->PDO;

        $sql = "delete 
                 from habitaciones 
                   where id = ?;";

        $borrados = 0;

        try {
            // Iniciamos la transacción,
            $db->beginTransaction();

            // Ejecutamos la operación tantas veces como familiares tenga

            $stmt = $db->prepare($sql);

            $stmt->bindValue(1, $id);

            $stmt->execute();
            
            $borrados = $stmt->rowCount();

            //Si fue todo correcto hacemos commit
            $db->commit();
        } catch (\Exception $e) {
            $borrados = 0;
            $db->rollback();
            throw $e;
        }

        /*
         * Realizamos el unset de $stmt para resetear el puntero de la consulta PDO,
         * esto nos conviene siempre liberar los recursos no utilizados
         */
        unset($stmt);

        return $borrados;
    }

    /**
     * Función que actualiza el campo de último accedo del usuario
     * 
     * @param number $id_usuario id del usuario
     * @return number devuelve 0 si no se actualizo nada o 1 en caso contrario
     */
    function actualizarUltimoAcceso($id_usuario) {

        $result = 0;

        $sql = "UPDATE usuarios
                 SET ultimo_acceso = now()
                    WHERE (id = '?');";

        $db = $this->PDO; // Puntero de conexión a la base de datos


        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $id_usuario, \PDO::PARAM_INT);

            $result = $stmt->execute(); // Ejecutamos la setencia preparada
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $result;
    }

    /**
     * Función que obtiene todas las imagenes de una habitación en concreto
     * 
     * @param Number $id_habitacion_tipo número (id) del tipo de habitación
     * @return Array devuelve un array con los datos de las imagenes
     */
    function filtrarHabitaciones($fecha_entrada, $fecha_salida, $tipo_de_habitacion = null) {

        $habitaciones = array();

        $sql = "select * 
                 from habitaciones
                    where id not in (
                       select hr.id_habitacion
                         from reservas as v, habitaciones_reservas as hr
                           where v.num_reserva like hr.num_reserva
                            and ? >= v.fecha_entrada
                              and ? <= v.fecha_salida
                                and ? >= v.fecha_entrada
                        ) 
                          and habitaciones.disponibilidad = 1";

        if ($tipo_de_habitacion != null) {
            $sql .= "and habitaciones.tipo_de_habitacion = ?;";
        }

        $db = $this->PDO; // Puntero de conexión a la base de datos


        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $fecha_entrada, \PDO::PARAM_STR);
            $stmt->bindValue(2, $fecha_entrada, \PDO::PARAM_STR);
            $stmt->bindValue(3, $fecha_salida, \PDO::PARAM_STR);

            if ($tipo_de_habitacion != null) {
                $stmt->bindValue(4, $tipo_de_habitacion, \PDO::PARAM_STR);
            }
            $stmt->execute(); // Ejecutamos la setencia preparada

            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {

                $imagenes = $this->buscarImagenesHabitacion($row['tipo_de_habitacion']);
                $servicios = array();
                $descripcion = $this->obtenerDescripcion($row['tipo_de_habitacion']);

                $habitacion = new habitacion($row['id'], $row['m2'], $row['ventana'],
                        $row['tipo_de_habitacion'], $descripcion, $row['servicio_limpieza'], $row['internet'],
                        $row['precio'], $servicios, $imagenes);

                array_push($habitaciones, $habitacion);
            }
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $habitaciones;
    }

    /**
     * Función que obtiene la descripción de un tipo de habitación
     * 
     * @param string $tipo_de_habitacion cade
     * @return string devuelve 0 si no se actualizo nada o 1 en caso contrario
     */
    function obtenerDescripcion($tipo_de_habitacion) {

        $descripcion = "";

        $sql = "select * 
                  from habitacion_tipo
                    where tipo_habitacion = ?;";

        $db = $this->PDO; // Puntero de conexión a la base de datos


        if (($stmt = $db->prepare($sql))) { // Creamos y validamos la sentencia preparada
            $stmt->bindValue(1, $tipo_de_habitacion, \PDO::PARAM_STR);

            $stmt->execute(); // Ejecutamos la setencia preparada

            if (($row = $stmt->fetch(\PDO::FETCH_BOTH))) {

                $descripcion = $row['descripcion'];
            }
        } else {
            echo "ERROR: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $descripcion;
    }
    
    function obtenerTiposHabitaciones() {
        
        $tipos_habitaciones = array();
        
        $sql = "select tipo_habitacion
                  from habitacion_tipo;";
        
                $db = $this->PDO; // Puntero de conexión a la base de datos


        if (($stmt = $db->query($sql))) {

            // Bucle para obtener todos los tipos de habitaciones
            while ($row = $stmt->fetch(\PDO::FETCH_BOTH)) {

                array_push($tipos_habitaciones, $row['tipo_habitacion']);
               
            }
            
        } else {
            echo "Se a producido un error: " . print_r($db->errorInfo());
        }

        unset($stmt);

        return $tipos_habitaciones;
                
    }

}
