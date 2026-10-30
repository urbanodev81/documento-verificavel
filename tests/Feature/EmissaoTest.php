<?php

namespace UrbanoDev\DocumentoVerificavel\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use UrbanoDev\DocumentoVerificavel\Codigo;
use UrbanoDev\DocumentoVerificavel\Tests\Consulta;
use UrbanoDev\DocumentoVerificavel\Tests\TestCase;

/** Especificação, "Emissão": código, conteúdo congelado com o código dentro, hash e autor. */
class EmissaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_emite_com_codigo_hash_do_conteudo_e_autor(): void
    {
        $consulta = Consulta::query()->create(['quando' => '2026-10-29 14:00']);

        $documento = $this->emitir(dono: $consulta);

        $this->assertTrue(Codigo::valido($documento->codigo));
        $this->assertSame(hash('sha256', $documento->conteudo), $documento->hash);
        $this->assertSame('Ana Profissional', $documento->autor);
        $this->assertSame(7, (int) $documento->autor_id);
        $this->assertTrue($documento->dono->is($consulta));

        $json = json_decode($documento->refresh()->conteudo, true);
        $this->assertSame($documento->codigo, $json['documento']['codigo']);
        $this->assertSame('Declaração de comparecimento', $json['documento']['titulo']);
        $this->assertSame('clinica-a', $json['documento']['inquilino']);
        $this->assertSame(['paciente' => 'M. F.', 'data' => '2026-10-29'], $documento->dados());
        $this->assertTrue($documento->integro());
    }

    public function test_conteudo_guarda_acento_sem_escapar(): void
    {
        $documento = $this->emitir(['texto' => 'Declaração — sessão às 14h']);

        $this->assertStringContainsString('Declaração — sessão às 14h', $documento->refresh()->conteudo);
    }

    public function test_sem_autor_e_o_sistema(): void
    {
        $documento = ($this->app->make(\UrbanoDev\DocumentoVerificavel\Acoes\EmitirDocumento::class))('aviso', 'Aviso', []);

        $this->assertSame('Sistema', $documento->autor);
        $this->assertNull($documento->autor_id);
        $this->assertNull($documento->inquilino);
    }

    public function test_conteudo_adulterado_no_banco_deixa_de_ser_integro(): void
    {
        $documento = $this->emitir();

        \Illuminate\Support\Facades\DB::table('documentos_verificaveis')->where('id', $documento->id)
            ->update(['conteudo' => str_replace('M. F.', 'X. Y.', $documento->conteudo)]);

        $this->assertFalse($documento->refresh()->integro());
    }
}
