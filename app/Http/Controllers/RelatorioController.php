<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Ata;
use App\Services\RelatorioService;
use App\Support\Formatos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class RelatorioController
{
    public function __construct(private RelatorioService $servico)
    {
    }

    public function index()
    {
        $tipos = RelatorioService::permitidos(auth()->user());
        abort_if($tipos === [], 403);

        return view('relatorios.index', ['tipos' => $tipos]);
    }

    public function gerar(Request $request)
    {
        $dados = $request->validate([
            'tipo' => ['required', 'string'],
            'cpf' => ['nullable', 'string', 'max:20'],
            'apto' => ['nullable', 'string', 'max:10'],
            'de' => ['nullable', 'date'],
            'ate' => ['nullable', 'date', 'after_or_equal:de'],
            'formato' => ['nullable', 'in:tela,pdf'],
        ], [
            'ate.after_or_equal' => 'A data final não pode ser anterior à data inicial.',
            'de.date' => 'Data inicial inválida.',
            'ate.date' => 'Data final inválida.',
        ]);

        $tipos = RelatorioService::permitidos(auth()->user());
        abort_unless(isset($tipos[$dados['tipo']]), 403);
        $config = $tipos[$dados['tipo']];

        $aluno = null;
        $cpf = preg_replace('/\D/', '', (string) ($dados['cpf'] ?? ''));
        if (in_array('aluno', $config['usa'], true) && $cpf !== '') {
            $aluno = Aluno::where('cpf', $cpf)->first();
            if (! $aluno) {
                return back()->withInput()->withErrors(['cpf' => 'Aluno não encontrado para o CPF informado.']);
            }
        }
        if (in_array('aluno', $config['exige'], true) && ! $aluno) {
            return back()->withInput()->withErrors(['cpf' => 'Informe o CPF do aluno para este relatório.']);
        }
        if (in_array('apto', $config['exige'], true) && blank($dados['apto'] ?? null)) {
            return back()->withInput()->withErrors(['apto' => 'Informe o número do apartamento.']);
        }

        $de = filled($dados['de'] ?? null) && in_array('periodo', $config['usa'], true)
            ? Carbon::parse($dados['de'])->startOfDay() : null;
        $ate = filled($dados['ate'] ?? null) && in_array('periodo', $config['usa'], true)
            ? Carbon::parse($dados['ate'])->startOfDay() : null;

        try {
            $relatorio = $this->servico->gerar($dados['tipo'], $aluno, $de, $ate, $dados['apto'] ?? null);
            \App\Models\Auditoria::registrar('gerou', 'Relatório', null, $config['rotulo'], ['detalhes' => [
                'aluno' => $aluno?->nome,
                'de' => $de?->toDateString(),
                'ate' => $ate?->toDateString(),
                'formato' => $dados['formato'] ?? 'tela',
            ]]);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['tipo' => $e->getMessage()]);
        }

        if (($dados['formato'] ?? 'tela') === 'pdf') {
            $colunas = collect($relatorio['secoes'])->max(fn (array $secao) => count($secao['colunas'])) ?? 0;

            return Pdf::loadView('relatorios.documento', ['relatorio' => $relatorio])
                ->setPaper('a4', $colunas >= 5 ? 'landscape' : 'portrait')
                ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false])
                ->stream('relatorio-' . $dados['tipo'] . '.pdf');
        }

        return view('relatorios.tela', ['relatorio' => $relatorio]);
    }

    /** Sugestões de aluno (por parte do nome ou do CPF) para os filtros dos relatórios. */
    public function alunos(Request $request)
    {
        abort_if(RelatorioService::permitidos(auth()->user()) === [], 403);
        Gate::authorize('viewAny', Aluno::class);

        // Tira os curingas do LIKE para o texto digitado ser sempre uma busca literal.
        $termo = trim(str_replace(['%', '_', '\\'], '', (string) $request->query('q', '')));
        if (mb_strlen($termo) < 2) {
            return response()->json([]);
        }

        $digitos = preg_replace('/\D/', '', $termo);

        $alunos = Aluno::query()
            ->where(function ($consulta) use ($termo, $digitos) {
                $consulta->where('nome', 'like', "%{$termo}%");
                if ($digitos !== '') {
                    $consulta->orWhere('cpf', 'like', "%{$digitos}%");
                }
            })
            ->orderBy('nome')
            ->limit(10)
            ->get(['id', 'nome', 'cpf']);

        return response()->json($alunos->map(fn (Aluno $aluno) => [
            'nome' => $aluno->nome,
            'cpf' => $aluno->cpf,
            'cpf_formatado' => Formatos::cpf($aluno->cpf),
        ])->values());
    }

    public function ata(Ata $ata)
    {
        Gate::authorize('view', $ata);

        $ata->load(['alunos' => fn ($consulta) => $consulta->orderBy('nome')]);

        return Pdf::loadView('relatorios.ata', ['ata' => $ata])
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false])
            ->stream('ata-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $ata->numero) . '.pdf');
    }
}