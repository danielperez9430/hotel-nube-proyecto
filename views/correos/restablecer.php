<?php
/**
 * Correo para restablecer la contraseña. Variables: $sitio, $nombre, $enlace.
 * Estilos en línea y tablas, que es lo que soportan los clientes de correo
 */
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reset your password</title></head>
<body style="margin:0;padding:0;background-color:#f5efe0;font-family:Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f5efe0;padding:32px 12px;">
        <tr><td align="center">
            <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;width:100%;background-color:#ffffff;border-top:4px solid #DCB004;">
                <tr><td align="center" style="background-color:#1F2929;padding:24px;">
                    <img src="<?= $e($sitio) ?>/src/logo/hotel_nube.png" width="160" alt="Hotel Nube" style="display:block;border:0;">
                </td></tr>
                <tr><td style="padding:32px 32px 8px;color:#1F2929;font-size:22px;">Hi <?= $e($nombre) ?>,</td></tr>
                <tr><td style="padding:8px 32px;color:#444444;font-size:16px;line-height:24px;">
                    We received a request to reset the password of your Hotel Nube account.
                    Press the button below to choose a new one. The link is valid for <strong>one hour</strong> and can only be used once.
                </td></tr>
                <tr><td align="center" style="padding:24px 32px;">
                    <a href="<?= $e($enlace) ?>" style="display:inline-block;background-color:#1F2929;color:#DCB004;border:1px solid #DCB004;padding:14px 28px;text-decoration:none;letter-spacing:2px;text-transform:uppercase;font-size:14px;">Reset password</a>
                </td></tr>
                <tr><td style="padding:0 32px 24px;color:#777777;font-size:13px;line-height:20px;">
                    If the button does not work, copy this address into your browser:<br>
                    <span style="color:#1F2929;word-break:break-all;"><?= $e($enlace) ?></span>
                </td></tr>
                <tr><td style="padding:0 32px 32px;color:#777777;font-size:13px;line-height:20px;">
                    If you did not ask to reset your password you can ignore this email: your password will not change.
                </td></tr>
                <tr><td align="center" style="background-color:#1F2929;color:#C9A44C;padding:16px;font-size:12px;">
                    &copy; Hotel Nube 2021&ndash;<?= date('Y') ?>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
