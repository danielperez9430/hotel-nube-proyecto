<?php

namespace Tests\Unit;

use Http\cabeceras;
use PHPUnit\Framework\TestCase;

class CabecerasTest extends TestCase {

    protected function tearDown(): void {
        putenv('API_URL');
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
}
