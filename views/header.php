<?php
// Usuario con sesión iniciada (null si es un visitante)
$usuario = \Session\session::usuario();

// Página actual, para marcar su enlace en la barra de navegación
$paginaActual = basename($_SERVER['SCRIPT_NAME']);
$enlaceNav = function ($pagina, $texto) use ($paginaActual) {
    $activa = $pagina === $paginaActual;
    return '<a class="nav-link' . ($activa ? ' active" aria-current="page' : '') . '" href="' . $pagina . '">' . $texto . '</a>';
};
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

        <!-- URL de hotelnube-api (API_URL en el .env); la lee src/js/config.js -->
        <meta name="api-url" content="<?= htmlspecialchars(rtrim(\Config\config::get('API_URL', 'http://localhost:8001'), '/')) ?>">
        <script src="src/js/config.js"></script>
        <script src="src/js/csrf.js"></script>
        <script src="src/js/login.js"></script>
        <script src="src/js/logout.js"></script>
        <script src="src/js/roomSelection.js"></script>
        <script src="src/js/userAccountSettings.js"></script>
        <script src="src/js/datepickers.js"></script>
    </head>

    <body>

        <!-- Barra de navegación -->
        <nav class="navbar navbar-expand-lg navbar-dark hn-navbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php"><img src="src/logo/hotel_nube_sin_fondo.svg" height="40" alt="Hotel Nube" /></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav me-auto">
                        <?= $enlaceNav('index.php', 'Home') ?>
                        <?= $enlaceNav('promotions.php', 'Promotions') ?>
                        <?= $enlaceNav('services.php', 'Services') ?>
                        <?= $enlaceNav('contact.php', 'Contact') ?>
                    </div>

                    <?php if ($usuario === null): ?>
                        <button type="button" class="hn-nav-button" id="navLogin" data-bs-toggle="modal" data-bs-target="#loginDialog">
                            <i class="far fa-user"></i>Login / Register
                        </button>
                    <?php else: ?>
                        <button type="button" class="hn-nav-button" id="userButton" aria-haspopup="true">
                            <i class="fas fa-user"></i><span id="userName"><?= htmlspecialchars(explode(' ', trim($usuario['nombre']))[0]) ?></span><i class="fas fa-chevron-down hn-caret"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <?php if ($usuario !== null): ?>
            <div id="userLoggedMenuBase" style="display: none;"></div>
            <div id="userLoggedMenu" style="display: none;">
                <span id="fullUserName"><?= htmlspecialchars($usuario['nombre']) ?></span>
                <span id="userEmail"><?= htmlspecialchars($usuario['email']) ?></span>
                <button id="myBookings" class="hn-menu-item"><i class="fas fa-suitcase"></i>My bookings</button>
                <button id="accountSettings" class="hn-menu-item"><i class="fas fa-user-cog"></i>Account settings</button>
                <?php if ($usuario['rol'] === 'admin'): ?>
                    <button id="adminPanel" class="hn-menu-item"><i class="fas fa-tools"></i>Admin panel</button>
                <?php endif; ?>
                <button id="logOut" class="hn-menu-item hn-menu-logout"><i class="fas fa-door-open"></i>Log out</button>
            </div>

            <!-- Ajustes de la cuenta -->
            <div class="modal fade" id="accountDialog" tabindex="-1" aria-labelledby="accountTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="accountTitle">Account settings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="modal-body" id="accountForm" novalidate>
                            <div id="accountMessage" class="alert" style="display: none;"></div>
                            <div class="mb-3">
                                <label for="accountEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="accountEmail" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="accountName" class="form-label">Name</label>
                                <input type="text" class="form-control" id="accountName" name="nombre" maxlength="255" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="accountPhoneNumber">Phone number</label>
                                <div class="input-group">
                                    <input type="text" class="form-control account-phone-code" id="accountPhoneCode" placeholder="+34" maxlength="4" aria-label="Country code">
                                    <input type="text" class="form-control" id="accountPhoneNumber" placeholder="612345678" maxlength="9">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="accountAddress" class="form-label">Address</label>
                                <input type="text" class="form-control" id="accountAddress" name="direccion" maxlength="60" required>
                            </div>
                            <div class="mb-3">
                                <label for="accountCountry" class="form-label">Country</label>
                                <select class="form-select" id="accountCountry"></select>
                            </div>
                            <div class="mb-3">
                                <label for="accountProvince" class="form-label">Province</label>
                                <select class="form-select" id="accountProvince"></select>
                            </div>
                            <div class="mb-3">
                                <label for="accountCity" class="form-label">City</label>
                                <select class="form-select" id="accountCity"></select>
                            </div>
                        </form>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" form="accountForm" id="accountSave"><i class="fas fa-save"></i> Save changes</button>
                        </div>
                    </div>
                </div>
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

