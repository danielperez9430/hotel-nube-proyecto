<?php

require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;

s::comprobar_sesion();

require_once(__DIR__ . '/../views/header.php');
?>

<main class="page">
    <h1 class="page-title">Privacy, cookies &amp; cancellation</h1>
    <p class="page-lead">How Hotel Nube looks after your data and your bookings.</p>

    <section class="page-card" id="privacy">
        <h2><i class="fas fa-user-shield"></i> Privacy policy</h2>
        <p>When you register we store your name, email, phone number, address and a securely hashed version of your password. When you book, we store the room, the dates and the price of your stay.</p>
        <p>We use this information only to manage your account and your bookings and to send you the related emails (welcome message and booking confirmations). We never sell or share it with third parties for marketing.</p>
        <p class="mb-0">You can ask us at any time to access, correct or delete your data through the <a href="contact.php">contact page</a>.</p>
    </section>

    <section class="page-card" id="cookies">
        <h2><i class="fas fa-cookie-bite"></i> Cookie policy</h2>
        <p>Hotel Nube only uses one cookie, <code>PHPSESSID</code>, which is strictly necessary to keep your session open and to protect the forms. It is deleted when you log out or close the browser.</p>
        <p class="mb-0">We do not use advertising or analytics cookies, and every resource of the website (styles, scripts and fonts) is served from our own server, so no third party receives your visit. If you start a search before logging in, the selected dates are kept in your browser's session storage until you log in.</p>
    </section>

    <section class="page-card" id="cancellation">
        <h2><i class="fas fa-calendar-times"></i> Cancellation policy</h2>
        <p>Bookings can be cancelled free of charge up to 48 hours before the check-in date. To cancel or modify a booking, contact us indicating your booking number.</p>
        <p class="mb-0">Cancellations made less than 48 hours before check-in, or no-shows, may be charged the first night of the stay.</p>
    </section>
</main>

<?php
require_once(__DIR__ . '/../views/footer.php');
