<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pernoite extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'pernoites';

    protected $fillable = [
        'aluno_id', 'data_pernoite', 'parcial', 'justificativa', 'forma_autorizacao', 'quem_autorizou', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'data_pernoite' => 'date',
            'parcial' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Guarda quem registrou a autorização.
        static::creating(function (Pernoite $pernoite): void {
            $pernoite->user_id ??= auth()->id();
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}