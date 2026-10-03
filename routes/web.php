<?php

use Illuminate\Support\Facades\Route;

// A página inicial leva direto ao painel (que pede o login, se preciso).
Route::redirect('/', '/admin');

require __DIR__ . '/relatorios.php';

require __DIR__ . '/anexos.php';

// Troca o visual do painel do usuário logado (Clássico ou Moderno).
Route::get('/tema/{tema}', function (string $tema) {
    abort_unless(array_key_exists($tema, \App\Support\Tema::TODOS), 404);

    auth()->user()->forceFill(['tema' => $tema])->save();

    return redirect()->back(fallback: '/admin');
})->middleware('auth')->name('tema.trocar');
