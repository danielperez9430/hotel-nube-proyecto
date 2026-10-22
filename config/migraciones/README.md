# Migraciones de la base de datos

Cada cambio de esquema posterior a `db_hotel.sql` va en un fichero
`NNN_descripcion.sql` (número de tres cifras, en orden). Se aplican con:

```bash
php scripts/migrar.php
```

- Necesita `DB_USER_MIGRACIONES` y `DB_PASSWORD_MIGRACIONES` (por defecto `root`),
  porque crea tablas y concede permisos a los usuarios `hotel_*`.
- Las aplicadas quedan en la tabla `migraciones` y no se repiten.
- Una migración ya publicada **no se modifica**: los cambios van en una nueva.
- Sin `DELIMITER`: cada sentencia termina en `;` (no hay triggers en las migraciones).
- En Docker se aplican solas al arrancar el contenedor de la web.
