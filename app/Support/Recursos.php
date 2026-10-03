<?php

namespace App\Support;

use App\Policies\AlojamentoPolicy;
use App\Policies\AlunoPolicy;
use App\Policies\ApartamentoPolicy;
use App\Policies\AtaPolicy;
use App\Policies\AtendimentoPolicy;
use App\Policies\CursoPolicy;
use App\Policies\FaltaPolicy;
use App\Policies\FichaSaudePolicy;
use App\Policies\MatriculaPolicy;
use App\Policies\OcorrenciaPolicy;
use App\Policies\PernoitePolicy;
use App\Policies\RegimePolicy;
use App\Policies\ResidenciaPolicy;
use App\Policies\SeriePolicy;
use App\Policies\TrocaApartamentoPolicy;
use App\Policies\TurmaPolicy;

/**
 * Cadastros (tabelas) cujo acesso por perfil pode ser escolhido na tela "Acessos por perfil".
 * A chave é o nome da policy sem "Policy", em snake_case (CursoPolicy => curso).
 * Usuários, Auditoria e a própria tela de acessos ficam de fora: são só do administrador.
 */
final class Recursos
{
    /** @return array<string, array{rotulo: string, tabela: string, policy: class-string, niveis: list<string>}> */
    public static function todos(): array
    {
        return self::cadastros() + self::relatorios();
    }

    /**
     * Cada relatório vira uma linha da tela de acessos (Sem acesso | Pode gerar).
     * O acesso inicial de cada perfil vem da lista "perfis" de RelatorioService::tipos().
     *
     * @return array<string, array<string, mixed>>
     */
    public static function relatorios(): array
    {
        $itens = [];

        foreach (\App\Services\RelatorioService::tipos() as $tipo => $dados) {
            $itens['relatorio_' . $tipo] = [
                'rotulo' => 'Relatório: ' . $dados['rotulo'],
                'tabela' => '(relatório)',
                'policy' => null,
                'niveis' => [PermissoesPerfil::NENHUM, PermissoesPerfil::CONSULTA],
                'rotulos_niveis' => [PermissoesPerfil::CONSULTA => 'Pode gerar'],
                'padrao' => $dados['perfis'],
            ];
        }

        return $itens;
    }

    /** Cadastros (tabelas) de onde cada relatório lê dados individuais: o perfil precisa consultá-los. */
    public static function cadastrosDoRelatorio(string $tipo): array
    {
        return [
            'apartamento' => ['aluno'],
            'trocas' => ['troca_apartamento'],
            'faltas' => ['falta'],
            'pernoites' => ['pernoite'],
            'ocorrencias' => ['ocorrencia'],
            'atendimentos' => ['atendimento'],
            'ficha' => ['aluno'],
        ][$tipo] ?? [];
    }

    /**
     * O usuário pode gerar o relatório? Administrador sempre pode. Os demais precisam da linha
     * do relatório liberada ("Pode gerar") E da consulta aos cadastros de onde ele lê os dados.
     */
    public static function podeGerarRelatorio(\App\Models\User $usuario, string $tipo): bool
    {
        if ($usuario->hasRole(Perfis::ADMIN)) {
            return true;
        }

        $padrao = \App\Services\RelatorioService::tipos()[$tipo]['perfis'] ?? [];
        $liberado = false;

        foreach ($usuario->getRoleNames() as $perfil) {
            $nivelPadrao = in_array($perfil, $padrao, true) ? PermissoesPerfil::CONSULTA : PermissoesPerfil::NENHUM;

            if (PermissoesPerfil::nivel($perfil, 'relatorio_' . $tipo, $nivelPadrao) === PermissoesPerfil::CONSULTA) {
                $liberado = true;
                break;
            }
        }

        if (! $liberado) {
            return false;
        }

        foreach (self::cadastrosDoRelatorio($tipo) as $cadastro) {
            if (! \Illuminate\Support\Facades\Gate::forUser($usuario)->allows('viewAny', self::modelo($cadastro))) {
                return false;
            }
        }

        return true;
    }

    /** Classe do model de um cadastro: ficha_saude => App\Models\FichaSaude. */
    public static function modelo(string $chave): string
    {
        return 'App\\Models\\' . \Illuminate\Support\Str::studly($chave);
    }

    /** Texto pequeno que aparece sob o nome do cadastro na tela de acessos. */
    public static function descricao(?string $chave): string
    {
        return str_starts_with((string) $chave, 'relatorio_') ? 'Relatório' : 'Tabela: ' . self::tabela($chave);
    }

