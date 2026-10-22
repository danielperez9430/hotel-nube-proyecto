<?php

namespace Tests\Unit;

use Config\config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {

    private string $fichero;

    protected function setUp(): void {
        $this->fichero = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($this->fichero, <<<ENV
            # Comentario
            SIN_COMILLAS=valor
            COMILLAS_DOBLES="con espacios"
            COMILLAS_SIMPLES='simple'
              CON_ESPACIOS  =  recortado  
            VACIA=
            CON_IGUAL=a=b
            LINEA SIN IGUAL
            ENV);
        config::usarFichero($this->fichero);
    }

    protected function tearDown(): void {
        unlink($this->fichero);
        putenv('HN_TEST_ENTORNO');
        config::usarFichero(__DIR__ . '/../no-existe.env');
    }

    public function testLeeValoresDelFichero(): void {
        $this->assertSame('valor', config::get('SIN_COMILLAS'));
        $this->assertSame('con espacios', config::get('COMILLAS_DOBLES'));
        $this->assertSame('simple', config::get('COMILLAS_SIMPLES'));
        $this->assertSame('recortado', config::get('CON_ESPACIOS'));
        $this->assertSame('', config::get('VACIA'));
        $this->assertSame('a=b', config::get('CON_IGUAL'));
    }

    public function testDevuelveElValorPorDefecto(): void {
        $this->assertNull(config::get('NO_EXISTE'));
        $this->assertSame('defecto', config::get('NO_EXISTE', 'defecto'));
    }

    public function testLasVariablesDeEntornoTienenPrioridad(): void {
        file_put_contents($this->fichero, "HN_TEST_ENTORNO=fichero\n");
        config::usarFichero($this->fichero);
        putenv('HN_TEST_ENTORNO=entorno');

        $this->assertSame('entorno', config::get('HN_TEST_ENTORNO'));
    }

    public function testRequeridaLanzaUnErrorSiFaltaOEstaVacia(): void {
        $this->assertSame('valor', config::requerida('SIN_COMILLAS'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('VACIA');
        config::requerida('VACIA');
    }

    public function testSinFicheroNoFalla(): void {
        config::usarFichero('/no/existe/.env');
        $this->assertSame('x', config::get('SIN_COMILLAS', 'x'));
    }
}
