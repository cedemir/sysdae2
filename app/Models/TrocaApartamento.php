<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrocaApartamento extends Model
{
    protected $table = 'trocas_apartamento';

    protected $fillable = ['aluno_id', 'origem_apartamento_id', 'destino_apartamento_id', 'data_troca', 'user_id'];

    protected function casts(): array
    {
        return ['data_troca' => 'date'];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function origem(): BelongsTo
    {
        return $this->belongsTo(Apartamento::class, 'origem_apartamento_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Apartamento::class, 'destino_apartamento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}