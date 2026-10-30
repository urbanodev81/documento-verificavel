{{--
    Selo de conferência para o PDF (dompdf): tabela e estilo em linha, que é
    o que o dompdf entende. Publique a view para mudar o texto ou o tamanho.
    Variáveis: Selo::dados($documento, $url).
--}}
<table style="width: 100%; border-top: 1px solid #999; margin-top: 16px; padding-top: 8px; font-size: 9px; color: #333; border-collapse: collapse;">
    <tr>
        <td style="width: 84px; vertical-align: top; padding: 6px 10px 0 0;">
            <img src="{{ $qr }}" alt="QR de conferência" style="width: 76px; height: 76px;">
        </td>
        <td style="vertical-align: top; padding-top: 6px;">
            @if ($anulado)
                <div style="font-size: 11px; font-weight: bold; color: #a00; margin-bottom: 4px;">DOCUMENTO ANULADO</div>
            @endif
            <div style="font-size: 11px; margin-bottom: 4px;">Código de verificação: <strong style="letter-spacing: 1px;">{{ $codigo }}</strong></div>
            <div style="margin-bottom: 4px;">Confira a autenticidade lendo o QR ou em {{ $url }}</div>
            <div style="color: #666; word-break: break-all;">SHA-256: {{ $hash }}</div>
        </td>
    </tr>
</table>
