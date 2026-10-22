<?php

// Autocarga de Composer (PHPUnit, PHPMailer) y de las clases de php/
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../autoload.php';

// Los tests no leen el .env del proyecto: usan variables de entorno o un
// fichero propio, para no depender de la configuración local
\Config\config::usarFichero(__DIR__ . '/no-existe.env');
