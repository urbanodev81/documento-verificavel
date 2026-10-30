<?php

namespace UrbanoDev\DocumentoVerificavel\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use UrbanoDev\DocumentoVerificavel\Acoes\AnularDocumento;
use UrbanoDev\DocumentoVerificavel\Selo;
use UrbanoDev\DocumentoVerificavel\Tests\TestCase;

class SeloTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_e_svg_em_data_uri(): void
    {
        $qr = Selo::qr('https://clinica-a.exemplo.test/documentos/AB12-CD34-EF56');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $qr);
        $this->assertStringContainsString('<svg', base64_decode(substr($qr, strlen('data:image/svg+xml;base64,'))));
    }

    public function test_view_mostra_codigo_endereco_e_hash(): void
    {
        $documento = $this->emitir();
        $url = 'https://clinica-a.exemplo.test/documentos/'.$documento->codigo;

        $html = view('documento-verificavel::selo', Selo::dados($documento, $url))->render();

        $this->assertStringContainsString($documento->codigo, $html);
        $this->assertStringContainsString($url, $html);
        $this->assertStringContainsString($documento->hash, $html);
        $this->assertStringNotContainsString('ANULADO', $html);
    }

    public function test_anulado_aparece_no_selo(): void
    {
        $documento = $this->emitir();
        ($this->app->make(AnularDocumento::class))($documento, 'Data errada');

        $html = view('documento-verificavel::selo', Selo::dados($documento->refresh(), 'https://x.test'))->render();

        $this->assertStringContainsString('DOCUMENTO ANULADO', $html);
    }
}
