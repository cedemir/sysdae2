<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Apartamento;
use App\Models\Atendimento;
use App\Models\Falta;
use App\Models\Ocorrencia;
use App\Models\Pernoite;
use App\Models\Residencia;
use App\Models\TrocaApartamento;
use App\Models\User;
use App\Support\Formatos;
use App\Support\Perfis;
use App\Support\Recursos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Monta os relatórios como dados simples: título, filtros, resumo e seções com tabela.
 * A mesma estrutura serve para a tela e para o PDF.
 */
class RelatorioService
{
    /**
     * Tipos de relatório. "usa" diz quais filtros se aplicam; "exige", quais são obrigatórios.
     *
     * @return array<string, array{rotulo: string, perfis: list<string>, usa: list<string>, exige: list<string>}>
     */
    public static function tipos(): array
    {
        $residencia = [Perfis::ADMIN, Perfis::RESIDENCIA, Perfis::DAE_CENTRAL];

        return [
            'estatisticas' => ['rotulo' => 'Estatísticas da residência', 'perfis' => [...$residencia, Perfis::SOMENTE_CONSULTA], 'usa' => [], 'exige' => []],
            'apartamento' => ['rotulo' => 'Alunos de um apartamento', 'perfis' => [...$residencia, Perfis::SOMENTE_CONSULTA], 'usa' => ['apto'], 'exige' => ['apto']],
            'trocas' => ['rotulo' => 'Trocas de apartamento', 'perfis' => $residencia, 'usa' => ['aluno', 'periodo'], 'exige' => []],
            'faltas' => ['rotulo' => 'Faltas na residência', 'perfis' => $residencia, 'usa' => ['aluno', 'periodo'], 'exige' => []],
            'pernoites' => ['rotulo' => 'Autorizações de pernoite', 'perfis' => $residencia, 'usa' => ['aluno', 'periodo'], 'exige' => []],
            'ocorrencias' => [
                'rotulo' => 'Ocorrências disciplinares',
                'perfis' => [Perfis::ADMIN, Perfis::DAE_CENTRAL, Perfis::PSICOSSOCIAL],
                'usa' => ['aluno', 'periodo'],
                'exige' => [],
            ],
            'atendimentos' => [
                'rotulo' => 'Atendimentos psicossociais',
                'perfis' => [Perfis::ADMIN, Perfis::PSICOSSOCIAL],
                'usa' => ['aluno', 'periodo'],
                'exige' => [],
            ],
            'ficha' => ['rotulo' => 'Ficha do aluno', 'perfis' => Perfis::TODOS, 'usa' => ['aluno'], 'exige' => ['aluno']],
        ];
    }

    /** @return array<string, array{rotulo: string, perfis: list<string>, usa: list<string>, exige: list<string>}> */
    public static function permitidos(User $usuario): array
    {
        return array_filter(self::tipos(), fn (array $tipo, string $chave) => Recursos::podeGerarRelatorio($usuario, $chave), ARRAY_FILTER_USE_BOTH);
    }

    public function gerar(string $tipo, ?Aluno $aluno, ?Carbon $de, ?Carbon $ate, ?string $apto): array
    {
        return match ($tipo) {
            'estatisticas' => $this->estatisticas(),
            'apartamento' => $this->alunosDoApartamento((string) $apto),
            'trocas' => $aluno ? $this->trocasDoAluno($aluno, $de, $ate) : $this->trocasGeral($de, $ate),
            'faltas' => $this->faltas($aluno, $de, $ate),
            'pernoites' => $this->pernoites($aluno, $de, $ate),
            'ocorrencias' => $this->ocorrencias($aluno, $de, $ate),
            'atendimentos' => $this->atendimentos($aluno, $de, $ate),
            'ficha' => $this->ficha($aluno),
            default => throw new InvalidArgumentException('Relatório desconhecido.'),
        };
    }

    // ---------- Estatísticas ----------

