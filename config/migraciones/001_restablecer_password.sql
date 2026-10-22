-- Recuperación de contraseña: tokens de un solo uso con caducidad.
-- Solo se guarda el hash SHA-256 del token; el token en claro solo viaja
-- en el enlace del correo
CREATE TABLE restablecimientos_password (
    id BIGINT NOT NULL AUTO_INCREMENT,
    id_usuario BIGINT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expira DATETIME NOT NULL,
    usado BOOLEAN NOT NULL DEFAULT FALSE,
    creado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_restablecimientos_token (token_hash),
    INDEX idx_restablecimientos_usuario (id_usuario, creado),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- El proceso de login (hotel_conexion) gestiona los tokens y solo puede
-- cambiar la columna password de usuarios
GRANT SELECT, INSERT, UPDATE ON hotel.restablecimientos_password TO 'hotel_conexion'@'%';
GRANT UPDATE (password) ON hotel.usuarios TO 'hotel_conexion'@'%';
