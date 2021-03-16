<?php

// Autocarga empleando función anónima
spl_autoload_register(function($nombre_clase) {

    $file = __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR. str_replace('\\', DIRECTORY_SEPARATOR, $nombre_clase) . ".php";

    if ($file != "") {
        // Comprobamos si el fichero existe
        if (file_exists($file)) {
            // Si existe incluimos el fichero
            include $file;
        }
    }
});
