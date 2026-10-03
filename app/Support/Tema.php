<?php

namespace App\Support;

/** Visual do painel escolhido por cada usuário (menu do perfil > "Usar tema ..."). */
final class Tema
{
    public const CLASSICO = 'classico';

    public const MODERNO = 'moderno';

    public const TODOS = [
        self::CLASSICO => 'Clássico',
        self::MODERNO => 'Moderno',
    ];

    public static function atual(): string
    {
        $tema = auth()->user()?->tema;

        return array_key_exists((string) $tema, self::TODOS) ? $tema : self::CLASSICO;
    }

    public static function moderno(): bool
    {
        return self::atual() === self::MODERNO;
    }

    /** Folha de estilo do tema atual, em public/css. */
    public static function arquivoCss(): string
    {
        return self::moderno() ? 'css/sysdae-tema-moderno.css' : 'css/sysdae-tema.css';
    }

    private function __construct()
    {
    }
}
