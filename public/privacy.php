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
        <p>Plans change, and we understand. Check-in is from 3 pm and check-out until 12 noon (hotel local time); the deadlines below are counted from 3 pm on the check-in date.</p>

        <table class="table policy-table">
            <thead>
                <tr>
                    <th scope="col">When you cancel</th>
                    <th scope="col">What we charge</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>More than 72 hours before check-in</td>
                    <td><strong>Nothing</strong>: free cancellation</td>
                </tr>
                <tr>
                    <td>Between 72 and 24 hours before check-in</td>
                    <td>50% of the first night</td>
                </tr>
                <tr>
                    <td>Less than 24 hours before check-in, or no-show</td>
                    <td>The first night in full</td>
                </tr>
            </tbody>
        </table>

        <ul class="policy-list">
            <li><strong>Changes of date</strong> are free up to 72 hours before check-in, subject to availability.</li>
            <li><strong>Early departures</strong>: we refund the unused nights, except the night following your departure.</li>
            <li><strong>Group bookings</strong> of three or more rooms can be cancelled free of charge up to 14 days before check-in.</li>
            <li><strong>Unforeseen circumstances</strong> (illness, transport strikes, severe weather): we offer a credit for the full amount, valid for 12 months.</li>
        </ul>

        <p class="mb-0">You can cancel a booking yourself until check-in time from <strong>My bookings</strong> in the user menu: you will see the exact charge, if any, before confirming. To modify a booking, contact us through the <a href="contact.php">contact page</a> indicating your booking number, which you will find in your confirmation email.</p>
    </section>
</main>

<?php
require_once(__DIR__ . '/../views/footer.php');
