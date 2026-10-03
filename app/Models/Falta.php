<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Falta extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'faltas';

    protected $fillable = ['aluno_id', 'data_falta', 'justificada', 'observacao', 'user_id'];

    protected function casts(): array
    {
        return [
            'data_falta' => 'date',
            'justificada' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Guarda quem registrou a falta.
        static::creating(function (Falta $falta): void {
            $falta->user_id ??= auth()->id();
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}