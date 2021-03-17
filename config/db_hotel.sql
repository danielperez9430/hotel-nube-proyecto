-- DB Daniel Pérez Fernández 3º Versión

drop database if exists hotel; -- Eliminamos la base de datos si existe

create database hotel; -- Creamos la base de datos

use hotel; -- Establemos nuestra db como principal

-- Creamos la tabla Roles
create table roles(
id bigint(20) not null unique auto_increment,
nombre_rol varchar(50) not null unique,
primary key(id)
);

-- Creamos la tabla Usuarios
create table usuarios (
id bigint(20) not null auto_increment,
nombre varchar(255) not null,
email varchar(50) not null unique,
telf varchar(13) not null,
pais varchar(255) not null,
provincia varchar(255) not null,
ciudad varchar(255) not null,
direccion varchar(60) not null,
password varchar(255) not null,
rol_usuario bigint(20) not null,
ultimo_acceso datetime DEFAULT CURRENT_TIMESTAMP,
ultima_modificacion datetime DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id),
foreign key (rol_usuario) references roles(id)
);


-- Creamos la tabla con los tipos de camas de nuestro hotel
CREATE TABLE habitacion_tipo (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tipo_habitacion VARCHAR(255) not null unique,
    descripcion varchar(500) not null,
    PRIMARY KEY (id)
);

-- Creamos la Tabla Imagenes_Habitaciones ya que una habitación puede tener más de una imagen
create table imagenes_habitaciones (
id bigint(20) not null auto_increment,
id_habitacion_tipo bigint(20) not null,
imagen_habitacion varchar(255) NOT NULL,
descripcion_imagen varchar(50) NOT NULL,
primary key (id),
foreign key (id_habitacion_tipo) references habitacion_tipo(id)
);

-- Creamos la Tabla  Habitaciones 
create table habitaciones(
id bigint(20) not null auto_increment,
m2 DECIMAL(6,2) not null,
ventana bit not null default 0,
tipo_de_habitacion varchar(255) not null,
servicio_limpieza bit not null default 0,
internet bit not null default 0,
precio DECIMAL(6,2) not null,
primary key (id),
foreign key (tipo_de_habitacion) references habitacion_tipo(tipo_habitacion)
);

-- Creamos la Tabla Servicios
create table servicios(
id bigint(20) not null auto_increment,
nombre_servicio varchar(255) not null,
precio_servicio DECIMAL(6,2) not null,
descripcion varchar(255) not null,
disponibilidad bit not null default 1,
primary key (id)
);

-- Creamos la Tabla Habitacion_Servicio que almacena la relación entre las habitaciones y los servicios que ofrecen
create table habitacion_servicio(
id_habitacion bigint(20) not null,
id_servicio bigint(20) not null,
fecha_servicio datetime not null,
fecha_fin_servicio datetime not null,
primary key (id_habitacion, id_servicio),
foreign key (id_habitacion) references habitaciones(id),
foreign key (id_servicio) references servicios(id)
);

-- Creamos la Tabla Reservas
create table reservas(
num_reserva bigint(20) not null auto_increment,
id_usuario bigint(20) not null,
fecha_reserva timestamp not null default current_timestamp,
num_dias smallint(20) not null,
primary key (num_reserva), 
foreign key (id_usuario) references usuarios(id)
);

-- Creamos la tabla Habitacion_Reserva
create table habitaciones_reservas(
id bigint(20) not null auto_increment,
num_reserva bigint(20) not null,
id_habitacion bigint(20) not null,
primary key (id),
foreign key (num_reserva) references  reservas(num_reserva),
foreign key (id_habitacion) references habitaciones(id)
);

-- Trigger actualización fecha de última modificación de los datos de un usuario
DELIMITER $$
DROP TRIGGER IF EXISTS trigger_check_update_user$$
CREATE TRIGGER trigger_check_update_user
BEFORE UPDATE
ON usuarios FOR EACH ROW
BEGIN
  set New.ultima_modificacion = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- Datos Roles
INSERT INTO `hotel`.`roles` (`nombre_rol`) VALUES ('admin');
INSERT INTO `hotel`.`roles` (`nombre_rol`) VALUES ('conexion');
INSERT INTO `hotel`.`roles` (`nombre_rol`) VALUES ('estandar');

-- Creacion de usuarios
-- Privilegios para `admin`@`%`
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' IDENTIFIED BY PASSWORD '*A4B6157319038724E3560894F7F932C8886EBFCF' WITH GRANT OPTION;

-- Privilegios para `conexion`@`%`
GRANT SELECT ON *.* TO 'conexion'@'%' IDENTIFIED BY PASSWORD '*A4B6157319038724E3560894F7F932C8886EBFCF';

--  Privilegios para `estandar`@`%`
GRANT SELECT, INSERT ON *.* TO 'estandar'@'%' IDENTIFIED BY PASSWORD '*A4B6157319038724E3560894F7F932C8886EBFCF';


-- Datos Habitación Tipo

