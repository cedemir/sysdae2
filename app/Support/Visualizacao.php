<?php

namespace App\Support;

use Filament\Infolists\Components\TextEntry;

/** Campos de texto das páginas de detalhe. */
final class Visualizacao
{
    /** Texto longo, em largura total, preservando as quebras de linha digitadas. */
    public static function texto(string $campo, string $rotulo): TextEntry
    {
        return TextEntry::make($campo)
            ->label($rotulo)
            ->placeholder('-')
            ->columnSpanFull()
            ->html()
            ->formatStateUsing(fn (?string $state) => nl2br(e((string) $state)));
    }

    private function __construct()
    {
    }
}