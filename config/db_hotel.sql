-- ---------------------------------------------------------------
-- Hotel Nube: esquema y datos de ejemplo
-- Compatible con MariaDB 10.x y MySQL 8.
-- Los usuarios de base de datos se crean aparte con db_usuarios.sql
-- ---------------------------------------------------------------

-- El script está en UTF-8: sin esto, el cliente puede leerlo como latin1
-- y guardar mal los acentos y las eñes
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS hotel;

CREATE DATABASE hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE hotel;

-- Roles de la aplicación
CREATE TABLE roles (
    id BIGINT NOT NULL AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

-- Usuarios registrados
CREATE TABLE usuarios (
    id BIGINT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    telf VARCHAR(13) NOT NULL,
    pais VARCHAR(255) NOT NULL,
    provincia VARCHAR(255) NOT NULL,
    ciudad VARCHAR(255) NOT NULL,
    direccion VARCHAR(60) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_usuario BIGINT NOT NULL,
    ultimo_acceso DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (rol_usuario) REFERENCES roles(id)
);

-- Tipos de habitación del hotel
CREATE TABLE habitacion_tipo (
    id BIGINT NOT NULL AUTO_INCREMENT,
    tipo_habitacion VARCHAR(255) NOT NULL UNIQUE,
    descripcion VARCHAR(500) NOT NULL,
    PRIMARY KEY (id)
);

-- Imágenes de cada tipo de habitación (un tipo puede tener varias)
CREATE TABLE imagenes_habitaciones (
    id BIGINT NOT NULL AUTO_INCREMENT,
    id_habitacion_tipo BIGINT NOT NULL,
    imagen_habitacion VARCHAR(255) NOT NULL,
    descripcion_imagen VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_habitacion_tipo) REFERENCES habitacion_tipo(id)
);

-- Habitaciones
CREATE TABLE habitaciones (
    id BIGINT NOT NULL AUTO_INCREMENT,
    m2 DECIMAL(6,2) NOT NULL,
    ventana BOOLEAN NOT NULL DEFAULT FALSE,
    id_tipo BIGINT NOT NULL,
    servicio_limpieza BOOLEAN NOT NULL DEFAULT FALSE,
    internet BOOLEAN NOT NULL DEFAULT FALSE,
    precio DECIMAL(6,2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_tipo) REFERENCES habitacion_tipo(id)
);

-- Servicios extra
CREATE TABLE servicios (
    id BIGINT NOT NULL AUTO_INCREMENT,
    nombre_servicio VARCHAR(255) NOT NULL,
    precio_servicio DECIMAL(6,2) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    disponibilidad BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (id)
);

-- Relación entre las habitaciones y los servicios que ofrecen
CREATE TABLE habitacion_servicio (
    id_habitacion BIGINT NOT NULL,
    id_servicio BIGINT NOT NULL,
    fecha_servicio DATETIME NOT NULL,
    fecha_fin_servicio DATETIME NOT NULL,
    PRIMARY KEY (id_habitacion, id_servicio),
    FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id)
);

-- Reservas: una estancia de un usuario entre dos fechas
CREATE TABLE reservas (
    num_reserva BIGINT NOT NULL AUTO_INCREMENT,
    id_usuario BIGINT NOT NULL,
    fecha_reserva TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    PRIMARY KEY (num_reserva),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    CHECK (fecha_salida > fecha_entrada)
);

-- Habitaciones de cada reserva, con el precio por noche en el momento de reservar
CREATE TABLE habitaciones_reservas (
    id BIGINT NOT NULL AUTO_INCREMENT,
    num_reserva BIGINT NOT NULL,
    id_habitacion BIGINT NOT NULL,
    precio_noche DECIMAL(6,2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (num_reserva) REFERENCES reservas(num_reserva),
    FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id)
);

-- Intentos de login fallidos, para limitar los ataques de fuerza bruta
CREATE TABLE intentos_login (
    id BIGINT NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_intentos_email (email, fecha),
    INDEX idx_intentos_ip (ip, fecha)
);

-- Trigger: actualiza la fecha de última modificación de los datos de un usuario
DELIMITER $$
DROP TRIGGER IF EXISTS trigger_check_update_user$$
CREATE TRIGGER trigger_check_update_user
BEFORE UPDATE
ON usuarios FOR EACH ROW
BEGIN
  SET NEW.ultima_modificacion = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- Datos: roles
INSERT INTO roles (nombre_rol) VALUES ('admin'), ('conexion'), ('estandar');

-- Datos: tipos de habitación
INSERT INTO habitacion_tipo (tipo_habitacion, descripcion) VALUES
    ('Deluxe Room', 'The room has a large comfortable double sized bed that easily sleeps 2. '),
    ('Double Room', 'Double room with private bathroom, shower and living room. The room has a wardrobe and a comfortable bed. It is a beautiful, sunny, quiet and comfortable room.'),
    ('Rustic Room', 'This rustic style hotel room offers fantastic accommodations to meet your needs in an even better location. Furnished with Old Hickory and Knotty Pine you\'ll feel like you\'re part of the Old West.'),
    ('Single Room', 'Simple Room, it is an ideal room to be relaxed thanks to the fact that it includes access to a small garden with a deck chair.\n\nThe design of this room can be said to be modern and it is a very bright room.'),
    ('Turquoise Room', 'The Turquoise Room takes you on a trip by the sea due to its blue tones of the decoration. In this room you will never lack fresh fruit on the table.\n\nWe hope you have the best possible stay in this exclusive room.');

