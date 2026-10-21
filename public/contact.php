<?php

require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;
s::comprobar_sesion();

require_once(__DIR__ . '/../views/header.php');

require_once(__DIR__ . '/../views/footer.php');
