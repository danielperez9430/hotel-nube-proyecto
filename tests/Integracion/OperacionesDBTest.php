<?php

namespace Tests\Integracion;

use DB\operacionesDB as db;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas contra una base de datos real creada con config/db_hotel.sql y
 * config/db_usuarios.sql. Se ejecutan solo con DB_TESTS=1 y las variables
 * DB_* apuntando a esa base de datos (¡se borran sus reservas y usuarios!)
 */
#[Group('db')]
class OperacionesDBTest extends TestCase {

    private static \PDO $admin;

    public static function setUpBeforeClass(): void {
        if (getenv('DB_TESTS') !== '1') {
            return;
        }

        self::$admin = new \PDO(
            'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_NAME') ?: 'hotel') . ';charset=utf8mb4',
            getenv('DB_USER_ADMIN'), getenv('DB_PASSWORD_ADMIN'), [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    protected function setUp(): void {
        if (getenv('DB_TESTS') !== '1') {
            $this->markTestSkipped('Define DB_TESTS=1 y las variables DB_* para ejecutar las pruebas de base de datos');
        }

        // Empezamos cada prueba sin reservas, usuarios ni intentos de login
        self::$admin->exec('DELETE FROM reservas_servicios');
        self::$admin->exec('DELETE FROM habitaciones_reservas');
        self::$admin->exec('DELETE FROM reservas');
        self::$admin->exec('DELETE FROM usuarios');
        self::$admin->exec('DELETE FROM intentos_login');
    }

    private function registrar(string $email = 'ana@test.com'): int {
        $hash = password_hash('clave', PASSWORD_DEFAULT);
        return (new db('estandar'))->registrarUsuario('Ana López', $email, '+34 600000000', 'Spain', 'Lugo', 'Lugo', 'Rúa 1', $hash);
    }

    private function fecha(int $dias): string {
        return (new \DateTimeImmutable('today'))->modify(sprintf("%+d days", $dias))->format('Y-m-d');
    }

    public function testRegistroAsignaElRolEstandar(): void {
        $id = $this->registrar();

        $this->assertGreaterThan(0, $id);

        $usuario = (new db('conexion'))->buscarUsuario('ana@test.com');
        $this->assertSame('estandar', $usuario[1]);
        $this->assertSame($id, $usuario[2]);
        $this->assertSame('Ana López', $usuario[3]);
        $this->assertTrue(password_verify('clave', $usuario[0]));
    }

    public function testElEmailNoSePuedeRepetir(): void {
        $this->registrar();

        $this->expectException(\PDOException::class);
        $this->registrar();
    }

    public function testBuscarUsuarioInexistente(): void {
        $this->assertFalse((new db('conexion'))->buscarUsuario('nadie@test.com'));
    }

    public function testReservaYDisponibilidad(): void {
        $usuario = $this->registrar();
        $con = new db('estandar');

        // Sin reservas, todas las habitaciones están libres
        $total = (int) self::$admin->query('SELECT COUNT(*) FROM habitaciones')->fetchColumn();
        $precio = (float) self::$admin->query('SELECT precio FROM habitaciones WHERE id = 5')->fetchColumn();

        $libres = $con->buscarHabitaciones($this->fecha(10), $this->fecha(13));
        $this->assertCount($total, $libres);
        $this->assertLessThanOrEqual($libres[1]['precio'], $libres[0]['precio'], 'Ordenadas por precio');

        $reserva = $con->crearReserva($usuario, 5, $this->fecha(10), $this->fecha(13));
        $this->assertIsArray($reserva);
        $this->assertSame($precio, $reserva['precio_noche']);

        // Ocupada en cualquier estancia que se solape
        $this->assertFalse($con->crearReserva($usuario, 5, $this->fecha(12), $this->fecha(15)));
        $this->assertFalse($con->crearReserva($usuario, 5, $this->fecha(8), $this->fecha(11)));
        $ids = array_column($con->buscarHabitaciones($this->fecha(11), $this->fecha(12)), 'id');
        $this->assertNotContains(5, $ids);
        $this->assertCount($total - 1, $ids);

        // Libre justo antes y justo después: [entrada, salida)
        $this->assertIsArray($con->crearReserva($usuario, 5, $this->fecha(13), $this->fecha(15)));
        $this->assertIsArray($con->crearReserva($usuario, 5, $this->fecha(7), $this->fecha(10)));

        $reservas = $con->obtenerReservasUsuario($usuario);
        // De la entrada más reciente a la más antigua, con total = noches × precio
        $this->assertSame([$this->fecha(13), $this->fecha(10), $this->fecha(7)], array_column($reservas, 'fecha_entrada'));
        $this->assertEquals([$precio * 2, $precio * 3, $precio * 3], array_map('floatval', array_column($reservas, 'total')));
    }

    public function testNoSePuedeReservarUnaHabitacionInexistente(): void {
        $this->assertFalse((new db('estandar'))->crearReserva($this->registrar(), 9999, $this->fecha(1), $this->fecha(2)));
    }

    public function testActualizarHabitacion(): void {
        $con = new db('admin');

        $this->assertTrue($con->actualizarHabitacion(3, 'Deluxe Room', 42.5, false, true, true, 199.99));
        $habitacion = array_values(array_filter($con->obtenerListaHabitaciones(), fn($h) => $h['id'] === 3))[0];
        $this->assertSame(['Deluxe Room', '42.50', 0, 1, 1, '199.99'], [$habitacion['tipo_habitacion'], $habitacion['m2'],
            $habitacion['ventana'], $habitacion['internet'], $habitacion['servicio_limpieza'], $habitacion['precio']]);

        // Guardar los mismos datos no es un error
        $this->assertTrue($con->actualizarHabitacion(3, 'Deluxe Room', 42.5, false, true, true, 199.99));
        // Tipo o habitación inexistentes
        $this->assertFalse($con->actualizarHabitacion(3, 'No existe', 10, false, false, false, 10));
        $this->assertFalse($con->actualizarHabitacion(9999, 'Deluxe Room', 10, false, false, false, 10));

        // Dejamos la habitación como estaba en db_hotel.sql
        $con->actualizarHabitacion(3, 'Rustic Room', 25, true, false, true, 100);
    }

    public function testIntentosDeLogin(): void {
        $con = new db('conexion');

        for ($i = 0; $i < 3; $i++) {
            $con->registrarIntentoFallido('ana@test.com', '10.0.0.1');
        }
        $con->registrarIntentoFallido('otro@test.com', '10.0.0.1');

        $this->assertSame(['email' => 3, 'ip' => 4], $con->contarIntentosLogin('ana@test.com', '10.0.0.1', 15));
        $this->assertSame(['email' => 0, 'ip' => 0], $con->contarIntentosLogin('nadie@test.com', '10.0.0.2', 15));

        $con->borrarIntentosLogin('ana@test.com');
        $this->assertSame(['email' => 0, 'ip' => 1], $con->contarIntentosLogin('ana@test.com', '10.0.0.1', 15));
    }

    public function testDatosDeLaCuenta(): void {
        $id = $this->registrar();
        $con = new db('estandar');

        $cuenta = $con->obtenerUsuario($id);
        $this->assertSame('ana@test.com', $cuenta['email']);
        $this->assertArrayNotHasKey('password', $cuenta);

        $this->assertTrue($con->actualizarDatosUsuario($id, 'Ana María', '+34 699999999', 'Spain', 'A Coruna', 'Ames', 'Rúa Nova 5'));
        // Guardar los mismos datos no es un error
        $this->assertTrue($con->actualizarDatosUsuario($id, 'Ana María', '+34 699999999', 'Spain', 'A Coruna', 'Ames', 'Rúa Nova 5'));
        $this->assertFalse($con->actualizarDatosUsuario(999999, 'X', '+34 699999999', 'Spain', 'Lugo', 'Lugo', 'Rúa'));

        $cuenta = $con->obtenerUsuario($id);
        $this->assertSame(['Ana María', 'ana@test.com', 'Rúa Nova 5'], [$cuenta['nombre'], $cuenta['email'], $cuenta['direccion']]);
        $this->assertFalse($con->obtenerUsuario(999999));
    }

    public function testElUsuarioEstandarNoPuedeLeerContraseñasNiCambiarEmails(): void {
        $id = $this->registrar();
        $pdo = new \PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_NAME') ?: 'hotel'),
            getenv('DB_USER_ESTANDAR'), getenv('DB_PASSWORD_ESTANDAR'), [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        foreach (['SELECT password FROM usuarios', "UPDATE usuarios SET email = 'x@test.com' WHERE id = $id"] as $sql) {
            try {
                $pdo->query($sql);
                $this->fail("Debería estar denegado: $sql");
            } catch (\PDOException $e) {
                $this->assertMatchesRegularExpression('/denied/i', $e->getMessage());
            }
        }
    }

    public function testServiciosDisponiblesOrdenadosPorPrecio(): void {
        $servicios = (new db('estandar'))->obtenerServicios();
        $noDisponibles = (int) self::$admin->query('SELECT COUNT(*) FROM servicios WHERE disponibilidad = FALSE')->fetchColumn();
        $total = (int) self::$admin->query('SELECT COUNT(*) FROM servicios')->fetchColumn();

        $this->assertCount($total - $noDisponibles, $servicios);
        $precios = array_map('floatval', array_column($servicios, 'precio_servicio'));
        $ordenados = $precios;
        sort($ordenados);
        $this->assertSame($ordenados, $precios);
        $this->assertStringStartsWith('fa', $servicios[0]['icono']);
    }

    public function testLosUsuariosDeBaseDeDatosTienenPermisosMinimos(): void {
        // El usuario estándar no puede borrar habitaciones
        $this->assertSame(0, (new db('estandar'))->borrarHabitacion(1));
        $this->assertContains(1, array_column((new db('admin'))->obtenerListaHabitaciones(), 'id'));

        // El usuario del login no puede leer las habitaciones
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches('/denied/i');
        (new db('conexion'))->buscarHabitaciones($this->fecha(1), $this->fecha(2));
    }

    public function testRestablecerLaContraseña(): void {
        $id = $this->registrar();
        $con = new db('conexion');
        $con->registrarIntentoFallido('ana@test.com', '127.0.0.1');

        $restablecimiento = $con->crearRestablecimiento('ana@test.com');
        $this->assertSame('Ana López', $restablecimiento['nombre']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $restablecimiento['token']);

        // En la base de datos solo se guarda el hash del token
        $guardado = self::$admin->query("SELECT token_hash FROM restablecimientos_password WHERE id_usuario = $id")->fetchColumn();
        $this->assertSame(hash('sha256', $restablecimiento['token']), $guardado);

        $this->assertTrue($con->tokenRestablecimientoValido($restablecimiento['token']));
        $this->assertTrue($con->restablecerPassword($restablecimiento['token'], password_hash('nueva-clave', PASSWORD_DEFAULT)));

        // La contraseña cambia, se borran los intentos fallidos y el token ya no sirve
        $this->assertTrue(password_verify('nueva-clave', $con->buscarUsuario('ana@test.com')[0]));
        $this->assertSame(['email' => 0, 'ip' => 0], array_map('intval', $con->contarIntentosLogin('ana@test.com', '127.0.0.2', 15)));
        $this->assertFalse($con->tokenRestablecimientoValido($restablecimiento['token']));
        $this->assertFalse($con->restablecerPassword($restablecimiento['token'], password_hash('otra', PASSWORD_DEFAULT)));
    }

    public function testRestablecerConUnEmailInexistenteNoHaceNada(): void {
        $this->assertNull((new db('conexion'))->crearRestablecimiento('nadie@test.com'));
        $this->assertFalse((new db('conexion'))->restablecerPassword(str_repeat('a', 64), 'hash'));
    }

    public function testUnTokenNuevoInvalidaElAnteriorYHayLimitePorHora(): void {
        $this->registrar();
        $con = new db('conexion');

        $primero = $con->crearRestablecimiento('ana@test.com');
        $segundo = $con->crearRestablecimiento('ana@test.com');

        $this->assertFalse($con->tokenRestablecimientoValido($primero['token']));
        $this->assertTrue($con->tokenRestablecimientoValido($segundo['token']));

        $this->assertNotNull($con->crearRestablecimiento('ana@test.com'));
        $this->assertNull($con->crearRestablecimiento('ana@test.com'), 'Como máximo 3 enlaces por hora');
    }

    public function testUnTokenCaducadoNoSirve(): void {
        $id = $this->registrar();
        $con = new db('conexion');

        $restablecimiento = $con->crearRestablecimiento('ana@test.com');
        self::$admin->exec("UPDATE restablecimientos_password SET expira = NOW() - INTERVAL 1 MINUTE WHERE id_usuario = $id");

        $this->assertFalse($con->tokenRestablecimientoValido($restablecimiento['token']));
        $this->assertFalse($con->restablecerPassword($restablecimiento['token'], password_hash('nueva', PASSWORD_DEFAULT)));
    }

    public function testCancelarUnaReservaLiberaLaHabitacion(): void {
        $usuario = $this->registrar();
        $con = new db('estandar');

        $reserva = $con->crearReserva($usuario, 5, $this->fecha(20), $this->fecha(23));
        $num = $reserva['num_reserva'];
        $this->assertFalse($con->crearReserva($usuario, 5, $this->fecha(21), $this->fecha(22)));

        $datos = $con->obtenerReservaUsuario($num, $usuario);
        $this->assertSame('confirmada', $datos['estado']);
        $this->assertEquals($reserva['precio_noche'], (float) $datos['primera_noche']);
        $this->assertSame(1, (int) $datos['habitaciones']);

        $this->assertTrue($con->cancelarReserva($num, $usuario, 12.5));
        // Una segunda cancelación no hace nada
        $this->assertFalse($con->cancelarReserva($num, $usuario, 0));

        $listado = $con->obtenerReservasUsuario($usuario)[0];
        $this->assertSame('cancelada', $listado['estado']);
        $this->assertEquals(12.5, (float) $listado['cargo_cancelacion']);
        $this->assertNotNull($listado['fecha_cancelacion']);

        // La habitación vuelve a estar libre
        $this->assertContains(5, array_column($con->buscarHabitaciones($this->fecha(21), $this->fecha(22)), 'id'));
        $this->assertIsArray($con->crearReserva($usuario, 5, $this->fecha(21), $this->fecha(22)));
    }

    public function testNoSePuedenCancelarReservasDeOtroUsuario(): void {
        $ana = $this->registrar();
        $luis = $this->registrar('luis@test.com');
        $con = new db('estandar');

        $num = $con->crearReserva($ana, 5, $this->fecha(20), $this->fecha(23))['num_reserva'];

        $this->assertFalse($con->obtenerReservaUsuario($num, $luis));
        $this->assertFalse($con->cancelarReserva($num, $luis, 0));
        $this->assertSame('confirmada', $con->obtenerReservaUsuario($num, $ana)['estado']);
    }

    public function testElUsuarioEstandarNoPuedeCambiarLasFechasDeUnaReserva(): void {
        $usuario = $this->registrar();
        $con = new db('estandar');
        $num = $con->crearReserva($usuario, 5, $this->fecha(20), $this->fecha(23))['num_reserva'];

        $pdo = new \PDO(
            'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_NAME') ?: 'hotel'),
            getenv('DB_USER_ESTANDAR'), getenv('DB_PASSWORD_ESTANDAR'), [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches('/denied/i');
        $pdo->exec("UPDATE reservas SET fecha_salida = fecha_salida + INTERVAL 5 DAY WHERE num_reserva = $num");
    }

    public function testCrearHabitacion(): void {
        $admin = new db('admin');

        $id = $admin->crearHabitacion('Single Room', 18.5, true, false, true, 75);
        $this->assertGreaterThan(0, $id);

        $habitacion = array_values(array_filter($admin->obtenerListaHabitaciones(), fn($h) => (int) $h['id'] === $id))[0];
        $this->assertSame('Single Room', $habitacion['tipo_habitacion']);
        $this->assertEquals(18.5, (float) $habitacion['m2']);
        $this->assertSame([1, 0, 1], [(int) $habitacion['ventana'], (int) $habitacion['internet'], (int) $habitacion['servicio_limpieza']]);

        // Con un tipo inexistente no se crea nada
        $this->assertSame(0, $admin->crearHabitacion('Suite Imaginaria', 20, true, true, true, 100));

        self::$admin->exec("DELETE FROM habitaciones WHERE id = $id");
    }

    public function testReservasYOcupacionParaElAdministrador(): void {
        $ana = $this->registrar();
        $con = new db('estandar');
        $admin = new db('admin');
        $hoy = $this->fecha(0);

        $estancia = $con->crearReserva($ana, 5, $this->fecha(-1), $this->fecha(2));
        $entra = $con->crearReserva($ana, 4, $this->fecha(0), $this->fecha(3));
        $futura = $con->crearReserva($ana, 3, $this->fecha(40), $this->fecha(42));
        $cancelada = $con->crearReserva($ana, 2, $this->fecha(5), $this->fecha(6));
        $con->cancelarReserva($cancelada['num_reserva'], $ana, 0);

        $proximas = $admin->obtenerReservasAdmin('proximas', $hoy);
        $this->assertSame([$estancia['num_reserva'], $entra['num_reserva'], $futura['num_reserva']], array_column($proximas, 'num_reserva'));
        $this->assertSame('Ana López', $proximas[0]['nombre']);
        $this->assertSame('ana@test.com', $proximas[0]['email']);

        $this->assertSame([$cancelada['num_reserva']], array_column($admin->obtenerReservasAdmin('canceladas', $hoy), 'num_reserva'));
        $this->assertCount(4, $admin->obtenerReservasAdmin('todas', $hoy));

        $ocupacion = $admin->obtenerResumenOcupacion($hoy, 30);
        $total = (int) self::$admin->query('SELECT COUNT(*) FROM habitaciones')->fetchColumn();

        $this->assertSame($total, $ocupacion['habitaciones']);
        $this->assertSame(2, $ocupacion['ocupadas_hoy']);
        $this->assertSame(1, $ocupacion['entradas_hoy']);
        $this->assertSame(0, $ocupacion['salidas_hoy']);
        // 2 noches de la estancia en curso + 3 de la que entra hoy (la futura queda fuera de los 30 días)
        $this->assertSame(5, $ocupacion['noches_reservadas']);
        $this->assertSame(round(5 * 100 / ($total * 30), 1), $ocupacion['ocupacion']);
        // Ingresos de las que entran hoy o después (no la cancelada)
        $this->assertEquals(round($entra['precio_noche'] * 3 + $futura['precio_noche'] * 2, 2), $ocupacion['ingresos_futuros']);
    }

    public function testGestionarServicios(): void {
        $admin = new db('admin');
        $datos = ['nombre' => 'Yoga al amanecer', 'precio' => 18.0, 'descripcion' => 'Clase en la terraza.', 'disponible' => false, 'icono' => 'fas fa-sun'];

        $id = $admin->guardarServicio(null, $datos);
        $this->assertGreaterThan(0, $id);

        // Los no disponibles solo los ve el administrador
        $this->assertContains($id, array_map('intval', array_column($admin->obtenerTodosServicios(), 'id')));
        $this->assertNotContains($id, array_map('intval', array_column((new db('estandar'))->obtenerServicios(), 'id')));

        $this->assertSame($id, $admin->guardarServicio($id, ['disponible' => true] + $datos));
        $this->assertContains($id, array_map('intval', array_column((new db('estandar'))->obtenerServicios(), 'id')));

        $this->assertSame(0, $admin->guardarServicio(999999, $datos));
        $this->assertTrue($admin->borrarServicio($id));
        $this->assertFalse($admin->borrarServicio($id));
    }

    public function testReservaConServiciosExtra(): void {
        $ana = $this->registrar();
        $con = new db('estandar');
        $admin = new db('admin');

        // El 10 (Rooftop Cloud Dinner) no está disponible y se descarta
        $servicios = $con->obtenerServiciosDisponibles([3, 7, 7, 10, 9999]);
        $this->assertSame([3, 7], array_map('intval', array_column($servicios, 'id')));

        $reserva = $con->crearReserva($ana, 5, $this->fecha(20), $this->fecha(22), $servicios);

        $this->assertNotFalse($reserva);

        // El precio se copia: cambiarlo después no altera la reserva
        self::$admin->exec('UPDATE servicios SET precio_servicio = precio_servicio + 100 WHERE id = 7');

        try {
            $listado = $con->obtenerReservasUsuario($ana)[0];
            $esperado = array_sum(array_map('floatval', array_column($servicios, 'precio_servicio')));
            $this->assertEquals($esperado, (float) $listado['extras']);
            $this->assertSame('Breakfast in Bed, Spa & Wellness Circuit', $listado['servicios']);

            $this->assertEquals($esperado, (float) $admin->obtenerReservasAdmin('todas', $this->fecha(0))[0]['extras']);

            // Un servicio contratado no se puede borrar (se oculta como no disponible)
            $this->assertFalse($admin->borrarServicio(7));
        } finally {
            self::$admin->exec('UPDATE servicios SET precio_servicio = precio_servicio - 100 WHERE id = 7');
        }
    }

    public function testReservaSinServiciosExtra(): void {
        $ana = $this->registrar();
        $con = new db('estandar');

        $this->assertSame([], $con->obtenerServiciosDisponibles([]));
        $con->crearReserva($ana, 5, $this->fecha(20), $this->fecha(22));

        $listado = $con->obtenerReservasUsuario($ana)[0];
        $this->assertEquals(0, (float) $listado['extras']);
        $this->assertNull($listado['servicios']);
    }
}