INSERT INTO `hotel`.`habitacion_tipo` (`tipo_habitacion`, `descripcion`) VALUES ('Deluxe Room', 'The room has a large comfortable double sized bed that easily sleeps 2. ');
INSERT INTO `hotel`.`habitacion_tipo` (`tipo_habitacion`, `descripcion`) VALUES ('Double Room', 'Double room with private bathroom, shower and living room. The room has a wardrobe and a comfortable bed. It is a beautiful, sunny, quiet and comfortable room.');
INSERT INTO `hotel`.`habitacion_tipo` (`tipo_habitacion`, `descripcion`) VALUES ('Rustic Room', 'This rustic style hotel room offers fantastic accommodations to meet your needs in an even better location. Furnished with Old Hickory and Knotty Pine you\'ll feel like you\'re part of the Old West.');
INSERT INTO `hotel`.`habitacion_tipo` (`tipo_habitacion`, `descripcion`) VALUES ('Single Room', 'Simple Room, it is an ideal room to be relaxed thanks to the fact that it includes access to a small garden with a deck chair.\n\nThe design of this room can be said to be modern and it is a very bright room.');
INSERT INTO `hotel`.`habitacion_tipo` (`tipo_habitacion`, `descripcion`) VALUES ('Turquoise Room', 'The Turquoise Room takes you on a trip by the sea due to its blue tones of the decoration. In this room you will never lack fresh fruit on the table.\n\nWe hope you have the best possible stay in this exclusive room.');

-- Datos Habitaciones
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('100', 1, 'Deluxe Room', 1, 1, '300');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('30', 1, 'Double Room', 1, 0, '150');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('25', 1, 'Rustic Room', 1, 0, '100');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('35', 0, 'Single Room', 0, 0, '50');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('40', 1, 'Turquoise Room', 1, 1, '200');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('31', 1, 'Double Room', 0, 1, '150');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('34', 0, 'Rustic Room', 0, 1, '200');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('56', 1, 'Deluxe Room', 1, 1, '150');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('34', 1, 'Double Room', 1, 0, '78');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('67', 1, 'Turquoise Room', 1, 0, '150');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('36', 0, 'Double Room', 0, 1, '267');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('29', 0, 'Double Room', 0, 1, '134');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('78', 1, 'Rustic Room', 1, 1, '165');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('45', 1, 'Single Room', 1, 0, '123');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('67', 1, 'Rustic Room', 1, 1, '145');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('78', 1, 'Single Room', 0, 0, '78');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('78', 0, 'Turquoise Room', 1, 1, '156');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('56', 0, 'Rustic Room', 0, 1, '194');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('19', 1, 'Single Room', 1, 1, '48');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('89', 1, 'Deluxe Room', 1, 0, '167');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('43', 1, 'Single Room', 1, 1, '145');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('23', 1, 'Double Room', 0, 1, '178');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('60', 0, 'Turquoise Room', 0, 0, '165');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('56', 0, 'Rustic Room', 1, 0, '143');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('48', 1, 'Single Room', 1, 1, '132');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('20', 1, 'Rustic Room', 1, 1, '156');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('200', 1, 'Deluxe Room', 1, 0, '145');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('150', 1, 'Turquoise Room', 0, 1, '650');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('34', 0, 'Double Room', 0, 1, '304');
INSERT INTO `hotel`.`habitaciones` (`m2`, `ventana`, `tipo_de_habitacion`, `servicio_limpieza`, `internet`, `precio`) VALUES ('43', 1, 'Rustic Room', 1, 1, '234');

-- Insertamos las imagenes
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('1', '/src/img/deluxe_room/cama_frente_hab_deluxe.jpg', 'Vista frontal cama habitación turquesa');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('1', '/src/img/deluxe_room/cama_lateral_hab_deluxe.jpg', 'Vista lateral cama habitación turquesa');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('1', '/src/img/deluxe_room/comedor_hab_deluxe.jpg', 'Comedor habitación turquesa');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('1', '/src/img/deluxe_room/salon_hab_deluxe.jpg', 'Salon habitación tuquesa');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('2', '/src/img/double_room/bano_hab_doble.jpg', 'Baño habitación doble');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('2', '/src/img/double_room/cama_hab_doble.jpg', 'Cama doble moderna');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('2', '/src/img/double_room/mesa_exterior_hab_doble.jpg', 'Mesita exterior habitación doble con jardín');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('2', '/src/img/double_room/salon_hab_doble.jpg', 'Salón con mucha luz y tranquilidad');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('3', '/src/img/rustic_room/cama_hab_rustica.jpg', 'Cama habitación rustica');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('3', '/src/img/rustic_room/chimenea_hab_rustica.jpg', 'Chimenea de la habitación rustica');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('3', '/src/img/rustic_room/mesa-salon_hab_rustica.jpg', 'Mesa salón habitación rustica');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('3', '/src/img/rustic_room/sofa_hab_rustica.jpg', 'Sofá con una amplia ventana');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('4', '/src/img/single_room/baño_hab_simple.jpg', 'Salón con sofá color crema');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('4', '/src/img/single_room/cama_hab_simple.jpg', 'Cama moderna con colores claros');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('4', '/src/img/single_room/hamaca_exterior_hab_simple.jpg', 'Hamaca exterior doble');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('4', '/src/img/single_room/salon_hab_simple.jpg', 'Salón moderno con mesita para el café');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('5', '/src/img/turquoise_room/cama_cerca_hab_turquesa.jpg', 'Cama de la habitación turquesa con frutas');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('5', '/src/img/turquoise_room/cama_hab_turquesa.jpg', 'Vista lateral cama de la habitación turquesa');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('5', '/src/img/turquoise_room/comedor_hab_turquesa.jpg', 'Gran comedor con colores turquesa');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('5', '/src/img/turquoise_room/salon_hab_turquesa.jpg', 'Salón comodo y muy luminoso');
INSERT INTO `hotel`.`imagenes_habitaciones` (`id_habitacion_tipo`, `imagen_habitacion`, `descripcion_imagen`) VALUES ('5', '/src/img/turquoise_room/sofa_hab_turquesa.jpg', 'Vista frontal salón habitación turquesa');

