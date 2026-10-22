<?php

/*
 * Aplica las migraciones pendientes de config/migraciones a la base de datos.
 *
 *   php scripts/migrar.php
 *
 * Necesita un usuario con permisos para cambiar el esquema y conceder
 * permisos (DB_USER_MIGRACIONES y DB_PASSWORD_MIGRACIONES; por defecto root).
 * Los usuarios de la aplicación no tienen esos permisos a propósito.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../autoload.php';

use \Config\config as config;
use \DB\migraciones;

try {
    $pdo = new \PDO(
        'mysql:host=' . config::get('DB_HOST', 'localhost') . ';port=' . config::get('DB_PORT', '3306')
            . ';dbname=' . config::get('DB_NAME', 'hotel') . ';charset=utf8mb4',
        config::get('DB_USER_MIGRACIONES', 'root'),
        config::get('DB_PASSWORD_MIGRACIONES', '')
    );

    $migraciones = new migraciones($pdo, __DIR__ . '/../config/migraciones');
    $aplicadas = $migraciones->aplicar(function ($nombre) {
        echo "  ✔ $nombre\n";
    });

    echo $aplicadas ? count($aplicadas) . " migración(es) aplicada(s)\n" : "La base de datos está al día\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al aplicar las migraciones: " . $e->getMessage() . "\n");
    exit(1);
}
