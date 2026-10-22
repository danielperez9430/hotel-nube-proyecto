#!/bin/bash
# Crea los usuarios de base de datos de Hotel Nube con las contraseñas del
# .env. Lo ejecuta la imagen de MariaDB al inicializar la base de datos,
# después de 01-esquema.sql
set -euo pipefail

# Escapa un valor para usarlo dentro de una cadena SQL entre comillas simples.
# Los textos de sustitución van entre comillas: si no, Bash 5.2 sustituye
# los & por el patrón encontrado y procesa las barras invertidas
escapar() {
    local valor=$1
    valor=${valor//"\\"/"\\\\"}
    valor=${valor//"'"/"''"}
    printf '%s' "$valor"
}

sql=$(cat /hotel/db_usuarios.sql)
sql=${sql//CAMBIAR_CLAVE_ADMIN/"$(escapar "$DB_PASSWORD_ADMIN")"}
sql=${sql//CAMBIAR_CLAVE_CONEXION/"$(escapar "$DB_PASSWORD_CONEXION")"}
sql=${sql//CAMBIAR_CLAVE_ESTANDAR/"$(escapar "$DB_PASSWORD_ESTANDAR")"}

mariadb --protocol=socket -uroot -p"$MARIADB_ROOT_PASSWORD" <<< "$sql"
