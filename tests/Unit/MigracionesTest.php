<?php

namespace Tests\Unit;

use DB\migraciones;
use PHPUnit\Framework\TestCase;

class MigracionesTest extends TestCase {

    public function testSeparaSentenciasRespetandoCadenasYComentarios(): void {
        $sql = <<<'SQL'
            -- Comentario; con punto y coma
            CREATE TABLE a (x VARCHAR(10) DEFAULT 'a;b');
            /* bloque; de comentario */
            INSERT INTO a VALUES ('it''s; ok'), ("doble;"), ('barra \' ;');
            UPDATE `tabla;rara` SET x = 1
            SQL;

        $this->assertSame([
            "CREATE TABLE a (x VARCHAR(10) DEFAULT 'a;b')",
            "INSERT INTO a VALUES ('it''s; ok'), (\"doble;\"), ('barra \\' ;')",
            'UPDATE `tabla;rara` SET x = 1',
        ], migraciones::separarSentencias($sql));
    }

    public function testScriptVacioNoTieneSentencias(): void {
        $this->assertSame([], migraciones::separarSentencias("  -- solo un comentario\n ; ;"));
    }
}
