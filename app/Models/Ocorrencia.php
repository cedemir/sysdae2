<?php

namespace App\Models;

use App\Models\Concerns\RestringeSigilo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Ocorrencia extends Model
{
    use RestringeSigilo;

    use \App\Models\Concerns\Auditavel;

    protected $table = 'ocorrencias';

    public const SITUACOES = [
        'pendente' => 'Pendente',
        'ok' => 'OK',
    ];

    protected $fillable = [
        'aluno_id', 'data_ocorrencia', 'descricao', 'sigiloso', 'data_reuniao', 'medidas', 'advertencia',
        'suspensao_residencia', 'perda_vaga', 'atividades_orientadas', 'horas_recebidas', 'horas_cumpridas',
        'setor', 'servidor', 'praticas_restaurativas', 'outros_encaminhamentos', 'anexos', 'anexos_nomes', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'data_ocorrencia' => 'date',
            'data_reuniao' => 'date',
            'sigiloso' => 'boolean',
            'suspensao_residencia' => 'boolean',
            'anexos' => 'array',
            'anexos_nomes' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ocorrencia $ocorrencia): void {
            $ocorrencia->user_id ??= auth()->id();
        });

        // Os anexos saem do disco junto com a ocorrência.
        static::deleting(function (Ocorrencia $ocorrencia): void {
            foreach ($ocorrencia->anexos ?? [] as $caminho) {
                Storage::disk('local')->delete($caminho);
            }
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}