    private function estatisticas(): array
    {
        $secoes = [];

        $porCategoria = Residencia::query()
            ->select('categoria', DB::raw('count(*) as total'))
            ->groupBy('categoria')
            ->pluck('total', 'categoria');
        $linhas = [];
        foreach (Residencia::CATEGORIAS as $chave => $rotulo) {
            $linhas[] = [$rotulo, (int) ($porCategoria[$chave] ?? 0)];
        }
        $linhas[] = ['TOTAL', (int) $porCategoria->sum()];
        $secoes[] = $this->secao('Residentes e semirresidentes', ['Categoria', 'Alunos'], $linhas);

        $apartamentos = Apartamento::query()->with('alojamento')->withCount('residencias')->orderBy('numero')->get();
        $secoes[] = $this->secao(
            'Por apartamento',
            ['Apartamento', 'Alojamento', 'Alunos', 'Vagas'],
            $apartamentos->map(fn (Apartamento $a) => [$a->numero, $a->alojamento->nome, $a->residencias_count, $a->capacidade])->all()
        );

        // Curso atual do aluno = curso da turma da matrícula mais recente.
        $porCurso = DB::table('residencias as r')
            ->join('alunos as a', 'a.id', '=', 'r.aluno_id')
            ->leftJoin('matriculas as m', function ($join) {
                $join->on('m.aluno_id', '=', 'a.id')
                    ->whereRaw('m.id = (select m2.id from matriculas m2 where m2.aluno_id = a.id order by m2.data_matricula desc, m2.id desc limit 1)');
            })
            ->leftJoin('turmas as t', 't.id', '=', 'm.turma_id')
            ->leftJoin('cursos as c', 'c.id', '=', 't.curso_id')
            ->selectRaw("coalesce(c.nome, 'Sem matrícula') as curso, count(*) as total")
            ->groupByRaw("coalesce(c.nome, 'Sem matrícula')")
            ->orderBy('curso')
            ->get();
        $secoes[] = $this->secao('Por curso', ['Curso', 'Alunos'], $porCurso->map(fn ($l) => [$l->curso, $l->total])->all());

        $porSexo = DB::table('residencias as r')
            ->join('alunos as a', 'a.id', '=', 'r.aluno_id')
            ->selectRaw('a.sexo, count(*) as total')
            ->groupBy('a.sexo')
            ->get();
        $secoes[] = $this->secao(
            'Por sexo',
            ['Sexo', 'Alunos'],
            $porSexo->map(fn ($l) => [Aluno::SEXOS[$l->sexo] ?? $l->sexo, $l->total])->all()
        );

        return $this->relatorio('Estatísticas da residência', [], [], $secoes);
    }

    // ---------- Alunos de um apartamento ----------

    private function alunosDoApartamento(string $numero): array
    {
        $apto = Apartamento::with('alojamento')->where('numero', mb_strtoupper(trim($numero)))->first();
        if (! $apto) {
            throw new InvalidArgumentException("Apartamento \"{$numero}\" não encontrado.");
        }

        $residencias = Residencia::query()
            ->with(['aluno.matriculaAtual.turma'])
            ->where('apartamento_id', $apto->id)
            ->get()
            ->sortBy(fn (Residencia $r) => $r->aluno->nome);

        $linhas = $residencias->map(function (Residencia $r) {
            $aluno = $r->aluno;
            $responsavel = collect([$aluno->nome_responsaveis, $aluno->telefone_familia])->filter()->implode("\n");

            return [
                ['imagem' => $this->fotoComoDados($aluno)],
                $aluno->nome,
                $aluno->matriculaAtual?->turma?->codigo ?? '-',
                $aluno->telefone_estudante ?: '-',
                $responsavel ?: '-',
            ];
        })->values()->all();

        return $this->relatorio(
            "Alunos do apartamento {$apto->numero}",
            ["Alojamento: {$apto->alojamento->nome}"],
            ['Total de alunos: '.count($linhas).' de '.$apto->capacidade.' vagas'],
            [$this->secao(null, ['Foto', 'Nome', 'Turma', 'Telefone do aluno', 'Responsável / telefone'], $linhas)]
        );
    }

    // ---------- Trocas de apartamento ----------

