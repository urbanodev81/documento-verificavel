<?php

namespace UrbanoDev\DocumentoVerificavel;

/**
 * O código impresso no documento: 12 caracteres em três grupos, num alfabeto
 * de 32 sem I, L, O e U (não se confundem com 1, 0 nem viram palavra), o
 * que dá 60 bits — adivinhar um código válido não é caminho.
 */
final class Codigo
{
    public const ALFABETO = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    public static function gerar(): string
    {
        $codigo = '';
        for ($i = 0; $i < 12; $i++) {
            $codigo .= self::ALFABETO[random_int(0, 31)];
        }

        return implode('-', str_split($codigo, 4));
    }

    /** Aceita o código digitado sem hífen, com espaço e em minúsculas. */
    public static function normalizar(string $codigo): string
    {
        $limpo = strtoupper((string) preg_replace('/[^0-9A-Za-z]/', '', $codigo));

        return strlen($limpo) === 12 ? implode('-', str_split($limpo, 4)) : $limpo;
    }

    public static function valido(string $codigo): bool
    {
        return (bool) preg_match('/^(['.self::ALFABETO.']{4}-){2}['.self::ALFABETO.']{4}$/', $codigo);
    }
}
