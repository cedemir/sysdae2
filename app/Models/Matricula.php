<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'matriculas';

    protected $fillable = ['aluno_id', 'turma_id', 'numero', 'data_matricula', 'situacao', 'observacoes'];

    protected function casts(): array
    {
        return ['data_matricula' => 'date'];
    }

    protected static function booted(): void
    {
        // A situação da matrícula mais recente passa a ser a situação do aluno.
        $sincronizarSituacao = function (Matricula $matricula): void {
            $ultima = Matricula::query()
                ->where('aluno_id', $matricula->aluno_id)
                ->orderByDesc('data_matricula')
                ->orderByDesc('id')
                ->first();

            if ($ultima) {
                Aluno::whereKey($matricula->aluno_id)->update(['situacao' => $ultima->situacao]);
            }
        };

        static::saved($sincronizarSituacao);
        static::deleted($sincronizarSituacao);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }
}