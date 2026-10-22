<?php

namespace Tests\Unit;

use Email\email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase {

    protected function tearDown(): void {
        putenv('MAIL_HOST');
    }

    public function testSinServidorSmtpNoSeEnvianCorreos(): void {
        putenv('MAIL_HOST=');

        $this->assertFalse(email::estaConfigurado());
        // No intenta conectar con ningún servidor y no lanza excepciones
        $this->assertFalse(email::enviarBienvenida('ana@test.com', 'Ana'));
    }

    public function testConServidorSmtpEstaConfigurado(): void {
        putenv('MAIL_HOST=smtp.example.com');

        $this->assertTrue(email::estaConfigurado());
    }

    public function testLaPlantillaDeRestablecimientoMuestraElNombreYElEnlaceEscapados(): void {
        putenv('SITE_URL=https://hotel.example');

        $html = email::plantilla('restablecer', ['nombre' => 'Ana <b>', 'enlace' => 'https://hotel.example/reset-password.php?token=abc&x=1']);

        putenv('SITE_URL');

        $this->assertStringContainsString('Hi Ana &lt;b&gt;,', $html);
        $this->assertStringContainsString('href="https://hotel.example/reset-password.php?token=abc&amp;x=1"', $html);
        $this->assertStringContainsString('src="https://hotel.example/src/logo/hotel_nube.png"', $html);
    }
}
