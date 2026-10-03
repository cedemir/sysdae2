<?php

use App\Http\Controllers\RelatorioController;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Support\Facades\Route;

// O middleware "auth" precisa de uma rota chamada "login"; ela leva ao login do painel.
Route::get('/entrar', fn () => redirect('/admin/login'))->name('login');

Route::middleware('auth')->prefix('relatorios')->name('relatorios.')->group(function () {
    // "panel:admin" monta o painel do Filament, para as telas usarem o mesmo layout (menu, tema, modo escuro).
    Route::middleware(['panel:admin', DispatchServingFilamentEvent::class])->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::get('/gerar', [RelatorioController::class, 'gerar'])->name('gerar');
    });
    Route::get('/alunos', [RelatorioController::class, 'alunos'])->middleware('throttle:60,1')->name('alunos');
    Route::get('/ata/{ata}', [RelatorioController::class, 'ata'])->name('ata');
});