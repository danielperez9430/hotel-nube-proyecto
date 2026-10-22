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
        return (new \DateTimeImmutable('today'))->modify("+$dias days")->format('Y-m-d');
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
}