    private function trocasGeral(?Carbon $de, ?Carbon $ate): array
    {
        $trocas = TrocaApartamento::query()
            ->with(['aluno', 'origem', 'destino'])
            ->when($de, fn (Builder $q) => $q->whereDate('data_troca', '>=', $de->toDateString()))
            ->when($ate, fn (Builder $q) => $q->whereDate('data_troca', '<=', $ate->toDateString()))
            ->orderBy('data_troca')->orderBy('id')
            ->get();

        return $this->relatorio(
            'Trocas de apartamento',
            [$this->periodo($de, $ate)],
            ['Total de trocas: '.$trocas->count()],
            [$this->secao(null, ['Data', 'Aluno', 'Saiu do apartamento', 'Foi para'], $trocas->map(fn (TrocaApartamento $t) => [
                $this->data($t->data_troca), $t->aluno->nome, $t->origem?->numero ?? '-', $t->destino?->numero ?? 'sem apartamento',
            ])->all())]
        );
    }

    private function trocasDoAluno(Aluno $aluno, ?Carbon $de, ?Carbon $ate): array
    {
        $residencia = $aluno->residencia()->with('apartamento')->first();
        $historico = TrocaApartamento::query()
            ->with(['origem', 'destino'])
            ->where('aluno_id', $aluno->id)
            ->orderBy('data_troca')->orderBy('id')
            ->get();

        $primeiro = $historico->first()?->origem?->numero ?? $residencia?->apartamento?->numero ?? 'não informado';
        $entrada = $residencia?->data_entrada ? ' (entrada em '.$this->data($residencia->data_entrada).')' : '';

        $noPeriodo = $historico->filter(
            fn (TrocaApartamento $t) => (! $de || $t->data_troca->gte($de)) && (! $ate || $t->data_troca->lte($ate))
        );

        return $this->relatorio(
            'Trocas de apartamento - '.$aluno->nome,
            ['Aluno: '.$aluno->nome.' (CPF '.Formatos::cpf($aluno->cpf).')', $this->periodo($de, $ate)],
            [
                '1º apartamento: '.$primeiro.$entrada,
                'Apartamento atual: '.($residencia?->apartamento?->numero ?? 'sem apartamento'),
            ],
            [$this->secao('Trocas', ['Data', 'Saiu do apartamento', 'Foi para'], $noPeriodo->map(fn (TrocaApartamento $t) => [
                $this->data($t->data_troca), $t->origem?->numero ?? '-', $t->destino?->numero ?? 'sem apartamento',
            ])->values()->all())]
        );
    }

    // ---------- Faltas, pernoites, ocorrências, atendimentos ----------

    private function faltas(?Aluno $aluno, ?Carbon $de, ?Carbon $ate): array
    {
        $faltas = Falta::query()->with('aluno')
            ->when($aluno, fn (Builder $q) => $q->where('aluno_id', $aluno->id))
            ->when($de, fn (Builder $q) => $q->whereDate('data_falta', '>=', $de->toDateString()))
            ->when($ate, fn (Builder $q) => $q->whereDate('data_falta', '<=', $ate->toDateString()))
            ->orderBy('data_falta')->orderBy('id')
            ->get();

        $justificadas = $faltas->where('justificada', true)->count();

        return $this->relatorio(
            'Faltas na residência',
            $this->filtros($aluno, $de, $ate),
            ["Total de faltas: {$faltas->count()} (justificadas: {$justificadas}, não justificadas: ".($faltas->count() - $justificadas).')'],
            [$this->secao(null, ['Data', 'Aluno', 'Situação', 'Observação'], $faltas->map(fn (Falta $f) => [
                $this->data($f->data_falta), $f->aluno->nome, $f->justificada ? 'Justificada' : 'Não justificada', $f->observacao ?: '-',
            ])->all())]
        );
    }

