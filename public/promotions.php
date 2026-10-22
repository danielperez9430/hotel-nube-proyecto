<?php

require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;

s::comprobar_sesion();

// Promociones vigentes: ventajas que el hotel aplica al llegar, sobre las
// reservas hechas en la web (el precio de la reserva no cambia)
$promociones = [
    [
        'icono' => 'fas fa-moon',
        'titulo' => 'Long Stay',
        'etiqueta' => '5 nights or more',
        'texto' => 'Stay five nights or more and enjoy a complimentary Spa & Wellness Circuit for two and a late check-out on your last day.'
    ],
    [
        'icono' => 'fas fa-heart',
        'titulo' => 'Romantic Escape',
        'etiqueta' => 'Deluxe & Turquoise rooms',
        'texto' => 'Book a Deluxe or Turquoise room and find a bottle of cava, fresh fruit and rose petals waiting for you on arrival.'
    ],
    [
        'icono' => 'fas fa-briefcase',
        'titulo' => 'Midweek Retreat',
        'etiqueta' => 'Sunday to Thursday',
        'texto' => 'Arrive between Sunday and Thursday and get breakfast in bed on your first morning, on the house.'
    ],
    [
        'icono' => 'fas fa-gift',
        'titulo' => 'Welcome Back',
        'etiqueta' => 'Returning guests',
        'texto' => 'Already stayed with us? Your next booking includes a welcome gift and a room upgrade whenever one is available.'
    ],
];

require_once(__DIR__ . '/../views/header.php');
?>

<main class="page page-wide">
    <h1 class="page-title">Promotions</h1>
    <p class="page-lead">A little extra for every kind of stay.</p>

    <div class="services-grid">
        <?php foreach ($promociones as $promocion): ?>
            <section class="page-card service-card">
                <i class="<?= $promocion['icono'] ?> service-icon" aria-hidden="true"></i>
                <span class="promo-tag"><?= $promocion['etiqueta'] ?></span>
                <h2><?= $promocion['titulo'] ?></h2>
                <p><?= $promocion['texto'] ?></p>
            </section>
        <?php endforeach; ?>
    </div>

    <div class="page-cta">
        <a href="index.php" class="btn btn-dark btn-lg"><i class="fas fa-search"></i> Find your room</a>
    </div>

    <p class="page-note">
        <i class="fas fa-info-circle"></i>
        Benefits are applied at check-in to bookings made on this website, subject to availability, and cannot be exchanged for money.
        Only one promotion applies per stay.
    </p>
</main>

<?php
require_once(__DIR__ . '/../views/footer.php');
