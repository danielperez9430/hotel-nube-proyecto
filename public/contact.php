<?php

require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;
use \Config\config as config;

s::comprobar_sesion();

// Dirección de contacto: la misma que envía los correos del hotel
$correoContacto = config::get('MAIL_FROM_ADDRESS', '') ?: config::get('MAIL_USERNAME', '');

require_once(__DIR__ . '/../views/header.php');
?>

<main class="page">
    <h1 class="page-title">Contact</h1>
    <p class="page-lead">We will be delighted to help you plan your stay.</p>

    <div class="page-card">
        <h2><i class="fas fa-concierge-bell"></i> Reservations &amp; guest services</h2>
        <?php if ($correoContacto !== ''): ?>
            <p>Write to us at <a href="mailto:<?= htmlspecialchars($correoContacto) ?>"><?= htmlspecialchars($correoContacto) ?></a> and our team will reply as soon as possible.</p>
        <?php endif; ?>
        <p>You can also book a room directly: choose your dates on the <a href="index.php">home page</a> and press <strong>Search</strong>.</p>
        <p class="mb-0">Registered guests can check their bookings at any time from the user menu, under <strong>My bookings</strong>.</p>
    </div>
</main>

<?php
require_once(__DIR__ . '/../views/footer.php');
