<?php

namespace Tests\Feature;

use App\Filament\Resources\Cursos\CursoResource;
use App\Models\Acesso;
use App\Models\Auditoria;
use App\Models\Curso;
use App\Models\Ocorrencia;
use App\Models\TrocaApartamento;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use App\Support\Recursos;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PermissoesPerfilTest extends TestCase
{
    private function acesso(string $perfil, string $recurso): Acesso
    {
        return Acesso::where('perfil', $perfil)->where('recurso', $recurso)->firstOrFail();
    }

    public function test_sincronizar_cria_uma_linha_por_perfil_e_cadastro(): void
    {
        $criadas = PermissoesPerfil::sincronizar();

        $this->assertSame(count(Recursos::perfisEditaveis()) * count(Recursos::todos()), $criadas);
        $this->assertSame($criadas, Acesso::count());
        $this->assertSame(0, PermissoesPerfil::sincronizar(), 'A segunda sincronização não deveria criar nada.');
    }

    public function test_a_tabela_nasce_com_o_padrao_que_cada_perfil_ja_tinha(): void
    {
        PermissoesPerfil::sincronizar();

        $this->assertSame('edicao', $this->acesso(Perfis::RESIDENCIA, 'curso')->nivel);
        $this->assertSame('nenhum', $this->acesso(Perfis::DAE_CENTRAL, 'curso')->nivel);
        $this->assertSame('consulta', $this->acesso(Perfis::DAE_CENTRAL, 'aluno')->nivel);
        $this->assertSame('edicao', $this->acesso(Perfis::PSICOSSOCIAL, 'ficha_saude')->nivel);
        $this->assertSame('edicao', $this->acesso(Perfis::DAE_CENTRAL, 'ata')->nivel);
        $this->assertSame('consulta', $this->acesso(Perfis::RESIDENCIA, 'troca_apartamento')->nivel);
    }

    public function test_sem_linhas_na_tabela_vale_o_padrao(): void
    {
        $residencia = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $dae = $this->usuarioComPerfil(Perfis::DAE_CENTRAL);

        $this->assertTrue(Gate::forUser($residencia)->allows('create', Curso::class));
        $this->assertFalse(Gate::forUser($dae)->allows('viewAny', Curso::class));
    }

    public function test_mudar_o_acesso_na_tabela_muda_a_permissao_na_hora(): void
    {
        PermissoesPerfil::sincronizar();
        $psico = $this->usuarioComPerfil(Perfis::PSICOSSOCIAL);

        $this->assertFalse(Gate::forUser($psico)->allows('viewAny', Curso::class));

        $this->acesso(Perfis::PSICOSSOCIAL, 'curso')->update(['nivel' => 'consulta']);
        $this->assertTrue(Gate::forUser($psico)->allows('viewAny', Curso::class));
        $this->assertFalse(Gate::forUser($psico)->allows('create', Curso::class));

        $this->acesso(Perfis::PSICOSSOCIAL, 'curso')->update(['nivel' => 'edicao']);
        $this->assertTrue(Gate::forUser($psico)->allows('create', Curso::class));

        $this->acesso(Perfis::PSICOSSOCIAL, 'curso')->update(['nivel' => 'nenhum']);
        $this->assertFalse(Gate::forUser($psico)->allows('viewAny', Curso::class));
    }

    public function test_tirar_o_acesso_de_um_perfil_funciona(): void
    {
        PermissoesPerfil::sincronizar();
        $residencia = $this->usuarioComPerfil(Perfis::RESIDENCIA);

        $this->assertTrue(Gate::forUser($residencia)->allows('create', Curso::class));

        $this->acesso(Perfis::RESIDENCIA, 'curso')->update(['nivel' => 'nenhum']);

        $this->assertFalse(Gate::forUser($residencia)->allows('viewAny', Curso::class));
        $this->assertFalse(Gate::forUser($residencia)->allows('create', Curso::class));
    }

    public function test_o_administrador_sempre_tem_acesso(): void
    {
        PermissoesPerfil::sincronizar();
        Acesso::query()->get()->each->update(['nivel' => 'nenhum']);

        $admin = $this->usuarioComPerfil(Perfis::ADMIN);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Curso::class));
        $this->assertTrue(Gate::forUser($admin)->allows('create', Curso::class));
    }

    public function test_so_o_administrador_gerencia_os_acessos(): void
    {
        PermissoesPerfil::sincronizar();
        $linha = $this->acesso(Perfis::RESIDENCIA, 'curso');

        $admin = $this->usuarioComPerfil(Perfis::ADMIN);
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Acesso::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $linha));
        $this->assertFalse(Gate::forUser($admin)->allows('create', Acesso::class));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $linha));

        foreach (Recursos::perfisEditaveis() as $perfil) {
            $usuario = $this->usuarioComPerfil($perfil);
            $this->assertFalse(Gate::forUser($usuario)->allows('viewAny', Acesso::class), "{$perfil} não deveria ver os acessos.");
            $this->assertFalse(Gate::forUser($usuario)->allows('update', $linha));
        }
    }

    public function test_o_historico_de_trocas_nunca_e_editavel_mesmo_na_tabela(): void
    {
        PermissoesPerfil::sincronizar();
        $this->assertArrayNotHasKey('edicao', Recursos::niveisPermitidos('troca_apartamento'));

        $this->acesso(Perfis::RESIDENCIA, 'troca_apartamento')->update(['nivel' => 'edicao']);

        $residencia = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $this->assertFalse(Gate::forUser($residencia)->allows('create', TrocaApartamento::class));
    }

    public function test_as_telas_obedecem_a_tabela_de_acessos(): void
    {
        PermissoesPerfil::sincronizar();
        $this->entrarNoPainel(Perfis::DAE_CENTRAL);

        $this->get(CursoResource::getUrl('index'))->assertForbidden();

        $this->acesso(Perfis::DAE_CENTRAL, 'curso')->update(['nivel' => 'consulta']);
        $this->get(CursoResource::getUrl('index'))->assertOk();

        $this->acesso(Perfis::DAE_CENTRAL, 'curso')->update(['nivel' => 'nenhum']);
        $this->get(CursoResource::getUrl('index'))->assertForbidden();
    }

    public function test_o_sigilo_continua_valendo_com_qualquer_nivel_de_acesso(): void
    {
        PermissoesPerfil::sincronizar();
        $this->acesso(Perfis::RESIDENCIA, 'ocorrencia')->update(['nivel' => 'edicao']);

        $aluno = $this->criarAluno();
        Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-10', 'descricao' => 'comum', 'sigiloso' => false]);
        Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-11', 'descricao' => 'sigilosa', 'sigiloso' => true]);

        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->assertSame(1, Ocorrencia::count());
    }

    public function test_a_mudanca_de_acesso_entra_na_auditoria(): void
    {
        PermissoesPerfil::sincronizar();
        $this->actingAs($this->usuarioComPerfil(Perfis::ADMIN));

        $this->acesso(Perfis::PSICOSSOCIAL, 'curso')->update(['nivel' => 'consulta']);

        $registro = Auditoria::where('entidade', 'Permissão de perfil')->where('evento', 'alterado')->firstOrFail();
        $this->assertSame('nenhum', $registro->alteracoes['mudancas']['nivel']['de']);
        $this->assertSame('consulta', $registro->alteracoes['mudancas']['nivel']['para']);
        $this->assertStringContainsString('Psicossocial', $registro->descricao);
        $this->assertStringContainsString('Cursos', $registro->descricao);
    }
}