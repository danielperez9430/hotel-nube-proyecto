<?php

namespace Tests\Integracion;

use DB\migraciones;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Aplica migraciones de prueba en una base de datos temporal. Necesita
 * DB_TESTS=1 y un usuario con permisos de esquema (DB_USER_MIGRACIONES)
 */
#[Group('db')]
class MigracionesTest extends TestCase {

    private \PDO $pdo;
    private string $directorio;
    private string $baseDeDatos;

    protected function setUp(): void {
        if (getenv('DB_TESTS') !== '1' || getenv('DB_USER_MIGRACIONES') === false) {
            $this->markTestSkipped('Define DB_TESTS=1 y DB_USER_MIGRACIONES para probar las migraciones');
        }

        $this->baseDeDatos = 'hotel_test_migraciones';
        $this->pdo = new \PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
            getenv('DB_USER_MIGRACIONES'), getenv('DB_PASSWORD_MIGRACIONES') ?: '', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec("DROP DATABASE IF EXISTS {$this->baseDeDatos}");
        $this->pdo->exec("CREATE DATABASE {$this->baseDeDatos}");
        $this->pdo->exec("USE {$this->baseDeDatos}");

        $this->directorio = sys_get_temp_dir() . '/migraciones_' . uniqid();
        mkdir($this->directorio);
        file_put_contents("$this->directorio/001_crear.sql", "CREATE TABLE notas (id INT PRIMARY KEY, texto VARCHAR(20));\nINSERT INTO notas VALUES (1, 'uno;');");
        file_put_contents("$this->directorio/002_columna.sql", "ALTER TABLE notas ADD visible BOOLEAN NOT NULL DEFAULT TRUE;");
        file_put_contents("$this->directorio/notas.txt", "no es una migración");
    }

    protected function tearDown(): void {
        if (isset($this->pdo)) {
            $this->pdo->exec("DROP DATABASE IF EXISTS {$this->baseDeDatos}");
            array_map('unlink', glob("$this->directorio/*"));
            rmdir($this->directorio);
        }
    }

    public function testAplicaLasPendientesUnaSolaVez(): void {
        $migraciones = new migraciones($this->pdo, $this->directorio);

        $this->assertSame(['001_crear.sql', '002_columna.sql'], $migraciones->pendientes());
        $this->assertSame(['001_crear.sql', '002_columna.sql'], $migraciones->aplicar());
        $this->assertSame(['uno;', 1], array_values(array_map(fn($v) => is_numeric($v) ? (int) $v : $v,
            $this->pdo->query('SELECT texto, visible FROM notas')->fetch(\PDO::FETCH_ASSOC))));

        // La segunda vez no hay nada que aplicar
        $this->assertSame([], $migraciones->aplicar());

        // Una migración nueva se aplica sin repetir las anteriores
        file_put_contents("$this->directorio/003_otra.sql", "INSERT INTO notas (id, texto) VALUES (2, 'dos');");
        $this->assertSame(['003_otra.sql'], $migraciones->aplicar());
    }

    public function testSeDetieneEnLaMigracionQueFalla(): void {
        file_put_contents("$this->directorio/002_columna.sql", "ALTER TABLE no_existe ADD x INT;");
        $migraciones = new migraciones($this->pdo, $this->directorio);

        try {
            $migraciones->aplicar();
            $this->fail('Debería fallar');
        } catch (\PDOException $e) {
            // La primera queda registrada y la que falla sigue pendiente
            $this->assertSame(['002_columna.sql'], $migraciones->pendientes());
        }
    }
}
