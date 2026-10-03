<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Registro de auditoria. O sistema só inclui registros: não altera nem apaga nenhum.
 * Falhas ao gravar a auditoria são reportadas, mas não impedem a operação do usuário.
 */
class Auditoria extends Model
{
    public $timestamps = false;

    protected $table = 'auditorias';

    public const EVENTOS = [
        'criado' => 'Criado',
        'alterado' => 'Alterado',
        'excluido' => 'Excluído',
        'baixou' => 'Baixou anexo',
        'gerou' => 'Gerou relatório',
        'entrou' => 'Entrou',
        'consultou' => 'Consultou',
        'falhou' => 'Falha de login',
        'alterou_senha' => 'Alterou a senha',
    ];

    /** Nomes mostrados para cada model. */
    public const ENTIDADES = [
        'Aluno' => 'Aluno',
        'Curso' => 'Curso',
        'Turma' => 'Turma',
        'Serie' => 'Série',
        'Alojamento' => 'Alojamento',
        'Apartamento' => 'Apartamento',
        'Regime' => 'Regime',
        'Matricula' => 'Matrícula',
        'Residencia' => 'Residência',
        'Falta' => 'Falta',
        'Pernoite' => 'Autorização de pernoite',
        'Ocorrencia' => 'Ocorrência disciplinar',
        'Atendimento' => 'Atendimento psicossocial',
        'FichaSaude' => 'Ficha de saúde',
        'Ata' => 'Ata de reunião',
        'User' => 'Usuário',
        'Acesso' => 'Permissão de perfil',
    ];

    /** Tabelas cujo conteúdo nunca vai para o log: só os nomes dos campos alterados. */
    public const TABELAS_SENSIVEIS = ['atendimentos', 'fichas_saude', 'ocorrencias'];

    /** Campos cujo conteúdo é ocultado em tabelas que não são sensíveis por inteiro. */
    public const CAMPOS_SIGILOSOS = ['alunos' => ['observacoes']];

    /** Campos que não valem a pena registrar. */
    private const IGNORAR = ['id', 'created_at', 'updated_at', 'user_id', 'remember_token', 'password', 'tema'];

    protected $fillable = [
        'user_id', 'user_nome', 'evento', 'entidade', 'registro_id', 'descricao', 'alteracoes', 'ip', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'alteracoes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Auditoria $auditoria): void {
            $auditoria->created_at ??= now();
        });

        // Registro de auditoria só é incluído.
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    /** Registra um evento avulso (download, relatório, acesso etc.). */
    public static function registrar(
        string $evento,
        string $entidade,
        ?int $registroId = null,
        ?string $descricao = null,
        ?array $alteracoes = null,
    ): void {
        try {
            $usuario = auth()->user();

            static::create([
                'user_id' => $usuario?->getAuthIdentifier(),
                'user_nome' => $usuario?->name ?? 'sistema (console)',
                'evento' => $evento,
                'entidade' => $entidade,
                'registro_id' => $registroId,
                'descricao' => $descricao !== null ? Str::limit($descricao, 250) : null,
                'alteracoes' => $alteracoes,
                'ip' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (\Throwable $erro) {
            report($erro);
        }
    }

    /** Registra a criação, alteração ou exclusão de um registro de cadastro. */
    public static function registrarModelo(Model $modelo, string $evento): void
    {
        $tabela = $modelo->getTable();
        $sensivel = in_array($tabela, self::TABELAS_SENSIVEIS, true);
        $sigilosos = self::CAMPOS_SIGILOSOS[$tabela] ?? [];
        $alteracoes = null;

        if ($evento === 'criado') {
            $valores = Arr::except($modelo->getAttributes(), self::IGNORAR);
            $alteracoes = $sensivel
                ? ['campos' => array_keys($valores)]
                : ['valores' => array_map(
                    fn ($valor, $campo) => in_array($campo, $sigilosos, true) ? '[oculto]' : self::resumirValor($valor),
                    $valores,
                    array_keys($valores)
                )];
            if (! $sensivel) {
                $alteracoes['valores'] = array_combine(array_keys($valores), $alteracoes['valores']);
            }
        } elseif ($evento === 'alterado') {
            $mudancas = Arr::except($modelo->getChanges(), self::IGNORAR);
            if ($mudancas === []) {
                return; // só mudou data de atualização ou quem atualizou
            }
            if ($sensivel) {
                $alteracoes = ['campos' => array_keys($mudancas)];
            } else {
                $detalhes = [];
                foreach ($mudancas as $campo => $novo) {
                    $oculto = in_array($campo, $sigilosos, true);
                    $detalhes[$campo] = [
                        'de' => $oculto ? '[oculto]' : self::resumirValor($modelo->getOriginal($campo)),
                        'para' => $oculto ? '[oculto]' : self::resumirValor($novo),
                    ];
                }
                $alteracoes = ['mudancas' => $detalhes];
            }
        }

        self::registrar(
            $evento,
            self::ENTIDADES[class_basename($modelo)] ?? class_basename($modelo),
            (int) $modelo->getKey(),
            self::descrever($modelo),
            $alteracoes,
        );
    }

    /** Texto curto dos detalhes, para a lista do painel. */
    public function resumo(): string
    {
        $dados = $this->alteracoes ?? [];
        $partes = [];

        if (isset($dados['campos'])) {
            $partes[] = 'Campos: ' . implode(', ', $dados['campos']);
        }
        foreach ($dados['mudancas'] ?? [] as $campo => $valores) {
            $partes[] = $campo . ': ' . ($valores['de'] ?? 'vazio') . ' → ' . ($valores['para'] ?? 'vazio');
        }
        foreach ($dados['valores'] ?? [] as $campo => $valor) {
            $partes[] = $campo . ': ' . ($valor ?? 'vazio');
        }
        foreach ($dados['detalhes'] ?? [] as $campo => $valor) {
            $partes[] = $campo . ': ' . (is_scalar($valor) || $valor === null ? ($valor ?? 'vazio') : json_encode($valor));
        }

        return implode(' | ', $partes);
    }

    private static function resumirValor(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        return Str::limit(is_scalar($valor) ? (string) $valor : (string) json_encode($valor), 300);
    }

    private static function descrever(Model $modelo): string
    {
        foreach (['nome', 'codigo', 'numero', 'assunto'] as $campo) {
            if (filled($modelo->getAttribute($campo))) {
                return (string) $modelo->getAttribute($campo);
            }
        }

        if ($modelo->getAttribute('aluno_id')) {
            $aluno = Aluno::find($modelo->getAttribute('aluno_id'));
            if ($aluno) {
                return 'Aluno: ' . $aluno->nome;
            }
        }

        return '#' . $modelo->getKey();
    }
}