    /**
     * Cadastros (tabelas) que têm acesso por perfil.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function cadastros(): array
    {
        $completo = [PermissoesPerfil::NENHUM, PermissoesPerfil::CONSULTA, PermissoesPerfil::EDICAO];
        $somenteConsulta = [PermissoesPerfil::NENHUM, PermissoesPerfil::CONSULTA];

        return [
            'aluno' => ['rotulo' => 'Alunos', 'tabela' => 'alunos', 'policy' => AlunoPolicy::class, 'niveis' => $completo],
            'matricula' => ['rotulo' => 'Matrículas', 'tabela' => 'matriculas', 'policy' => MatriculaPolicy::class, 'niveis' => $completo],
            'curso' => ['rotulo' => 'Cursos', 'tabela' => 'cursos', 'policy' => CursoPolicy::class, 'niveis' => $completo],
            'serie' => ['rotulo' => 'Séries', 'tabela' => 'series', 'policy' => SeriePolicy::class, 'niveis' => $completo],
            'turma' => ['rotulo' => 'Turmas', 'tabela' => 'turmas', 'policy' => TurmaPolicy::class, 'niveis' => $completo],
            'alojamento' => ['rotulo' => 'Alojamentos', 'tabela' => 'alojamentos', 'policy' => AlojamentoPolicy::class, 'niveis' => $completo],
            'apartamento' => ['rotulo' => 'Apartamentos', 'tabela' => 'apartamentos', 'policy' => ApartamentoPolicy::class, 'niveis' => $completo],
            'regime' => ['rotulo' => 'Regimes', 'tabela' => 'regimes', 'policy' => RegimePolicy::class, 'niveis' => $completo],
            'residencia' => ['rotulo' => 'Residências', 'tabela' => 'residencias', 'policy' => ResidenciaPolicy::class, 'niveis' => $completo],
            'troca_apartamento' => ['rotulo' => 'Histórico de trocas de apartamento', 'tabela' => 'trocas_apartamento', 'policy' => TrocaApartamentoPolicy::class, 'niveis' => $somenteConsulta],
            'falta' => ['rotulo' => 'Faltas na residência', 'tabela' => 'faltas', 'policy' => FaltaPolicy::class, 'niveis' => $completo],
            'pernoite' => ['rotulo' => 'Autorizações de pernoite', 'tabela' => 'pernoites', 'policy' => PernoitePolicy::class, 'niveis' => $completo],
            'ocorrencia' => ['rotulo' => 'Ocorrências disciplinares', 'tabela' => 'ocorrencias', 'policy' => OcorrenciaPolicy::class, 'niveis' => $completo],
            'atendimento' => ['rotulo' => 'Atendimentos psicossociais', 'tabela' => 'atendimentos', 'policy' => AtendimentoPolicy::class, 'niveis' => $completo],
            'ficha_saude' => ['rotulo' => 'Fichas de saúde', 'tabela' => 'fichas_saude', 'policy' => FichaSaudePolicy::class, 'niveis' => $completo],
            'ata' => ['rotulo' => 'Atas de reunião', 'tabela' => 'atas', 'policy' => AtaPolicy::class, 'niveis' => $completo],
        ];
    }

    /** Perfis que o administrador pode configurar (o administrador sempre tem acesso total). */
    public static function perfisEditaveis(): array
    {
        return [Perfis::DAE_CENTRAL, Perfis::RESIDENCIA, Perfis::PSICOSSOCIAL, Perfis::SOMENTE_CONSULTA];
    }

    /**
     * Acesso inicial de perfis que têm padrão próprio. Devolve null quando o perfil não tem
     * (vale então o padrão da policy de cada cadastro).
     */
    public static function padraoDoPerfil(string $perfil, string $recurso): ?string
    {
        if ($perfil === Perfis::SOMENTE_CONSULTA) {
            // Só consulta nos cadastros de estrutura e de alunos; sem acesso aos dados sensíveis,
            // às faltas, autorizações, atas e ao histórico de trocas.
            $consulta = ['aluno', 'matricula', 'curso', 'serie', 'turma', 'alojamento', 'apartamento', 'regime', 'residencia'];

            return in_array($recurso, $consulta, true) ? PermissoesPerfil::CONSULTA : PermissoesPerfil::NENHUM;
        }

        return null;
    }

    public static function rotulo(?string $chave): string
    {
        return self::todos()[$chave]['rotulo'] ?? (string) $chave;
    }

    public static function tabela(?string $chave): string
    {
        return self::todos()[$chave]['tabela'] ?? '-';
    }

    /** @return array<string, string> nível => nome, só os níveis permitidos para o cadastro */
    public static function niveisPermitidos(?string $chave): array
    {
        $permitidos = self::todos()[$chave]['niveis'] ?? [PermissoesPerfil::NENHUM];

        $rotulos = array_merge(PermissoesPerfil::NIVEIS, self::todos()[$chave]['rotulos_niveis'] ?? []);

        return array_intersect_key($rotulos, array_flip($permitidos));
    }

    private function __construct()
    {
    }
}