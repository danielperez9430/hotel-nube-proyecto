-- Cancelación de reservas por el cliente según la política de cancelación.
-- Las reservas canceladas se conservan (con la fecha y el cargo aplicado)
-- pero dejan libre la habitación
ALTER TABLE reservas
    ADD COLUMN estado ENUM('confirmada', 'cancelada') NOT NULL DEFAULT 'confirmada',
    ADD COLUMN fecha_cancelacion DATETIME NULL,
    ADD COLUMN cargo_cancelacion DECIMAL(8,2) NULL;

-- El cliente solo puede cambiar el estado de sus reservas, nunca las fechas
GRANT UPDATE (estado, fecha_cancelacion, cargo_cancelacion) ON hotel.reservas TO 'hotel_estandar'@'%';
