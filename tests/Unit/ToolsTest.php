<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tools\tools;

class ToolsTest extends TestCase {

    private tools $tools;

    protected function setUp(): void {
        $this->tools = new tools();
        putenv('TIMEZONE=Europe/Madrid');
    }

    protected function tearDown(): void {
        putenv('TIMEZONE');
    }

    private function dentroDe(int $dias, string $formato = 'd-m-Y'): string {
        $hoy = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid'));
        return $hoy->modify("$dias days")->format($formato);
    }

    public function testCifraYCompruebaContraseñas(): void {
        $hash = $this->tools->cifrarContraseña('secreta');

        $this->assertNotSame('secreta', $hash);
        $this->assertTrue($this->tools->comprobarContraseña('secreta', $hash));
        $this->assertFalse($this->tools->comprobarContraseña('otra', $hash));
    }

    public function testComprobarEmail(): void {
        $this->assertSame('ana@test.com', $this->tools->comprobarEmail('ana@test.com'));
        $this->assertFalse($this->tools->comprobarEmail('no es un email'));
    }

    /** @return array<string, array{string, ?string}> */
    public static function fechas(): array {
        return [
            'formato del selector' => ['25-12-2030', '2030-12-25'],
            'formato ISO' => ['2030-12-25', '2030-12-25'],
            'día inexistente' => ['31-02-2030', null],
            'mes inexistente' => ['01-13-2030', null],
            'sin ceros' => ['5-3-2030', null],
            'texto' => ['mañana', null],
            'vacía' => ['', null],
        ];
    }

    #[DataProvider('fechas')]
    public function testParsearFecha(string $texto, ?string $esperada): void {
        $fecha = $this->tools->parsearFecha($texto);

        $this->assertSame($esperada, $fecha?->format('Y-m-d'));
    }

    public function testEstanciaValida(): void {
        $estancia = $this->tools->validarEstancia($this->dentroDe(3), $this->dentroDe(6));

        $this->assertSame([
            'entrada' => $this->dentroDe(3, 'Y-m-d'),
            'salida' => $this->dentroDe(6, 'Y-m-d'),
            'noches' => 3
        ], $estancia);
    }

    public function testSePuedeEntrarHoy(): void {
        $this->assertArrayNotHasKey('error', $this->tools->validarEstancia($this->dentroDe(0), $this->dentroDe(1)));
    }

    /** @return array<string, array{int, int, string}> */
    public static function estanciasNoValidas(): array {
        return [
            'entrada pasada' => [-1, 2, 'already passed'],
            'misma fecha' => [3, 3, 'must be after'],
            'salida anterior' => [5, 3, 'must be after'],
            'más de 30 noches' => [1, 32, 'maximum stay'],
        ];
    }

    #[DataProvider('estanciasNoValidas')]
    public function testEstanciaNoValida(int $entrada, int $salida, string $mensaje): void {
        $estancia = $this->tools->validarEstancia($this->dentroDe($entrada), $this->dentroDe($salida));

        $this->assertStringContainsString($mensaje, $estancia['error']);
    }

    /** @return array<string, string> */
    private function datosCuenta(): array {
        return ['nombre' => 'Ana López', 'telf' => '+34 612345678', 'pais' => 'Spain',
            'provincia' => 'Lugo', 'ciudad' => 'Lugo', 'direccion' => 'Rúa 1'];
    }

    public function testDatosDeCuentaValidos(): void {
        $this->assertSame([], $this->tools->validarDatosCuenta($this->datosCuenta()));
    }

    public function testDatosDeCuentaNoValidos(): void {
        $datos = ['nombre' => '  ', 'telf' => '612345678', 'direccion' => str_repeat('x', 61)] + $this->datosCuenta();

        $this->assertSame([
            'nombre' => 'Required',
            'direccion' => 'Maximum 60 characters',
            'telf' => 'Invalid phone number'
        ], $this->tools->validarDatosCuenta($datos));
    }

    public function testEstanciaConFechasInvalidas(): void {
        $this->assertSame(['error' => 'Invalid dates'], $this->tools->validarEstancia('31-02-2030', '05-03-2030'));
    }

    public function testValidarPassword(): void {
        $tool = $this->tools;

        $this->assertNull($tool->validarPassword('12345678'));
        $this->assertNotNull($tool->validarPassword('1234567'));
        $this->assertNotNull($tool->validarPassword(str_repeat('a', 73)));
        // Se cuentan caracteres para el mínimo y bytes para el máximo de bcrypt
        $this->assertNull($tool->validarPassword('ññññññññ'));
        $this->assertNotNull($tool->validarPassword(str_repeat('ñ', 37)));
    }
}
