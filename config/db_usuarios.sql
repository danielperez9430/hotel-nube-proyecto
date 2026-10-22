-- ---------------------------------------------------------------
-- Hotel Nube: usuarios de base de datos con permisos mínimos
--
-- 1. Sustituye las tres contraseñas CAMBIAR_* por unas seguras.
-- 2. Ejecuta el script como root DESPUÉS de db_hotel.sql:
--      mariadb -u root -p < config/db_usuarios.sql
-- 3. Pon los mismos usuarios y contraseñas en el .env.
--
-- Con Docker Compose no hace falta: los usuarios se crean solos con
-- las contraseñas del .env.
-- ---------------------------------------------------------------

CREATE USER IF NOT EXISTS 'hotel_admin'@'%' IDENTIFIED BY 'CAMBIAR_CLAVE_ADMIN';
CREATE USER IF NOT EXISTS 'hotel_conexion'@'%' IDENTIFIED BY 'CAMBIAR_CLAVE_CONEXION';
CREATE USER IF NOT EXISTS 'hotel_estandar'@'%' IDENTIFIED BY 'CAMBIAR_CLAVE_ESTANDAR';

-- Administración: gestiona los datos de la base de datos hotel (no su estructura)
GRANT SELECT, INSERT, UPDATE, DELETE ON hotel.* TO 'hotel_admin'@'%';

-- Login: lee usuarios y roles y registra los intentos fallidos
GRANT SELECT ON hotel.usuarios TO 'hotel_conexion'@'%';
GRANT SELECT ON hotel.roles TO 'hotel_conexion'@'%';
GRANT SELECT, INSERT, DELETE ON hotel.intentos_login TO 'hotel_conexion'@'%';

-- Visitantes y clientes: consultan el catálogo, se registran, reservan
-- y editan los datos de su cuenta
GRANT SELECT ON hotel.roles TO 'hotel_estandar'@'%';
GRANT SELECT ON hotel.habitacion_tipo TO 'hotel_estandar'@'%';
GRANT SELECT ON hotel.habitaciones TO 'hotel_estandar'@'%';
GRANT SELECT ON hotel.imagenes_habitaciones TO 'hotel_estandar'@'%';
GRANT SELECT ON hotel.servicios TO 'hotel_estandar'@'%';
GRANT SELECT, INSERT ON hotel.reservas TO 'hotel_estandar'@'%';
GRANT SELECT, INSERT ON hotel.habitaciones_reservas TO 'hotel_estandar'@'%';
GRANT INSERT ON hotel.usuarios TO 'hotel_estandar'@'%';
-- Ajustes de cuenta: lee sus datos (nunca la contraseña) y edita solo
-- nombre, teléfono y dirección
GRANT SELECT (id, nombre, email, telf, pais, provincia, ciudad, direccion) ON hotel.usuarios TO 'hotel_estandar'@'%';
GRANT UPDATE (nombre, telf, pais, provincia, ciudad, direccion) ON hotel.usuarios TO 'hotel_estandar'@'%';

FLUSH PRIVILEGES;
