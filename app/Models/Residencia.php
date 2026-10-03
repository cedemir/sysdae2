<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Residencia extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'residencias';

    public const CATEGORIAS = [
        'residente' => 'Residente',
        'semirresidente' => 'Semirresidente',
    ];

    protected $fillable = ['aluno_id', 'categoria', 'apartamento_id', 'regime_id', 'data_entrada', 'ocorrencias'];

    protected function casts(): array
    {
        return ['data_entrada' => 'date'];
    }

    protected static function booted(): void
    {
        // Toda mudança de apartamento (inclusive sair sem ir para outro) entra no histórico.
        static::saved(function (Residencia $residencia): void {
            //if ($residencia->wasRecentlyCreated || ! $residencia->wasChanged('apartamento_id')) {
            //    return;
            $origem = $residencia->getOriginal('apartamento_id');
            if ($origem === null || (int) $origem === (int) $residencia->apartamento_id) {
                return; // primeira atribuição de apartamento ou nenhuma mudança: não é troca
            }

            TrocaApartamento::create([
                'aluno_id' => $residencia->aluno_id,
                'origem_apartamento_id' => $origem,
                'destino_apartamento_id' => $residencia->apartamento_id,
                'data_troca' => now()->toDateString(),
                'user_id' => auth()->id(),
            ]);
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function apartamento(): BelongsTo
    {
        return $this->belongsTo(Apartamento::class);
    }

    public function regime(): BelongsTo
    {
        return $this->belongsTo(Regime::class);
    }
}