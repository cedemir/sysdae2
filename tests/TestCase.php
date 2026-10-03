<?php

namespace Tests;

use App\Models\Alojamento;
use App\Models\Aluno;
use App\Models\Apartamento;
use App\Models\Curso;
use App\Models\Turma;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    private static int $contador = 0;

    protected function setUp(): void
    {
        // TRAVA DE SEGURANÇA: roda antes de qualquer migração. Os testes recriam o banco, então
        // jamais podem apontar para o banco real. O phpunit.xml precisa forçar SQLite em memória.
        $conexao = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? getenv('DB_CONNECTION');
        $banco = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');

        if ($conexao !== 'sqlite' || $banco !== ':memory:') {
            throw new \RuntimeException(
                'Os testes só rodam em SQLite em memória (DB_CONNECTION=sqlite e DB_DATABASE=:memory: no phpunit.xml). '
                . 'Abortado para proteger o banco real.'
            );
        }

        parent::setUp();
    }

    // ---------- Usuários ----------

    protected function usuarioComPerfil(string $perfil): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate($perfil, 'web');

        $usuario = User::factory()->create();
        $usuario->assignRole($perfil);

        return $usuario;
    }

    /** Entra como um usuário do perfil e deixa o painel do Filament ativo (para testar as telas). */
    protected function entrarNoPainel(string $perfil): User
    {
        $usuario = $this->usuarioComPerfil($perfil);
        $this->actingAs($usuario);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $usuario;
    }

    // ---------- Cenários ----------

    /** CPF válido e diferente a cada chamada. */
    protected function cpf(): string
    {
        self::$contador++;
        $base = str_pad((string) (100000000 + (self::$contador * 7919) % 800000000), 9, '0', STR_PAD_LEFT);
        $digitos = array_map('intval', str_split($base));

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $digitos[$i] * (($t + 1) - $i);
            }
            $digitos[] = ((10 * $soma) % 11) % 10;
        }

        return implode('', $digitos);
    }

    protected function criarAluno(array $dados = []): Aluno
    {
        return Aluno::create(array_merge([
            'cpf' => $this->cpf(),
            'nome' => 'Aluno ' . (self::$contador + 1),
            'sexo' => 'masculino',
            'situacao' => 'cursando',
            'programa_beneficios' => 'nao_recebe',
        ], $dados));
    }

    protected function criarCurso(array $dados = []): Curso
    {
        return Curso::create(array_merge([
            'nome' => 'Curso ' . uniqid(),
            'nivel' => 'tecnico_integrado',
            'ativo' => true,
        ], $dados));
    }

    protected function criarTurma(?Curso $curso = null, array $dados = []): Turma
    {
        return Turma::create(array_merge([
            'codigo' => 'T' . strtoupper(uniqid()),
            'curso_id' => ($curso ?? $this->criarCurso())->id,
            'ano_letivo' => 2026,
            'turno' => 'integral',
            'ativa' => true,
        ], $dados));
    }

    protected function criarAlojamento(string $publico = 'misto'): Alojamento
    {
        return Alojamento::create([
            'nome' => 'Alojamento ' . uniqid(),
            'publico' => $publico,
            'ativo' => true,
        ]);
    }

    protected function criarApartamento(?Alojamento $alojamento = null, int $capacidade = 4, array $dados = []): Apartamento
    {
        return Apartamento::create(array_merge([
            'numero' => strtoupper(substr(uniqid(), -6)),
            'alojamento_id' => ($alojamento ?? $this->criarAlojamento())->id,
            'capacidade' => $capacidade,
            'ativo' => true,
        ], $dados));
    }
}