<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_verificaveis', function (Blueprint $table) {
            $table->id();
            // XXXX-XXXX-XXXX: é o que vai impresso e o que o QR abre.
            $table->string('codigo', 14)->unique();
            // Quem é o dono no sistema (sindicato, clínica). Nulo em sistema de um inquilino só.
            $table->string('inquilino', 64)->nullable();
            $table->string('tipo', 60);
            $table->string('titulo');
            // Do que o documento fala (agendamento, votação, relatório).
            $table->nullableMorphs('dono');
            // O JSON exatamente como foi assinado: o hash é dele, byte a byte.
            $table->longText('conteudo');
            $table->char('hash', 64);
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->string('autor');
            $table->timestamp('created_at');
            // Anular não apaga: a conferência passa a dizer "anulado" e por quê.
            $table->timestamp('anulado_em')->nullable();
            $table->string('anulado_por')->nullable();
            $table->text('motivo_anulacao')->nullable();

            $table->index(['inquilino', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_verificaveis');
    }
};
