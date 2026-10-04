<?php

namespace Tests\Feature;

use App\Filament\Resources\Perfis\Pages\CreatePerfil;
use App\Filament\Resources\Perfis\Pages\EditPerfil;
use App\Filament\Resources\Perfis\Pages\ListPerfis;
use App\Models\Acesso;
use App\Models\Curso;
use App\Models\Ocorrencia;
use App\Models\Perfil;
use App\Models\User;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use App\Support\Recursos;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PerfilCadastroTest extends TestCase
{
    private function criarPerfil(array $dados = []): Perfil
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return Perfil::create(array_merge(['rotulo' => 'Assistência Social'], $dados));
    }

    private function usuarioDoPerfil(Perfil $perfil): User
    {
        $usuario = User::factory()->create();
        $usuario->assignRole($perfil->name);

        return $usuario->fresh();
    }

    private function nivel(string $perfil, string $recurso): string
    {
        return Acesso::where('perfil', $perfil)->where('recurso', $recurso)->value('nivel');
    }

    public function test_administrador_cria_perfil_que_comeca_sem_acesso(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        Livewire::test(CreatePerfil::class)
            ->fillForm(['rotulo' => 'Assistência Social', 'ativo' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $perfil = Perfil::where('rotulo', 'Assistência Social')->firstOrFail();
        $this->assertSame('assistencia_social', $perfil->name);
        $this->assertContains('assistencia_social', Recursos::perfisEditaveis());
        $this->assertSame(count(Recursos::todos()), Acesso::where('perfil', 'assistencia_social')->count());
        $this->assertSame(0, Acesso::where('perfil', 'assistencia_social')->where('nivel', '!=', PermissoesPerfil::NENHUM)->count());
    }

    public function test_nome_repetido_e_recusado_e_codigo_nao_se_repete(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $this->criarPerfil(['rotulo' => 'Coordenação']);

        Livewire::test(CreatePerfil::class)
            ->fillForm(['rotulo' => 'Coordenação'])
            ->call('create')
            ->assertHasFormErrors(['rotulo' => 'unique']);

        $this->assertSame('coordenacao_2', $this->criarPerfil(['rotulo' => 'Coordenacao'])->name);
    }

    public function test_perfil_novo_pode_copiar_os_acessos_de_outro(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        Livewire::test(CreatePerfil::class)
            ->fillForm(['rotulo' => 'Apoio à Residência', 'copiar_de' => Perfis::RESIDENCIA])
            ->call('create')
            ->assertHasNoFormErrors();

        $codigo = Perfil::where('rotulo', 'Apoio à Residência')->value('name');
        foreach (Recursos::todos() as $recurso => $dados) {
            $this->assertSame($this->nivel(Perfis::RESIDENCIA, $recurso), $this->nivel($codigo, $recurso), $recurso);
        }
        $this->assertTrue(Gate::forUser($this->usuarioDoPerfil(Perfil::where('name', $codigo)->first()))->allows('create', Curso::class));
    }

    public function test_acesso_do_perfil_cadastrado_segue_a_tela_de_acessos(): void
    {
        $perfil = $this->criarPerfil();
        PermissoesPerfil::sincronizar();
        $usuario = $this->usuarioDoPerfil($perfil);

        $this->assertTrue($usuario->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse(Gate::forUser($usuario)->allows('viewAny', Curso::class));

        Acesso::where('perfil', $perfil->name)->where('recurso', 'curso')->first()->update(['nivel' => PermissoesPerfil::CONSULTA]);

        $this->assertTrue(Gate::forUser($usuario)->allows('viewAny', Curso::class));
        $this->assertFalse(Gate::forUser($usuario)->allows('create', Curso::class));
    }

    public function test_sigilosos_so_aparecem_para_perfil_marcado(): void
    {
        $aluno = $this->criarAluno();
        Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-10', 'descricao' => 'comum', 'sigiloso' => false]);
        Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-11', 'descricao' => 'sigilosa', 'sigiloso' => true]);

        $this->actingAs($this->usuarioDoPerfil($this->criarPerfil(['rotulo' => 'Sem sigilo'])));
        $this->assertSame(['comum'], Ocorrencia::pluck('descricao')->all());

        $this->actingAs($this->usuarioDoPerfil($this->criarPerfil(['rotulo' => 'Com sigilo', 've_sigilosos' => true])));
        $this->assertSame(['comum', 'sigilosa'], Ocorrencia::orderBy('id')->pluck('descricao')->all());
    }

    public function test_perfil_inativo_tira_o_acesso_e_encerra_as_sessoes(): void
    {
        $perfil = $this->criarPerfil(['ve_sigilosos' => true]);
        PermissoesPerfil::sincronizar();
        Acesso::where('perfil', $perfil->name)->where('recurso', 'curso')->first()->update(['nivel' => PermissoesPerfil::EDICAO]);
        $usuario = $this->usuarioDoPerfil($perfil);
        DB::table('sessions')->insert([
            'id' => 'sessao-de-teste', 'user_id' => $usuario->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'teste', 'payload' => 'x', 'last_activity' => time(),
        ]);

        $perfil->update(['ativo' => false]);
        $usuario = $usuario->fresh();

        $this->assertDatabaseMissing('sessions', ['id' => 'sessao-de-teste']);
        $this->assertFalse($usuario->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse(Gate::forUser($usuario)->allows('viewAny', Curso::class));
        $this->assertFalse(Perfis::veSigilosos($usuario));
    }

    public function test_exclusao_bloqueada_para_perfil_original_ou_com_usuarios(): void
    {
        $admin = $this->usuarioComPerfil(Perfis::ADMIN);
        $original = Perfil::findOrCreate(Perfis::RESIDENCIA, 'web');
        $comUsuario = $this->criarPerfil(['rotulo' => 'Com usuário']);
        $this->usuarioDoPerfil($comUsuario);
        $vazio = $this->criarPerfil(['rotulo' => 'Vazio']);
        PermissoesPerfil::sincronizar();

        $this->assertFalse(Gate::forUser($admin)->allows('delete', Perfil::findByName(Perfis::ADMIN)));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', Perfil::find($original->id)));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $comUsuario));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $vazio));

        $vazio->delete();

        $this->assertSame(0, Acesso::where('perfil', $vazio->name)->count());
    }

    public function test_so_o_administrador_gerencia_perfis(): void
    {
        $this->assertTrue(Gate::forUser($this->usuarioComPerfil(Perfis::ADMIN))->allows('viewAny', Perfil::class));

        foreach ([Perfis::DAE_CENTRAL, Perfis::RESIDENCIA, Perfis::PSICOSSOCIAL, Perfis::SOMENTE_CONSULTA] as $perfil) {
            $this->assertFalse(Gate::forUser($this->usuarioComPerfil($perfil))->allows('viewAny', Perfil::class), $perfil);
        }
    }

    public function test_perfil_administrador_nao_pode_ser_inativado_pela_tela(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $admin = Perfil::findByName(Perfis::ADMIN);

        Livewire::test(EditPerfil::class, ['record' => $admin->getRouteKey()])
            ->assertFormFieldIsDisabled('ativo');
    }

    public function test_lista_mostra_perfis_com_nome_e_quantidade_de_usuarios(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $perfil = $this->criarPerfil();
        $this->usuarioDoPerfil($perfil);
        $renomeado = Perfil::findOrCreate(Perfis::DAE_CENTRAL, 'web');
        $renomeado->update(['rotulo' => 'Diretoria de Assuntos Estudantis']);

        Livewire::test(ListPerfis::class)
            ->assertCanSeeTableRecords([$perfil, $renomeado])
            ->assertTableColumnStateSet('users_count', 1, $perfil)
            ->assertSee('Diretoria de Assuntos Estudantis');

        $this->assertSame('Diretoria de Assuntos Estudantis', Perfis::rotulo(Perfis::DAE_CENTRAL));
    }
}