-- Datos: habitaciones (id_tipo: 1 = Deluxe Room, 2 = Double Room, 3 = Rustic Room, 4 = Single Room, 5 = Turquoise Room)
INSERT INTO habitaciones (m2, ventana, id_tipo, servicio_limpieza, internet, precio) VALUES
    (100, 1, 1, 1, 1, 300),
    (30, 1, 2, 1, 0, 150),
    (25, 1, 3, 1, 0, 100),
    (35, 0, 4, 0, 0, 50),
    (40, 1, 5, 1, 1, 200),
    (31, 1, 2, 0, 1, 150),
    (34, 0, 3, 0, 1, 200),
    (56, 1, 1, 1, 1, 150),
    (34, 1, 2, 1, 0, 78),
    (67, 1, 5, 1, 0, 150),
    (36, 0, 2, 0, 1, 267),
    (29, 0, 2, 0, 1, 134),
    (78, 1, 3, 1, 1, 165),
    (45, 1, 4, 1, 0, 123),
    (67, 1, 3, 1, 1, 145),
    (78, 1, 4, 0, 0, 78),
    (78, 0, 5, 1, 1, 156),
    (56, 0, 3, 0, 1, 194),
    (19, 1, 4, 1, 1, 48),
    (89, 1, 1, 1, 0, 167),
    (43, 1, 4, 1, 1, 145),
    (23, 1, 2, 0, 1, 178),
    (60, 0, 5, 0, 0, 165),
    (56, 0, 3, 1, 0, 143),
    (48, 1, 4, 1, 1, 132),
    (20, 1, 3, 1, 1, 156),
    (200, 1, 1, 1, 0, 145),
    (150, 1, 5, 0, 1, 650),
    (34, 0, 2, 0, 1, 304),
    (43, 1, 3, 1, 1, 234);

-- Datos: imágenes de cada tipo de habitación
INSERT INTO imagenes_habitaciones (id_habitacion_tipo, imagen_habitacion, descripcion_imagen) VALUES
    (1, '/src/img/deluxe_room/cama_frente_hab_deluxe.jpg', 'Vista frontal cama habitación turquesa'),
    (1, '/src/img/deluxe_room/cama_lateral_hab_deluxe.jpg', 'Vista lateral cama habitación turquesa'),
    (1, '/src/img/deluxe_room/comedor_hab_deluxe.jpg', 'Comedor habitación turquesa'),
    (1, '/src/img/deluxe_room/salon_hab_deluxe.jpg', 'Salon habitación tuquesa'),
    (2, '/src/img/double_room/bano_hab_doble.jpg', 'Baño habitación doble'),
    (2, '/src/img/double_room/cama_hab_doble.jpg', 'Cama doble moderna'),
    (2, '/src/img/double_room/mesa_exterior_hab_doble.jpg', 'Mesita exterior habitación doble con jardín'),
    (2, '/src/img/double_room/salon_hab_doble.jpg', 'Salón con mucha luz y tranquilidad'),
    (3, '/src/img/rustic_room/cama_hab_rustica.jpg', 'Cama habitación rustica'),
    (3, '/src/img/rustic_room/chimenea_hab_rustica.jpg', 'Chimenea de la habitación rustica'),
    (3, '/src/img/rustic_room/mesa-salon_hab_rustica.jpg', 'Mesa salón habitación rustica'),
    (3, '/src/img/rustic_room/sofa_hab_rustica.jpg', 'Sofá con una amplia ventana'),
    (4, '/src/img/single_room/bano_hab_simple.jpg', 'Salón con sofá color crema'),
    (4, '/src/img/single_room/cama_hab_simple.jpg', 'Cama moderna con colores claros'),
    (4, '/src/img/single_room/hamaca_exterior_hab_simple.jpg', 'Hamaca exterior doble'),
    (4, '/src/img/single_room/salon_hab_simple.jpg', 'Salón moderno con mesita para el café'),
    (5, '/src/img/turquoise_room/cama_cerca_hab_turquesa.jpg', 'Cama de la habitación turquesa con frutas'),
    (5, '/src/img/turquoise_room/cama_hab_turquesa.jpg', 'Vista lateral cama de la habitación turquesa'),
    (5, '/src/img/turquoise_room/comedor_hab_turquesa.jpg', 'Gran comedor con colores turquesa'),
    (5, '/src/img/turquoise_room/salon_hab_turquesa.jpg', 'Salón comodo y muy luminoso'),
    (5, '/src/img/turquoise_room/sofa_hab_turquesa.jpg', 'Vista frontal salón habitación turquesa');