    private function pernoites(?Aluno $aluno, ?Carbon $de, ?Carbon $ate): array
    {
        $lista = Pernoite::query()->with('aluno')
            ->when($aluno, fn (Builder $q) => $q->where('aluno_id', $aluno->id))
            ->when($de, fn (Builder $q) => $q->whereDate('data_pernoite', '>=', $de->toDateString()))
            ->when($ate, fn (Builder $q) => $q->whereDate('data_pernoite', '<=', $ate->toDateString()))
            ->orderBy('data_pernoite')->orderBy('id')
            ->get();

        return $this->relatorio(
            'Autorizações de pernoite',
            $this->filtros($aluno, $de, $ate),
            ['Total de autorizações: '.$lista->count()],
            [$this->secao(null, ['Data', 'Aluno', 'Parcial', 'Forma', 'Autorizado por', 'Justificativa'], $lista->map(fn (Pernoite $p) => [
                $this->data($p->data_pernoite), $p->aluno->nome, $p->parcial ? 'Sim' : 'Não',
                $p->forma_autorizacao ?: '-', $p->quem_autorizou, $p->justificativa,
            ])->all())]
        );
    }

    private function ocorrencias(?Aluno $aluno, ?Carbon $de, ?Carbon $ate): array
    {
        // O escopo de sigilo do model já esconde as ocorrências sigilosas de quem não pode vê-las.
        $lista = Ocorrencia::query()->with('aluno')
            ->when($aluno, fn (Builder $q) => $q->where('aluno_id', $aluno->id))
            ->when($de, fn (Builder $q) => $q->whereDate('data_ocorrencia', '>=', $de->toDateString()))
            ->when($ate, fn (Builder $q) => $q->whereDate('data_ocorrencia', '<=', $ate->toDateString()))
            ->orderBy('data_ocorrencia')->orderBy('id')
            ->get();

        $resumo = ['Total de ocorrências: '.$lista->count()];
        if (! Perfis::veSigilosos(auth()->user())) {
            $resumo[] = 'Ocorrências sigilosas não são exibidas para o seu perfil.';
        }

        return $this->relatorio(
            'Ocorrências disciplinares',
            $this->filtros($aluno, $de, $ate),
            $resumo,
            [$this->secao(null, ['Data', 'Aluno', 'Descrição', 'Sigilosa', 'Advertência', 'Perda de vaga'], $lista->map(fn (Ocorrencia $o) => [
                $this->data($o->data_ocorrencia), $o->aluno->nome, $o->descricao, $o->sigiloso ? 'Sim' : 'Não',
                Ocorrencia::SITUACOES[$o->advertencia] ?? '-', Ocorrencia::SITUACOES[$o->perda_vaga] ?? '-',
            ])->all())]
        );
    }

    private function atendimentos(?Aluno $aluno, ?Carbon $de, ?Carbon $ate): array
    {
        $lista = Atendimento::query()->with('aluno')
            ->when($aluno, fn (Builder $q) => $q->where('aluno_id', $aluno->id))
            ->when($de, fn (Builder $q) => $q->whereDate('data_atendimento', '>=', $de->toDateString()))
            ->when($ate, fn (Builder $q) => $q->whereDate('data_atendimento', '<=', $ate->toDateString()))
            ->orderBy('data_atendimento')->orderBy('id')
            ->get();

        return $this->relatorio(
            'Atendimentos psicossociais',
            $this->filtros($aluno, $de, $ate),
            ['Total de atendimentos: '.$lista->count(), 'Documento sigiloso: uso restrito à equipe autorizada.'],
            [$this->secao(null, ['Data', 'Aluno', 'Forma', 'Servidores', 'Relato'], $lista->map(fn (Atendimento $a) => [
                $this->data($a->data_atendimento).($a->hora_atendimento ? ' '.substr($a->hora_atendimento, 0, 5) : ''),
                $a->aluno->nome, Atendimento::FORMAS[$a->forma] ?? $a->forma, $a->servidores, $a->relato,
            ])->all())]
        );
    }

    // ---------- Ficha do aluno ----------

