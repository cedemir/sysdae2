<?php

namespace App\Rules;

use App\Models\Aluno;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Valida os dígitos do CPF e impede CPF repetido entre os alunos. */
class Cpf implements ValidationRule
{
    public function __construct(private ?int $ignorarAlunoId = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D/', '', (string) $value);

        if (!self::valido($cpf)) {
            $fail('O CPF informado não é válido.');
            return;
        }

        $existe = Aluno::query()
            ->where('cpf', $cpf)
            ->when($this->ignorarAlunoId, fn ($consulta, $id) => $consulta->whereKeyNot($id))
            ->exists();

        if ($existe) {
            $fail('Já existe um aluno cadastrado com este CPF.');
        }
    }

    public static function valido(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }

        return true;
    }
}