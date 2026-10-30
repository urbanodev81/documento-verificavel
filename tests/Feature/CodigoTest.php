<?php

namespace UrbanoDev\DocumentoVerificavel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use UrbanoDev\DocumentoVerificavel\Codigo;

class CodigoTest extends TestCase
{
    public function test_formato_e_alfabeto_sem_letras_ambiguas(): void
    {
        for ($i = 0; $i < 300; $i++) {
            $codigo = Codigo::gerar();
            $this->assertMatchesRegularExpression('/^[0-9A-Z]{4}-[0-9A-Z]{4}-[0-9A-Z]{4}$/', $codigo);
            $this->assertDoesNotMatchRegularExpression('/[ILOU]/', $codigo);
            $this->assertTrue(Codigo::valido($codigo));
        }
    }

    public function test_normaliza_o_que_a_pessoa_digita(): void
    {
        $this->assertSame('AB12-CD34-EF56', Codigo::normalizar('ab12cd34ef56'));
        $this->assertSame('AB12-CD34-EF56', Codigo::normalizar(' ab12 - cd34 - ef56 '));
        $this->assertSame('AB12-CD34-EF56', Codigo::normalizar('AB12-CD34-EF56'));
        $this->assertSame('ABC', Codigo::normalizar('abc'));
        $this->assertFalse(Codigo::valido('AB1I-CD34-EF56'));
    }
}
