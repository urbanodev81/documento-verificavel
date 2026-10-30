<?php

namespace UrbanoDev\DocumentoVerificavel\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use UrbanoDev\DocumentoVerificavel\Models\DocumentoVerificavel;
use UrbanoDev\DocumentoVerificavel\Tests\TestCase;

/** Invariante 3: o código só existe dentro do inquilino que o emitiu. */
class EscopoTest extends TestCase
{
    use RefreshDatabase;

    public function test_acha_no_proprio_inquilino_com_codigo_digitado_de_qualquer_jeito(): void
    {
        $documento = $this->emitir(inquilino: 'clinica-a');

        $digitado = strtolower(str_replace('-', '', $documento->codigo));
        $this->assertTrue(DocumentoVerificavel::porCodigo($digitado, 'clinica-a')?->is($documento));
    }

    public function test_outro_inquilino_nao_acha(): void
    {
        $documento = $this->emitir(inquilino: 'clinica-a');

        $this->assertNull(DocumentoVerificavel::porCodigo($documento->codigo, 'clinica-b'));
        $this->assertNull(DocumentoVerificavel::porCodigo($documento->codigo, null));
    }

    public function test_sem_inquilino_so_acha_sem_inquilino(): void
    {
        $documento = $this->emitir(inquilino: null);

        $this->assertTrue(DocumentoVerificavel::porCodigo($documento->codigo, null)?->is($documento));
        $this->assertNull(DocumentoVerificavel::porCodigo($documento->codigo, 'clinica-a'));
    }

    public function test_codigo_inexistente(): void
    {
        $this->assertNull(DocumentoVerificavel::porCodigo('0000-0000-0000', 'clinica-a'));
    }
}
