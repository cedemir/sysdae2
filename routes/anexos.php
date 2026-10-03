<?php

use App\Http\Controllers\AnexoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('anexos')->name('anexos.')->group(function () {
    Route::get('/ocorrencias/{ocorrencia}', [AnexoController::class, 'listar'])->name('ocorrencia');
    Route::get('/ocorrencias/{ocorrencia}/{indice}', [AnexoController::class, 'baixar'])
        ->whereNumber('indice')
        ->name('ocorrencia.baixar');
});