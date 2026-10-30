<?php

namespace UrbanoDev\DocumentoVerificavel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;
use UrbanoDev\DocumentoVerificavel\Codigo;

/**
 * Um documento emitido. O conteúdo é congelado na emissão (invariante 1):
 * baixar de novo regenera o PDF deste JSON, não do estado de hoje do sistema.
 *
 * @property int $id
 * @property string $codigo
 * @property ?string $inquilino
 * @property string $tipo
 * @property string $titulo
 * @property string $conteudo
 * @property string $hash
 * @property ?int $autor_id
 * @property string $autor
 * @property \Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $anulado_em
 * @property ?string $anulado_por
 * @property ?string $motivo_anulacao
 */
class DocumentoVerificavel extends Model
{
    protected $table = 'documentos_verificaveis';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    /** O que a emissão fixou. Anular é a única mudança possível, e uma vez. */
    private const CONGELADOS = [
        'codigo', 'inquilino', 'tipo', 'titulo', 'dono_type', 'dono_id',
        'conteudo', 'hash', 'autor_id', 'autor', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'anulado_em' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $documento) {
            if ($documento->isDirty(self::CONGELADOS)) {
                throw new LogicException('Documento emitido não muda: emita outro e anule este.');
            }
            if ($documento->getOriginal('anulado_em') !== null) {
                throw new LogicException('Documento já anulado.');
            }
        });

        // Expurgo por obrigação legal, se houver, é decisão do sistema e passa
        // pelo query builder, de propósito e à vista (invariante 2).
        static::deleting(function () {
            throw new LogicException('Documento emitido não se apaga: anule.');
        });
    }

    /**
     * Busca pelo código dentro de UM inquilino. O inquilino é obrigatório na
     * assinatura para nenhum chamador esquecer: código de outra clínica dá
     * "não encontrado", igual a código inexistente (invariante 3).
     */
    public static function porCodigo(string $codigo, ?string $inquilino): ?self
    {
        return static::query()
            ->where('codigo', Codigo::normalizar($codigo))
            ->where('inquilino', $inquilino)
            ->first();
    }

    public function dono(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, mixed> o que o sistema mandou na emissão */
    public function dados(): array
    {
        return json_decode($this->conteudo, true, flags: JSON_THROW_ON_ERROR)['dados'] ?? [];
    }

    /** O conteúdo guardado ainda bate com o hash da emissão. */
    public function integro(): bool
    {
        return hash_equals($this->hash, hash('sha256', $this->conteudo));
    }

    public function anulado(): bool
    {
        return $this->anulado_em !== null;
    }
}
