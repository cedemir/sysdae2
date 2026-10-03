<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/** Cartão com o crédito do sistema, exibido no Painel. */
class Creditos extends Widget
{
    protected string $view = 'filament.widgets.creditos';

    // Fica ao lado do cartão do usuário, no lugar do antigo cartão do Filament.
    protected static ?int $sort = -2;

    // Texto fixo: aparece junto com a página, sem carregar depois.
    protected static bool $isLazy = false;
}
