<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Session\session;

class SessionTest extends TestCase {

    #[RunInSeparateProcess]
    public function testElVisitanteTieneRolEstandarYNingunUsuario(): void {
        session::comprobar_sesion();

        $this->assertSame('estandar', $_SESSION['rol']);
        $this->assertNull(session::usuario());
        $this->assertFalse(session::esAdmin());
    }

    #[RunInSeparateProcess]
    public function testIniciarSesionGuardaElUsuarioYConservaElRol(): void {
        session::iniciarSesionUsuario(7, 'Ana López', 'ana@test.com', 'admin');
        session::comprobar_sesion();

        $this->assertSame(['id' => 7, 'nombre' => 'Ana López', 'email' => 'ana@test.com', 'rol' => 'admin'], session::usuario());
        $this->assertTrue(session::esAdmin());
    }

    #[RunInSeparateProcess]
    public function testElTokenCsrfEsAleatorioYEstable(): void {
        $token = session::tokenCsrf();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
        $this->assertSame($token, session::tokenCsrf());
    }
}
