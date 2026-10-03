<?php

namespace App\Support;

final class Formatos
{
    /** 12345678901 -> 123.456.789-01 */
    public static function cpf(?string $cpf): string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf);

        if (strlen($digitos) !== 11) {
            return (string) $cpf;
        }

        return substr($digitos, 0, 3) . '.' . substr($digitos, 3, 3) . '.'
            . substr($digitos, 6, 3) . '-' . substr($digitos, 9, 2);
    }

    private function __construct()
    {
    }
}