<?php

namespace Tests\Feature;

use App\Filament\Resources\Alunos\AlunoResource;
use App\Filament\Resources\Cursos\CursoResource;
use App\Filament\Resources\FichaSaudes\FichaSaudeResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Acesso;
use App\Models\Atendimento;
use App\Models\Aluno;
use App\Models\Auditoria;
use App\Models\Curso;
use App\Models\FichaSaude;
use App\Models\Ocorrencia;
use App\Models\User;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PerfilSomenteConsultaTest extends TestCase
{
    private function nivel(string $recurso): string
    {
        return Acesso::where('perfil', Perfis::SOMENTE_CONSULTA)->where('recurso', $recurso)->value('nivel');
    }

    public function test_o_perfil_entra_no_painel(): void
    {
        $usuario = $this->usuarioComPerfil(Perfis::SOMENTE_CONSULTA);

        $this->assertTrue($usuario->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_acesso_inicial_na_tabela_de_acessos(): void
    {
        PermissoesPerfil::sincronizar();

        foreach (['aluno', 'matricula', 'curso', 'serie', 'turma', 'alojamento', 'apartamento', 'regime', 'residencia'] as $recurso) {
            $this->assertSame('consulta', $this->nivel($recurso), "{$recurso} deveria ser só consulta.");
        }

        foreach (['ocorrencia', 'atendimento', 'ficha_saude', 'falta', 'pernoite', 'ata', 'troca_apartamento'] as $recurso) {
            $this->assertSame('nenhum', $this->nivel($recurso), "{$recurso} deveria ficar sem acesso.");
        }
    }

    public function test_consulta_sim_edicao_e_dados_sensiveis_nao(): void
    {
        $usuario = $this->usuarioComPerfil(Perfis::SOMENTE_CONSULTA); // sem linhas na tabela: vale o padrão

        $this->assertTrue(Gate::forUser($usuario)->allows('viewAny', Aluno::class));
        $this->assertTrue(Gate::forUser($usuario)->allows('viewAny', Curso::class));

        $this->assertFalse(Gate::forUser($usuario)->allows('create', Aluno::class));
        $this->assertFalse(Gate::forUser($usuario)->allows('create', Curso::class));

        foreach ([FichaSaude::class, Atendimento::class, Ocorrencia::class] as $modelo) {
            $this->assertFalse(Gate::forUser($usuario)->allows('viewAny', $modelo), "{$modelo} não deveria ser visível.");
        }
    }

    public function test_as_telas_obedecem_ao_perfil(): void
    {
        PermissoesPerfil::sincronizar();
        $this->entrarNoPainel(Perfis::SOMENTE_CONSULTA);

        $this->get(AlunoResource::getUrl('index'))->assertOk();
        $this->get(CursoResource::getUrl('index'))->assertOk();
        $this->get(CursoResource::getUrl('create'))->assertForbidden();
        $this->get(FichaSaudeResource::getUrl('index'))->assertForbidden();
    }

    public function test_relatorios_permitidos_e_negados(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::SOMENTE_CONSULTA));
        $aluno = $this->criarAluno();

        $this->get(route('relatorios.gerar', ['tipo' => 'estatisticas']))->assertOk();
        $this->get(route('relatorios.gerar', ['tipo' => 'ficha', 'cpf' => $aluno->cpf]))->assertOk();

        foreach (['faltas', 'pernoites', 'trocas', 'ocorrencias', 'atendimentos'] as $tipo) {
            $this->get(route('relatorios.gerar', ['tipo' => $tipo]))->assertForbidden();
        }
    }

    public function test_o_administrador_cria_um_usuario_com_o_perfil(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $perfil = Role::findOrCreate(Perfis::SOMENTE_CONSULTA, 'web');

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Pessoa de Consulta', 'email' => 'consulta@exemplo.com', 'roles' => [$perfil->id],
                'ativo' => true, 'password' => 'Senha1234', 'password_confirmation' => 'Senha1234',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(User::where('email', 'consulta@exemplo.com')->firstOrFail()->hasRole(Perfis::SOMENTE_CONSULTA));

        $registro = Auditoria::where('entidade', 'Usuário')->get()
            ->first(fn (Auditoria $r) => isset($r->alteracoes['detalhes']['perfil']));
        $this->assertSame('(nenhum) → SomenteConsulta', $registro->alteracoes['detalhes']['perfil']);
    }

    public function test_o_sigilo_continua_valendo_mesmo_se_o_administrador_liberar_o_cadastro(): void
    {
        PermissoesPerfil::sincronizar();
        Acesso::where('perfil', Perfis::SOMENTE_CONSULTA)->where('recurso', 'ocorrencia')->update(['nivel' => 'consulta']);
        PermissoesPerfil::limparCache();

        $aluno = $this->criarAluno();
        Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-10', 'descricao' => 'comum', 'sigiloso' => false]);
        Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-11', 'descricao' => 'sigilosa', 'sigiloso' => true]);

        $this->actingAs($this->usuarioComPerfil(Perfis::SOMENTE_CONSULTA));

        $this->assertTrue(Gate::allows('viewAny', Ocorrencia::class));
        $this->assertSame(1, Ocorrencia::count(), 'A ocorrência sigilosa não pode aparecer para este perfil.');
    }
}