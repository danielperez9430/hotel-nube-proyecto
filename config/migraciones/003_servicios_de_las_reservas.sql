-- Servicios extra contratados con cada reserva. El precio se copia al
-- reservar, para que cambiarlo después no altere las reservas ya hechas
CREATE TABLE reservas_servicios (
    id BIGINT NOT NULL AUTO_INCREMENT,
    num_reserva BIGINT NOT NULL,
    id_servicio BIGINT NOT NULL,
    precio DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY reserva_servicio (num_reserva, id_servicio),
    FOREIGN KEY (num_reserva) REFERENCES reservas(num_reserva) ON DELETE CASCADE,
    FOREIGN KEY (id_servicio) REFERENCES servicios(id)
);

-- habitacion_servicio relacionaba servicios con habitaciones (no con
-- reservas) y nunca se ha usado: la sustituye reservas_servicios
DROP TABLE IF EXISTS habitacion_servicio;

GRANT SELECT, INSERT ON hotel.reservas_servicios TO 'hotel_estandar'@'%';
