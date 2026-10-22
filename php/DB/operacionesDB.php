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
     * @throws \PDOException
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
            throw new \PDOException('Error al preparar la consulta: ' . implode(' ', $db->errorInfo()));
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
     * Función que crea un token para restablecer la contraseña de un usuario.
     * Invalida los tokens anteriores y limita las solicitudes por hora
     * 
     * @param string $email cadena de texto con el email del usuario
     * @param Number $maxPorHora número máximo de solicitudes por hora
     * @return array|null devuelve ['token' => token en claro, 'nombre' => nombre]
     * o null si el usuario no existe o ha superado el límite
     */
    function crearRestablecimiento($email, $maxPorHora = 3) {

        $stmt = $this->PDO->prepare("SELECT id, nombre FROM usuarios WHERE email = ?;");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($usuario === false) {
            return null;
        }

        $stmt = $this->PDO->prepare("SELECT COUNT(*) FROM restablecimientos_password
                                      WHERE id_usuario = ? AND creado > NOW() - INTERVAL 1 HOUR;");
        $stmt->execute([$usuario['id']]);

        if ((int) $stmt->fetchColumn() >= $maxPorHora) {
            return null;
        }

        // Solo vale el último enlace enviado
        $this->PDO->prepare("UPDATE restablecimientos_password SET usado = TRUE WHERE id_usuario = ? AND usado = FALSE;")
                ->execute([$usuario['id']]);

        $token = bin2hex(random_bytes(32));

        $this->PDO->prepare("INSERT INTO restablecimientos_password (id_usuario, token_hash, expira)
                             VALUES (?, ?, NOW() + INTERVAL 1 HOUR);")
                ->execute([$usuario['id'], hash('sha256', $token)]);

        return ['token' => $token, 'nombre' => $usuario['nombre']];
    }

    /**
     * Función que indica si un token de restablecimiento es válido (existe,
     * no se ha usado y no ha caducado)
     * 
     * @param string $token cadena de texto con el token en claro
     * @return boolean devuelve true si el token es válido
     */
    function tokenRestablecimientoValido($token) {

        $stmt = $this->PDO->prepare("SELECT COUNT(*) FROM restablecimientos_password
                                      WHERE token_hash = ? AND usado = FALSE AND expira > NOW();");
        $stmt->execute([hash('sha256', (string) $token)]);

        return (int) $stmt->fetchColumn() === 1;
    }

    /**
     * Función que cambia la contraseña con un token de restablecimiento
     * válido. Invalida todos los tokens del usuario y borra sus intentos de
     * login fallidos
     * 
     * @param string $token cadena de texto con el token en claro
     * @param string $hash hash de la nueva contraseña
     * @return boolean devuelve true si se cambió la contraseña
     */
    function restablecerPassword($token, $hash) {

        $db = $this->PDO;
        $db->beginTransaction();

        try {
            // FOR UPDATE: dos peticiones con el mismo token no pueden usarlo a
            // la vez. Solo se bloquea la fila del token: en MySQL 8 bloquear
            // usuarios exigiría permiso UPDATE sobre toda la tabla
            $stmt = $db->prepare("SELECT id_usuario FROM restablecimientos_password
                                   WHERE token_hash = ? AND usado = FALSE AND expira > NOW()
                                     FOR UPDATE;");
            $stmt->execute([hash('sha256', (string) $token)]);
            $idUsuario = $stmt->fetchColumn();

            if ($idUsuario === false) {
                $db->rollBack();
                return false;
            }

            $stmt = $db->prepare("SELECT id AS id_usuario, email FROM usuarios WHERE id = ?;");
            $stmt->execute([$idUsuario]);
            $fila = $stmt->fetch(\PDO::FETCH_ASSOC);

            $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?;")->execute([$hash, $fila['id_usuario']]);
            $db->prepare("UPDATE restablecimientos_password SET usado = TRUE WHERE id_usuario = ?;")->execute([$fila['id_usuario']]);
            $db->prepare("DELETE FROM intentos_login WHERE email = ?;")->execute([$fila['email']]);

            $db->commit();
            return true;
        } catch (\PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Función que obtiene los datos de la cuenta de un usuario (sin la
     * contraseña)
     * 
     * @param Number $id número del id del usuario
     * @return array|false devuelve un array asociativo con los datos o false
     * si el usuario no existe
     */
    function obtenerUsuario($id) {

        $stmt = $this->PDO->prepare("SELECT id, nombre, email, telf, pais, provincia, ciudad, direccion
                                       FROM usuarios WHERE id = ?;");
        $stmt->execute([$id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Función que actualiza los datos de un usuario. El email no se cambia:
     * es el identificador del login
     * 
     * @param Number $id número del id del usuario
     * @param string $nombre cadena de texto con el nombre del usuario
     * @param string $telf cadena de texto con número de telf del usuario
     * @param string $pais cadena de texto con el pais del usuario
     * @param string $provincia cadena de texto con la provincia
     * @param string $ciudad cadena de texto con la ciudad
     * @param string $direccion cadena de texto con la dirección
     * @return boolean devuelve true si el usuario existe (aunque los datos
     * no cambien)
     */
    function actualizarDatosUsuario($id, $nombre, $telf, $pais, $provincia, $ciudad, $direccion) {

        $sql = "UPDATE usuarios
                   SET nombre = ?, telf = ?, pais = ?, provincia = ?, ciudad = ?, direccion = ?
                 WHERE id = ?;";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([$nombre, $telf, $pais, $provincia, $ciudad, $direccion, $id]);

        // rowCount es 0 si los datos no cambian: comprobamos que el usuario exista
        return $stmt->rowCount() === 1 || $this->obtenerUsuario($id) !== false;
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
     * @return Number devuelve el id del nuevo usuario o 0 si no se registró
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

            // Obtenemos el id del usuario insertado
            $resultado = $stmt->rowCount() === 1 ? (int) $db->lastInsertId() : 0;
        } else {
            throw new \PDOException('Error al preparar la consulta: ' . implode(' ', $db->errorInfo()));
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
            throw new \PDOException('Error al preparar la consulta: ' . implode(' ', $db->errorInfo()));
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
     * Función que actualiza los datos de una habitación
     * 
     * @param Number $id_habitacion número (id) de la habitación
     * @param string $tipo nombre del tipo de habitación
     * @param float $m2 superficie en metros cuadrados
     * @param boolean $ventana si tiene ventana
     * @param boolean $internet si tiene internet
     * @param boolean $limpieza si tiene servicio de limpieza
     * @param float $precio precio por noche
     * @return boolean devuelve true si la habitación existe y el tipo es válido
     */
    function actualizarHabitacion($id_habitacion, $tipo, $m2, $ventana, $internet, $limpieza, $precio) {

        // El tipo se busca por nombre: si no existe no se actualiza nada
        $sql = "UPDATE habitaciones h
                 INNER JOIN habitacion_tipo t ON t.tipo_habitacion = ?
                   SET h.id_tipo = t.id, h.m2 = ?, h.ventana = ?, h.internet = ?,
                       h.servicio_limpieza = ?, h.precio = ?
                 WHERE h.id = ?;";

        $stmt = $this->PDO->prepare($sql);
        $stmt->bindValue(1, $tipo, \PDO::PARAM_STR);
        $stmt->bindValue(2, $m2);
        $stmt->bindValue(3, $ventana, \PDO::PARAM_BOOL);
        $stmt->bindValue(4, $internet, \PDO::PARAM_BOOL);
        $stmt->bindValue(5, $limpieza, \PDO::PARAM_BOOL);
        $stmt->bindValue(6, $precio);
        $stmt->bindValue(7, $id_habitacion, \PDO::PARAM_INT);
        $stmt->execute();

        // rowCount es 0 si los datos no cambian, así que comprobamos que la
        // habitación y el tipo existan
        $comprobacion = $this->PDO->prepare("SELECT COUNT(*) FROM habitaciones h
                 INNER JOIN habitacion_tipo t ON t.id = h.id_tipo
                 WHERE h.id = ? AND t.tipo_habitacion = ?;");
        $comprobacion->execute([$id_habitacion, $tipo]);

        return (int) $comprobacion->fetchColumn() === 1;
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
     * Función que busca las habitaciones libres entre dos fechas
     * 
     * @param string $fecha_entrada fecha de entrada (Y-m-d)
     * @param string $fecha_salida fecha de salida (Y-m-d)
     * @return array devuelve un array con los datos de cada habitación libre,
     * su tipo y la primera imagen del tipo, ordenado por precio
     */
    function buscarHabitaciones($fecha_entrada, $fecha_salida) {

        $sql = "SELECT h.id, t.tipo_habitacion, t.descripcion, h.m2, h.ventana,
                       h.servicio_limpieza, h.internet, h.precio,
                       (SELECT i.imagen_habitacion FROM imagenes_habitaciones i
                         WHERE i.id_habitacion_tipo = t.id ORDER BY i.id LIMIT 1) AS imagen,
                       (SELECT i.descripcion_imagen FROM imagenes_habitaciones i
                         WHERE i.id_habitacion_tipo = t.id ORDER BY i.id LIMIT 1) AS descripcion_imagen
                  FROM habitaciones h
                 INNER JOIN habitacion_tipo t ON t.id = h.id_tipo
                 WHERE NOT EXISTS (" . self::SQL_OCUPADA . ")
                 ORDER BY h.precio, h.id;";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([$fecha_salida, $fecha_entrada]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Subconsulta que indica si la habitación h tiene alguna reserva que se
    // solapa con la estancia [entrada, salida). Parámetros: salida, entrada
    const SQL_OCUPADA = "SELECT 1 FROM habitaciones_reservas hr
                          INNER JOIN reservas r ON r.num_reserva = hr.num_reserva
                          WHERE hr.id_habitacion = h.id
                            AND r.fecha_entrada < ? AND r.fecha_salida > ?";

    /**
     * Función que reserva una habitación si sigue libre. Usa un bloqueo con
     * nombre por habitación para que dos reservas simultáneas de la misma
     * habitación no puedan solaparse
     * 
     * @param Number $id_usuario id del usuario que reserva
     * @param Number $id_habitacion id de la habitación
     * @param string $fecha_entrada fecha de entrada (Y-m-d)
     * @param string $fecha_salida fecha de salida (Y-m-d)
     * @return array|false devuelve num_reserva, tipo_habitacion, imagen y
     * precio_noche, o false si la habitación no existe o está ocupada
     */
    function crearReserva($id_usuario, $id_habitacion, $fecha_entrada, $fecha_salida) {

        $db = $this->PDO;
        $bloqueo = 'hotel_reserva_' . (int) $id_habitacion;

        $stmt = $db->prepare("SELECT GET_LOCK(?, 5);");
        $stmt->execute([$bloqueo]);

        if ((int) $stmt->fetchColumn() !== 1) {
            return false;
        }

        try {
            $stmt = $db->prepare("SELECT h.precio, t.tipo_habitacion,
                       (SELECT i.imagen_habitacion FROM imagenes_habitaciones i
                         WHERE i.id_habitacion_tipo = t.id ORDER BY i.id LIMIT 1) AS imagen
                  FROM habitaciones h
                 INNER JOIN habitacion_tipo t ON t.id = h.id_tipo
                 WHERE h.id = ? AND NOT EXISTS (" . self::SQL_OCUPADA . ");");
            $stmt->execute([$id_habitacion, $fecha_salida, $fecha_entrada]);
            $habitacion = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($habitacion === false) {
                return false;
            }

            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO reservas (id_usuario, fecha_entrada, fecha_salida) VALUES (?, ?, ?);");
            $stmt->execute([$id_usuario, $fecha_entrada, $fecha_salida]);
            $num_reserva = (int) $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO habitaciones_reservas (num_reserva, id_habitacion, precio_noche) VALUES (?, ?, ?);");
            $stmt->execute([$num_reserva, $id_habitacion, $habitacion['precio']]);

            $db->commit();

            return [
                'num_reserva' => $num_reserva,
                'tipo_habitacion' => $habitacion['tipo_habitacion'],
                'imagen' => $habitacion['imagen'],
                'precio_noche' => (float) $habitacion['precio']
            ];
        } catch (\PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        } finally {
            $db->prepare("SELECT RELEASE_LOCK(?);")->execute([$bloqueo]);
        }
    }

    /**
     * Función que obtiene las reservas de un usuario, de la más reciente a
     * la más antigua
     * 
     * @param Number $id_usuario id del usuario
     * @return array devuelve un array con los datos de cada reserva
     */
    function obtenerReservasUsuario($id_usuario) {

        $sql = "SELECT r.num_reserva, r.fecha_reserva, r.fecha_entrada, r.fecha_salida,
                       t.tipo_habitacion, hr.id_habitacion, hr.precio_noche,
                       hr.precio_noche * DATEDIFF(r.fecha_salida, r.fecha_entrada) AS total
                  FROM reservas r
                 INNER JOIN habitaciones_reservas hr ON hr.num_reserva = r.num_reserva
                 INNER JOIN habitaciones h ON h.id = hr.id_habitacion
                 INNER JOIN habitacion_tipo t ON t.id = h.id_tipo
                 WHERE r.id_usuario = ?
                 ORDER BY r.fecha_entrada DESC, r.num_reserva DESC;";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([$id_usuario]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Función que obtiene los servicios disponibles del hotel, de más
     * baratos a más caros
     * 
     * @return array devuelve un array con id, nombre_servicio,
     * precio_servicio, descripcion e icono de cada servicio
     */
    function obtenerServicios() {

        $sql = "SELECT id, nombre_servicio, precio_servicio, descripcion, icono
                  FROM servicios
                 WHERE disponibilidad = TRUE
                 ORDER BY precio_servicio, nombre_servicio;";

        return $this->PDO->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
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
            throw new \PDOException('Error al preparar la consulta: ' . implode(' ', $db->errorInfo()));
        }

        unset($stmt);

        return $imagenes;
    }

}
