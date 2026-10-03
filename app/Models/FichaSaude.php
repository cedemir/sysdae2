<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaSaude extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'fichas_saude';

    public const TIPOS_SANGUINEOS = [
        'nao_informado' => 'Não informado',
        'A+' => 'A+', 'A-' => 'A-',
        'B+' => 'B+', 'B-' => 'B-',
        'AB+' => 'AB+', 'AB-' => 'AB-',
        'O+' => 'O+', 'O-' => 'O-',
    ];

    protected $fillable = [
        'aluno_id', 'tipo_sanguineo', 'alergias', 'condicoes_saude', 'medicamentos_uso',
        'restricoes_alimentares', 'necessidades_especiais', 'cartao_sus', 'plano_saude',
        'unidade_referencia', 'observacoes', 'user_id',
    ];

    protected static function booted(): void
    {
        // Guarda quem atualizou a ficha por último (a data fica em updated_at).
        static::saving(function (FichaSaude $ficha): void {
            $ficha->user_id = auth()->id();
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}