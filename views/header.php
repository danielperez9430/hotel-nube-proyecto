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
        
        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
              integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="src/css/style.css" rel="stylesheet" />
        <link href="src/css/roomSelection.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
                integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
        
        <!-- Fonts Awesome -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
        
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"
                integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>

        <!-- Litepicker -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker@2.0.12/dist/css/litepicker.css"
              integrity="sha384-fcpDiVI0TDx8mYG1bVKKGTM+E7cSE50LVyMaaBkegJuyXG1DOhC/DNsShtuPlo1L" crossorigin="anonymous"/>
        <script src="https://cdn.jsdelivr.net/npm/litepicker@2.0.12/dist/litepicker.js"
                integrity="sha384-TwHJTusQtbcoEHJcrF5aeo0MHBgj8lt8onFRrG7wKk8Q/LG9EphkEsqNRiVWzIP9" crossorigin="anonymous"></script>

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

