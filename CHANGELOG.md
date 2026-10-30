# Changelog

## [Não lançado]

## [0.1.0] - 2026-10-29

### Adicionado
- Tabela `documentos_verificaveis` e model `DocumentoVerificavel`: código, inquilino, tipo,
  título, dono polimórfico, conteúdo JSON congelado com o código dentro, sha256, autor.
  Emitido não muda nem se apaga; anular é a única mudança, uma vez e com motivo.
- `EmitirDocumento`, `AnularDocumento`, `DocumentoVerificavel::porCodigo()` (sempre dentro
  de um inquilino), `integro()`.
- `Codigo` (`XXXX-XXXX-XXXX`, alfabeto sem I/L/O/U) e `Selo` (QR SVG em data URI + partial
  Blade para o PDF, com "DOCUMENTO ANULADO" quando for o caso).
- Nasceu do `DocumentosDaVotacao` do SSB (pendência 241).