    private function ficha(Aluno $aluno): array
    {
        $aluno->load(['matriculas.turma.curso', 'residencia.apartamento.alojamento', 'residencia.regime']);
        $r = $aluno->residencia;

        $dados = [
            [['imagem' => $this->fotoComoDados($aluno)], ''],
            ['Nome', $aluno->nome],
            ['CPF', Formatos::cpf($aluno->cpf)],
            ['Sexo', Aluno::SEXOS[$aluno->sexo] ?? $aluno->sexo],
            ['Situação', Aluno::SITUACOES[$aluno->situacao] ?? $aluno->situacao],
            ['E-mail', $aluno->email ?: '-'],
            ['Telefone do aluno', $aluno->telefone_estudante ?: '-'],
            ['Responsáveis', $aluno->nome_responsaveis ?: '-'],
            ['Telefone da família', $aluno->telefone_familia ?: '-'],
            ['Contato de emergência', $aluno->contato_emergencia ?: '-'],
            ['Município', $aluno->municipio ?: '-'],
            ['Programa de benefícios', Aluno::PROGRAMAS[$aluno->programa_beneficios] ?? $aluno->programa_beneficios],
            ['Observações', $aluno->observacoes ?: '-'],
        ];

        $matriculas = $aluno->matriculas->sortByDesc('data_matricula')->map(fn ($m) => [
            $m->numero, $m->turma->codigo, $m->turma->curso->nome, $this->data($m->data_matricula),
            Aluno::SITUACOES[$m->situacao] ?? $m->situacao,
        ])->values()->all();

        $residencia = $r ? [
            ['Categoria', Residencia::CATEGORIAS[$r->categoria] ?? $r->categoria],
            ['Apartamento', $r->apartamento ? $r->apartamento->numero.' ('.$r->apartamento->alojamento->nome.')' : 'sem apartamento'],
            ['Regime', $r->regime?->nome ?? '-'],
            ['Entrada na residência', $r->data_entrada ? $this->data($r->data_entrada) : '-'],
        ] : [['Residência', 'Sem registro de residência']];

        return $this->relatorio(
            'Ficha do aluno - '.$aluno->nome,
            [],
            [],
            [
                $this->secao('Dados cadastrais', ['Campo', 'Valor'], $dados),
                $this->secao('Matrículas', ['Nº', 'Turma', 'Curso', 'Data', 'Situação'], $matriculas),
                $this->secao('Residência', ['Campo', 'Valor'], $residencia),
            ]
        );
    }

    // ---------- Apoio ----------

    /** @param list<string> $filtros @param list<string> $resumo @param list<array> $secoes */
    private function relatorio(string $titulo, array $filtros, array $resumo, array $secoes): array
    {
        return ['titulo' => $titulo, 'filtros' => $filtros, 'resumo' => $resumo, 'secoes' => $secoes];
    }

    /** @param list<string> $colunas @param list<list<mixed>> $linhas */
    private function secao(?string $titulo, array $colunas, array $linhas): array
    {
        return ['titulo' => $titulo, 'colunas' => $colunas, 'linhas' => $linhas];
    }

    /** @return list<string> */
    private function filtros(?Aluno $aluno, ?Carbon $de, ?Carbon $ate): array
    {
        $filtros = [];
        if ($aluno) {
            $filtros[] = 'Aluno: '.$aluno->nome.' (CPF '.Formatos::cpf($aluno->cpf).')';
        }
        $filtros[] = $this->periodo($de, $ate);

        return $filtros;
    }

    private function periodo(?Carbon $de, ?Carbon $ate): string
    {
        if (! $de && ! $ate) {
            return 'Período: todo o histórico';
        }

        return 'Período: '.($de ? $de->format('d/m/Y') : 'início').' a '.($ate ? $ate->format('d/m/Y') : 'hoje');
    }

    private function data(mixed $data): string
    {
        return $data ? Carbon::parse($data)->format('d/m/Y') : '-';
    }

    /** Foto em formato "data URI", que funciona na tela e no PDF sem depender de links externos. */
    private function fotoComoDados(Aluno $aluno): ?string
    {
        $disco = Storage::disk('public');
        if (! $aluno->foto_path || ! $disco->exists($aluno->foto_path)) {
            return null;
        }

        return 'data:'.($disco->mimeType($aluno->foto_path) ?: 'image/jpeg').';base64,'.base64_encode($disco->get($aluno->foto_path));
    }
}
