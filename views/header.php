<?php
// Usuario con sesión iniciada (null si es un visitante)
$usuario = \Session\session::usuario();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="<?= htmlspecialchars(\Session\session::tokenCsrf()) ?>">
        <title>Hotel Nube</title>
        
<link rel="icon" href="favicon.svg" type="image/svg+xml">
        <link rel="icon" href="favicon.ico" sizes="any">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <meta name="theme-color" content="#1F2929">
        <!-- Librerías (copias locales en src/vendor) -->
        <!-- Bootstrap 5 -->
        <link href="src/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="src/css/style.css" rel="stylesheet" />
        <link href="src/css/roomSelection.css" rel="stylesheet" />
        <script src="src/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        
        <!-- Fonts Awesome -->
        <link rel="stylesheet" href="src/vendor/fontawesome/css/all.min.css"/>
        
        <!-- jQuery -->
        <script src="src/vendor/jquery/jquery.min.js"></script>

        <!-- Litepicker -->
        <link rel="stylesheet" href="src/vendor/litepicker/litepicker.css"/>
        <script src="src/vendor/litepicker/litepicker.js"></script>

        <!-- URL de hotelnube-api, configurada con API_URL en el .env -->
        <script>
            window.API_URL = <?= json_encode(rtrim(\Config\config::get('API_URL', 'http://localhost:8001'), '/'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?>;
        </script>
        <script src="src/js/csrf.js"></script>
        <script src="src/js/login.js"></script>
        <script src="src/js/logout.js"></script>
        <script src="src/js/roomSelection.js"></script>
        <script src="src/js/datepickers.js"></script>
    </head>

    <body>

        <!-- Barra de navegación -->
        <nav class="navbar navbar-expand-lg navbar-light bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="src/logo/hotel_nube.svg" height="40" /></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav align-items-center">
                        <a class="nav-link active text-light" aria-current="page" href="#">Home</a>
                        <a class="nav-link text-light" href="#">Promotions</a>
                        <a class="nav-link text-light" href="#">Services</a>
                        <a class="nav-link text-light" href="#">Contact</a>

                        <?php if ($usuario === null): ?>
                            <div class="navbar-nav login">
                                <a class="nav-link text-light me-auto" data-bs-toggle="modal" data-bs-target="#loginDialog">Login/Register</a>
                            </div>
                        <?php else: ?>
                            <div class="userButtonDiv">
                                <button id="userButton"><i class="fas fa-user"></i><span id="userName"><?= htmlspecialchars(explode(' ', trim($usuario['nombre']))[0]) ?></span></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <?php if ($usuario !== null): ?>
            <div id="userLoggedMenuBase" style="display: none;"></div>
            <div id="userLoggedMenu" style="display: none;">
                <span id="fullUserName"><?= htmlspecialchars($usuario['nombre']) ?></span>
                <span id="userEmail"><?= htmlspecialchars($usuario['email']) ?></span>
                <button id="myBookings" class="btn btn-outline-dark mb-2"><i class="fas fa-suitcase"></i>My bookings</button>
                <?php if ($usuario['rol'] === 'admin'): ?>
                    <button id="adminPanel" class="btn btn-outline-dark mb-2"><i class="fas fa-tools"></i>Admin panel</button>
                <?php endif; ?>
                <button id="logOut" class="btn btn-primary"><i class="fas fa-door-open"></i>Log out</button>
            </div>

            <!-- Reservas del usuario -->
            <div class="modal fade" id="myBookingsDialog" tabindex="-1" aria-labelledby="myBookingsTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myBookingsTitle">My bookings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="myBookingsList"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

