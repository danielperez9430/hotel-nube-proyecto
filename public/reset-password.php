<?php

require_once(__DIR__ . '/../autoload.php');

use \Session\session as s;
use \DB\operacionesDB as db;

s::comprobar_sesion();

// El token llega en el enlace del correo; solo comprobamos si sigue siendo válido
$token = isset($_GET['token']) ? (string) $_GET['token'] : '';
$tokenValido = false;

if (preg_match('/^[0-9a-f]{64}$/', $token)) {
    try {
        $tokenValido = (new db('conexion'))->tokenRestablecimientoValido($token);
    } catch (\PDOException $e) {
        error_log('Error al comprobar el token de restablecimiento: ' . $e->getMessage());
    }
}

// El enlace no debe filtrarse a otros sitios en la cabecera Referer
header('Referrer-Policy: no-referrer');

require_once(__DIR__ . '/../views/header.php');
?>

<main class="page">
    <h1 class="page-title">Reset your password</h1>

    <div class="page-card reset-card">
        <?php if ($tokenValido): ?>
            <p class="page-lead">Choose a new password for your account (at least 8 characters).</p>
            <form id="resetForm" novalidate>
                <div id="resetMessage" class="alert" style="display: none;"></div>
                <input type="hidden" id="resetToken" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label for="resetPassword" class="form-label">New password</label>
                    <input type="password" class="form-control" id="resetPassword" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="mb-3">
                    <label for="resetPasswordRepeat" class="form-label">Repeat the new password</label>
                    <input type="password" class="form-control" id="resetPasswordRepeat" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn btn-primary" id="resetSubmit"><i class="fas fa-key"></i> Change password</button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">This link is not valid or has expired. Links can only be used once and are valid for one hour.</div>
            <a class="btn btn-primary" href="index.php?forgot=1"><i class="fas fa-redo"></i> Request a new link</a>
        <?php endif; ?>
    </div>
</main>

<script src="src/js/resetPassword.js"></script>

<?php
require_once(__DIR__ . '/../views/footer.php');
