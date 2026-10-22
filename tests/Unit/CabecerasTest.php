<?php

namespace Tests\Unit;

use Http\cabeceras;
use PHPUnit\Framework\TestCase;

class CabecerasTest extends TestCase {

    protected function tearDown(): void {
        putenv('API_URL');
        putenv('TRUST_PROXY');
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    public function testLaPoliticaSoloPermiteLaPropiaWebYLaApi(): void {
        putenv('API_URL=https://api.hotel.test:8443/');
        $politica = cabeceras::politicaContenido();

        $this->assertStringContainsString("script-src 'self';", $politica);
        $this->assertStringContainsString("connect-src 'self' https://api.hotel.test:8443;", $politica);
        $this->assertStringContainsString("frame-ancestors 'none'", $politica);
        $this->assertStringContainsString("object-src 'none'", $politica);
        // Ningún script en línea ni eval
        $this->assertStringNotContainsString("'unsafe-eval'", $politica);
        $this->assertDoesNotMatchRegularExpression("/script-src[^;]*'unsafe-inline'/", $politica);
    }

    public function testSinApiValidaSoloSePermiteLaPropiaWeb(): void {
        putenv('API_URL=no es una url');

        $this->assertStringContainsString("connect-src 'self';", cabeceras::politicaContenido());
    }

    public function testDetectaHttps(): void {
        $this->assertFalse(cabeceras::esHttps());

        $_SERVER['HTTPS'] = 'off';
        $this->assertFalse(cabeceras::esHttps());

        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue(cabeceras::esHttps());
    }

    public function testSoloSeFiaDeXForwardedProtoDetrasDeUnProxy(): void {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertFalse(cabeceras::esHttps(), 'Sin TRUST_PROXY cualquiera podría enviar la cabecera');

        putenv('TRUST_PROXY=true');
        $this->assertTrue(cabeceras::esHttps());

        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
        $this->assertFalse(cabeceras::esHttps());
    }
}
