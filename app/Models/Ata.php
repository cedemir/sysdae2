<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ata extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'atas';

    protected $fillable = [
        'numero', 'data_reuniao', 'assunto', 'participantes', 'pauta', 'deliberacoes', 'encaminhamentos', 'user_id',
    ];

    protected function casts(): array
    {
        return ['data_reuniao' => 'date'];
    }

    protected static function booted(): void
    {
        // Guarda quem registrou a ata.
        static::creating(function (Ata $ata): void {
            $ata->user_id ??= auth()->id();
        });
    }

    /** Alunos citados na ata. */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'ata_aluno')->withTimestamps();
    }
}