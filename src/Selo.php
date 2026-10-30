<?php

namespace UrbanoDev\DocumentoVerificavel;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use UrbanoDev\DocumentoVerificavel\Models\DocumentoVerificavel;

/**
 * O que vai no rodapé do PDF: código, QR, endereço e hash. A URL é do
 * sistema (cada um tem o seu domínio e a sua página de conferência); o
 * pacote só desenha.
 *
 * No Blade do PDF: `@include('documento-verificavel::selo', Selo::dados($documento, $url))`.
 */
final class Selo
{
    /** @return array{codigo: string, hash: string, url: string, qr: string, anulado: bool} */
    public static function dados(DocumentoVerificavel $documento, string $url, int $tamanho = 200): array
    {
        return [
            'codigo' => $documento->codigo,
            'hash' => $documento->hash,
            'url' => $url,
            'qr' => self::qr($url, $tamanho),
            'anulado' => $documento->anulado(),
        ];
    }

    /** O QR como data URI SVG — o dompdf desenha sem extensão gd/imagick. */
    public static function qr(string $url, int $tamanho = 200): string
    {
        $svg = (new Writer(new ImageRenderer(new RendererStyle($tamanho, 0), new SvgImageBackEnd)))->writeString($url);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
