<?php

namespace UrbanoDev\DocumentoVerificavel\Acoes;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use UrbanoDev\DocumentoVerificavel\Codigo;
use UrbanoDev\DocumentoVerificavel\Models\DocumentoVerificavel;

/**
 * Emite: gera o código, congela o conteúdo num JSON com o código dentro (o
 * papel e a conferência dizem a mesma coisa), guarda o sha256 dele.
 *
 * O sistema monta `$dados` com o que o documento afirma — e só isso: o que
 * entra aqui fica guardado como foi emitido, então dado sensível que o papel
 * não precisa não entra.
 */
class EmitirDocumento
{
    /** @param array<string, mixed> $dados */
    public function __invoke(
        string $tipo,
        string $titulo,
        array $dados,
        ?string $inquilino = null,
        ?Model $dono = null,
        ?Authenticatable $autor = null,
    ): DocumentoVerificavel {
        $nomeDoAutor = $autor?->name ?? 'Sistema';

        // 60 bits tornam a colisão rara; o índice único é a garantia final.
        do {
            $codigo = Codigo::gerar();
        } while (DocumentoVerificavel::query()->where('codigo', $codigo)->exists());

        $emitidoEm = now();
        $conteudo = json_encode([
            'documento' => [
                'versao' => 1,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'codigo' => $codigo,
                'inquilino' => $inquilino,
                'emitido_em' => $emitidoEm->toIso8601String(),
                'emitido_por' => $nomeDoAutor,
            ],
            'dados' => $dados,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return DocumentoVerificavel::query()->create([
            'codigo' => $codigo,
            'inquilino' => $inquilino,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'dono_type' => $dono?->getMorphClass(),
            'dono_id' => $dono?->getKey(),
            'conteudo' => $conteudo,
            'hash' => hash('sha256', $conteudo),
            'autor_id' => $autor?->getAuthIdentifier(),
            'autor' => $nomeDoAutor,
            'created_at' => $emitidoEm,
        ]);
    }
}
