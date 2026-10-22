<?php
/**
 * Correo de reserva cancelada. Variables: $sitio, $nombre, $reserva,
 * $entrada, $salida (Y-m-d) y $cargo (euros).
 * Estilos en línea y tablas, que es lo que soportan los clientes de correo
 */
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES);
$fecha = fn($texto) => (new \DateTimeImmutable($texto))->format('j M Y');
$euros = fn($valor) => number_format((float) $valor, 2, '.', ',') . ' €';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Booking cancelled</title></head>
<body style="margin:0;padding:0;background-color:#f5efe0;font-family:Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f5efe0;padding:32px 12px;">
        <tr><td align="center">
            <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;width:100%;background-color:#ffffff;border-top:4px solid #DCB004;">
                <tr><td align="center" style="background-color:#1F2929;padding:24px;">
                    <img src="<?= $e($sitio) ?>/src/logo/hotel_nube.png" width="160" alt="Hotel Nube" style="display:block;border:0;">
                </td></tr>
                <tr><td style="padding:32px 32px 8px;color:#1F2929;font-size:22px;">Hi <?= $e($nombre) ?>,</td></tr>
                <tr><td style="padding:8px 32px;color:#444444;font-size:16px;line-height:24px;">
                    Your booking <strong>#<?= $e($reserva) ?></strong> has been cancelled. We are sorry you cannot join us this time.
                </td></tr>
                <tr><td style="padding:16px 32px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e6dcc3;font-size:15px;color:#1F2929;">
                        <tr>
                            <td style="padding:12px 16px;border-bottom:1px solid #e6dcc3;">Stay</td>
                            <td align="right" style="padding:12px 16px;border-bottom:1px solid #e6dcc3;"><?= $e($fecha($entrada)) ?> &rarr; <?= $e($fecha($salida)) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:12px 16px;">Cancellation charge</td>
                            <td align="right" style="padding:12px 16px;"><strong><?= (float) $cargo > 0 ? $e($euros($cargo)) : 'Free' ?></strong></td>
                        </tr>
                    </table>
                </td></tr>
                <tr><td style="padding:8px 32px 32px;color:#777777;font-size:13px;line-height:20px;">
                    The charge follows our <a href="<?= $e($sitio) ?>/privacy.php#cancellation" style="color:#1F2929;">cancellation policy</a>.
                    We hope to welcome you at Hotel Nube soon.
                </td></tr>
                <tr><td align="center" style="background-color:#1F2929;color:#C9A44C;padding:16px;font-size:12px;">
                    &copy; Hotel Nube 2021&ndash;<?= date('Y') ?>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
