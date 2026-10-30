<?php

namespace UrbanoDev\DocumentoVerificavel\Acoes;

use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;
use UrbanoDev\DocumentoVerificavel\Models\DocumentoVerificavel;

/**
 * Documento emitido com erro não se corrige nem se apaga: anula-se, com
 * motivo, e emite-se outro. Quem conferir o papel antigo vê que foi anulado.
 */
class AnularDocumento
{
    public function __invoke(DocumentoVerificavel $documento, string $motivo, ?Authenticatable $quem = null): DocumentoVerificavel
    {
        $motivo = trim($motivo);
        if ($motivo === '') {
            throw new InvalidArgumentException('Diga por que o documento foi anulado.');
        }

        $documento->forceFill([
            'anulado_em' => now(),
            'anulado_por' => $quem?->name ?? 'Sistema',
            'motivo_anulacao' => $motivo,
        ])->save();

        return $documento;
    }
}
