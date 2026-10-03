<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Aluno extends Model
{
    use \App\Models\Concerns\Auditavel;

    protected $table = 'alunos';

    public const SEXOS = [
        'masculino' => 'Masculino',
        'feminino' => 'Feminino',
        'nao_dizer' => 'Prefiro não dizer',
    ];

    public const PROGRAMAS = [
        'nao_recebe' => 'Não recebe',
        'auxilio_moradia' => 'Auxílio moradia',
        'auxilio_permanencia' => 'Auxílio permanência',
    ];

    public const SITUACOES = [
        'cursando' => 'Cursando',
        'transferido' => 'Transferido',
        'trancamento' => 'Trancamento',
        'formado' => 'Formado',
    ];

    protected $fillable = [
        'cpf', 'nome', 'foto_path', 'sexo', 'email', 'telefone_estudante', 'nome_responsaveis',
        'telefone_familia', 'contato_emergencia', 'municipio', 'programa_beneficios', 'situacao',
        'observacoes',
    ];

    /** O CPF é sempre guardado apenas com os 11 dígitos. */
    protected function cpf(): Attribute
    {
        return Attribute::make(set: fn (string $valor) => preg_replace('/\D/', '', $valor));
    }

    protected static function booted(): void
    {
        // A foto sai do disco junto com o aluno.
        static::deleting(function (Aluno $aluno): void {
            // Apaga do disco os anexos das ocorrências do aluno (o banco apaga os registros em cascata).
            foreach (\App\Models\Ocorrencia::withoutGlobalScopes()->where('aluno_id', $aluno->id)->get() as $ocorrencia) {
                foreach ($ocorrencia->anexos ?? [] as $caminho) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($caminho);
                }
            }

            if ($aluno->foto_path) {
                Storage::disk('public')->delete($aluno->foto_path);
            }
        });
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    /** Matrícula mais recente: define o curso e a turma atuais do aluno. */
    public function matriculaAtual(): HasOne
    {
        //return $this->hasOne(Matricula::class)->latestOfMany(['data_matricula' => 'max', 'id' => 'max']);
        return $this->hasOne(Matricula::class)->latestOfMany(['data_matricula', 'id']);
    }

    public function residencia(): HasOne
    {
        return $this->hasOne(Residencia::class);
    }
}
