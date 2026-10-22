<?php

require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;
use \DB\operacionesDB as db;

s::comprobar_sesion();

$servicios = (new db('estandar'))->obtenerServicios();

require_once(__DIR__ . '/../views/header.php');
?>

<main class="page page-wide">
    <h1 class="page-title">Services</h1>
    <p class="page-lead">Every detail of your stay, taken care of.</p>

    <div class="services-grid">
        <?php foreach ($servicios as $servicio): ?>
            <section class="page-card service-card">
                <i class="<?= htmlspecialchars($servicio['icono']) ?> service-icon" aria-hidden="true"></i>
                <h2><?= htmlspecialchars($servicio['nombre_servicio']) ?></h2>
                <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
                <span class="service-price">
                    <?= (float) $servicio['precio_servicio'] > 0
                        ? htmlspecialchars(number_format((float) $servicio['precio_servicio'], 0, '.', ',')) . '€'
                        : 'Complimentary' ?>
                </span>
            </section>
        <?php endforeach; ?>
    </div>

    <p class="page-note">
        <i class="fas fa-info-circle"></i>
        Any service can be requested at reception or by writing to us from the <a href="contact.php">contact page</a>.
    </p>
</main>

<?php
require_once(__DIR__ . '/../views/footer.php');
