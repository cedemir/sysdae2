<?php

namespace App\Models;

use App\Models\Concerns\RestringeSigilo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Atendimento extends Model
{
    use RestringeSigilo;

    use \App\Models\Concerns\Auditavel;

    protected $table = 'atendimentos';

    public const FORMAS = [
        'presencial' => 'Presencial',
        'online' => 'Online',
        'telefone' => 'Telefone',
    ];

    protected $fillable = [
        'aluno_id', 'data_atendimento', 'hora_atendimento', 'servidores', 'forma', 'relato',
        'outras_observacoes', 'historia_vida', 'encaminhamentos', 'sigiloso', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'data_atendimento' => 'date',
            'sigiloso' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Atendimento $atendimento): void {
            $atendimento->user_id ??= auth()->id();
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}