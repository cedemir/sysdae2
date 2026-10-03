<?php

namespace App\Listeners;

use App\Models\Atendimento;
use App\Models\Auditoria;
use App\Models\FichaSaude;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;

/**
 * Registra na auditoria quem abriu telas com dados sensíveis.
 * O Laravel descobre este listener sozinho (pelo tipo do evento) e o chama no fim de cada requisição.
 */
class RegistrarConsultaSensivel
{
    /**
     * Telas monitoradas: prefixo do nome da rota => [nome na auditoria, model].
     * Para ver os nomes das rotas: php artisan route:list --name=filament.admin.resources
     */
    private const ALVOS = [
        'filament.admin.resources.ficha-saudes' => ['Ficha de saúde', FichaSaude::class],
        'filament.admin.resources.atendimentos' => ['Atendimento psicossocial', Atendimento::class],
        // Para monitorar também as ocorrências disciplinares, retire o comentário da linha abaixo:
        // 'filament.admin.resources.ocorrencias' => ['Ocorrência disciplinar', \App\Models\Ocorrencia::class],
    ];

    /** A mesma pessoa abrindo a mesma tela de novo dentro deste prazo conta uma vez só. */
    private const MINUTOS_SEM_REPETIR = 5;

    public function handle(RequestHandled $evento): void
    {
        $requisicao = $evento->request;

        // Só o carregamento da página (GET) com acesso permitido; busca e paginação usam POST.
        if (! $requisicao->isMethod('GET') || ! $evento->response->isSuccessful()) {
            return;
        }

        $nomeRota = $requisicao->route()?->getName();
        if (! $nomeRota || ! str_starts_with($nomeRota, 'filament.admin.resources.') || ! auth()->check()) {
            return;
        }

        try {
            $this->registrar($nomeRota, $requisicao);
        } catch (\Throwable $erro) {
            report($erro); // a auditoria nunca pode derrubar a tela do usuário
        }
    }

    private function registrar(string $nomeRota, Request $requisicao): void
    {
        foreach (self::ALVOS as $prefixo => [$entidade, $modelo]) {
            if (! str_starts_with($nomeRota, $prefixo . '.')) {
                continue;
            }

            $pagina = substr($nomeRota, strlen($prefixo) + 1);
            if (! in_array($pagina, ['index', 'view', 'edit'], true)) {
                return; // cadastrar um registro novo não é consulta
            }

            $registroId = null;
            $descricao = 'Lista';

            if ($pagina !== 'index') {
                $registro = $modelo::with('aluno')->find($requisicao->route('record'));
                if (! $registro) {
                    return;
                }
                $registroId = (int) $registro->getKey();
                $descricao = 'Aluno: ' . ($registro->aluno?->nome ?? '-');
            }

            if ($this->jaRegistrado($entidade, $registroId)) {
                return;
            }

            Auditoria::registrar('consultou', $entidade, $registroId, $descricao, [
                'detalhes' => ['pagina' => match ($pagina) {
                    'index' => 'lista',
                    'edit' => 'edição',
                    default => 'visualização',
                }],
            ]);

            return;
        }
    }

    private function jaRegistrado(string $entidade, ?int $registroId): bool
    {
        return Auditoria::query()
            ->where('user_id', auth()->id())
            ->where('evento', 'consultou')
            ->where('entidade', $entidade)
            ->when(
                $registroId,
                fn ($consulta) => $consulta->where('registro_id', $registroId),
                fn ($consulta) => $consulta->whereNull('registro_id'),
            )
            ->where('created_at', '>=', now()->subMinutes(self::MINUTOS_SEM_REPETIR))
            ->exists();
    }
}