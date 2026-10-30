<?php

namespace UrbanoDev\DocumentoVerificavel\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use UrbanoDev\DocumentoVerificavel\Acoes\AnularDocumento;
use UrbanoDev\DocumentoVerificavel\Tests\TestCase;

/** Invariantes 1 e 2: o emitido não muda nem some; anula-se, uma vez, com motivo. */
class ImutavelTest extends TestCase
{
    use RefreshDatabase;

    public function test_conteudo_nao_muda(): void
    {
        $documento = $this->emitir();

        $this->expectException(LogicException::class);
        $documento->update(['conteudo' => '{}']);
    }

    public function test_titulo_e_autor_nao_mudam(): void
    {
        $documento = $this->emitir();

        try {
            $documento->update(['titulo' => 'Outro']);
            $this->fail('título mudou');
        } catch (LogicException) {
        }

        $this->expectException(LogicException::class);
        $documento->refresh()->update(['autor' => 'Outra pessoa']);
    }

    public function test_nao_se_apaga(): void
    {
        $documento = $this->emitir();

        $this->expectException(LogicException::class);
        $documento->delete();
    }

    public function test_anula_com_motivo_e_so_uma_vez(): void
    {
        $documento = $this->emitir();
        $anular = $this->app->make(AnularDocumento::class);

        $anular($documento, 'Data errada', $this->autor('Bia Admin', 9));

        $documento->refresh();
        $this->assertTrue($documento->anulado());
        $this->assertSame('Bia Admin', $documento->anulado_por);
        $this->assertSame('Data errada', $documento->motivo_anulacao);
        $this->assertTrue($documento->integro());

        $this->expectException(LogicException::class);
        $anular($documento, 'De novo');
    }

    public function test_anular_sem_motivo_e_recusado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->app->make(AnularDocumento::class))($this->emitir(), '   ');
    }
}
