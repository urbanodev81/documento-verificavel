# urbanodev/documento-verificavel

Documento emitido pelo sistema com **código e QR de conferência**: o conteúdo é
congelado na emissão, guardado com o sha256 dele, e o papel leva um selo com
código, QR e hash que abre a página pública de conferência do sistema. É a
implementação Laravel do módulo **`kit-desenvolvimento/modulos/documento-verificavel`**
— a especificação e os invariantes estão lá; aqui está o código.

Nasceu dos documentos da votação do SSB (zerésima, ata, listas — `DocumentosDaVotacao`,
29/10/2026); primeiro uso como pacote: declarações do Synapse.

## O que entrega (e o que não)

- Tabela `documentos_verificaveis`, model `DocumentoVerificavel` (imutável depois de
  emitido; não se apaga) e as ações `EmitirDocumento` e `AnularDocumento`.
- `Codigo`: `XXXX-XXXX-XXXX` em alfabeto de 32 sem I, L, O e U; normaliza o que a pessoa digita.
- `Selo`: QR em SVG (data URI, desenha no dompdf sem gd) e o partial
  `documento-verificavel::selo` para o rodapé do PDF.
- **Não entrega** os tipos de documento, o layout do PDF, a rota nem a página pública de
  conferência: cada sistema faz as suas, com a marca e o domínio dele.

## Instalar num sistema

```jsonc
// composer.json do sistema
"repositories": [{ "type": "vcs", "url": "https://github.com/urbanodev81/documento-verificavel" }],
"require": { "urbanodev/documento-verificavel": "0.1.0" }   // versão exata, nunca faixa
```

Repositório público (MIT): o build baixa sem token. Banco por tenant (stancl):
`DOCUMENTO_VERIFICAVEL_CARREGAR_MIGRATIONS=false` e
`php artisan vendor:publish --tag=documento-verificavel-migrations`, movendo para
`database/migrations/tenant`.

## Usar

```php
use UrbanoDev\DocumentoVerificavel\Acoes\EmitirDocumento;
use UrbanoDev\DocumentoVerificavel\Models\DocumentoVerificavel;
use UrbanoDev\DocumentoVerificavel\Selo;

// Emitir: $dados é o que o papel afirma — e só isso (fica guardado como foi emitido).
$documento = app(EmitirDocumento::class)(
    tipo: 'comparecimento',
    titulo: 'Declaração de comparecimento',
    dados: ['paciente' => 'Maria F.', 'inicio' => '2026-10-29T14:00:00-03:00'],
    inquilino: (string) $tenant->id,
    dono: $agendamento,
    autor: $request->user(),
);

// PDF: monte com $documento->dados() (nunca com o estado de hoje) e inclua o selo.
// @include('documento-verificavel::selo', Selo::dados($documento, $urlDeConferencia))

// Conferência pública: o inquilino é obrigatório — código de outro dá null.
$documento = DocumentoVerificavel::porCodigo($codigoDigitado, (string) $tenant->id);
$documento?->integro();   // o conteúdo ainda bate com o hash
$documento?->anulado();
```

A página de conferência é **pública e sem login**: ponha `throttle` na rota e mostre só o
que o papel precisa para ser conferido (ver invariante 4 no kit).

## Testes

`scripts/testar.sh` roda a suíte em SQLite e num Postgres descartável (só precisa de Docker).
