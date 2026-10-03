<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'cursos';

    public const NIVEIS = [
        'tecnico_integrado' => 'Técnico integrado',
        'tecnico_subsequente' => 'Técnico subsequente',
        'superior' => 'Superior',
        'pos_graduacao' => 'Pós-graduação',
    ];

    protected $fillable = ['nome', 'sigla', 'nivel', 'duracao_anos', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class);
    }
}