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
}
