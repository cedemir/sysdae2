<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\FichaSaude;
use App\Support\Perfis;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Tests\TestCase;

class AuditoriaTest extends TestCase
{
    public function test_criar_e_alterar_um_aluno_gera_registros(): void
    {
        $admin = $this->usuarioComPerfil(Perfis::ADMIN);
        $this->actingAs($admin);

        $aluno = $this->criarAluno(['nome' => 'Nome Antigo']);

        $this->assertDatabaseHas('auditorias', [
            'evento' => 'criado', 'entidade' => 'Aluno', 'registro_id' => $aluno->id, 'user_nome' => $admin->name,
        ]);

        $aluno->update(['nome' => 'Nome Novo']);

        $registro = Auditoria::where('evento', 'alterado')->where('registro_id', $aluno->id)->latest('id')->firstOrFail();
        $this->assertSame('Nome Antigo', $registro->alteracoes['mudancas']['nome']['de']);
        $this->assertSame('Nome Novo', $registro->alteracoes['mudancas']['nome']['para']);
    }

    public function test_observacoes_do_aluno_ficam_ocultas_no_log(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::ADMIN));
        $aluno = $this->criarAluno();

        $aluno->update(['observacoes' => 'Informacao reservada do aluno']);

        $registro = Auditoria::where('evento', 'alterado')->where('registro_id', $aluno->id)->latest('id')->firstOrFail();
        $this->assertSame('[oculto]', $registro->alteracoes['mudancas']['observacoes']['para']);
        $this->assertStringNotContainsString('Informacao reservada', json_encode($registro->alteracoes));
    }

    public function test_dados_sensiveis_guardam_so_os_nomes_dos_campos(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::PSICOSSOCIAL));
        $aluno = $this->criarAluno();

        $ficha = FichaSaude::create(['aluno_id' => $aluno->id, 'alergias' => 'dipirona']);
        $ficha->update(['alergias' => 'penicilina']);

        $registros = Auditoria::where('entidade', 'Ficha de saúde')->get();
        $this->assertCount(2, $registros);

        foreach ($registros as $registro) {
            $texto = json_encode($registro->alteracoes);
            $this->assertStringNotContainsString('dipirona', $texto);
            $this->assertStringNotContainsString('penicilina', $texto);
        }

        $this->assertContains('alergias', $registros->last()->alteracoes['campos']);
    }

    public function test_registro_de_auditoria_nao_pode_ser_alterado_nem_apagado(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::ADMIN));
        $this->criarAluno();

        $registro = Auditoria::firstOrFail();

        $this->assertFalse($registro->update(['evento' => 'adulterado']));
        $this->assertFalse($registro->delete());
        $this->assertNotSame('adulterado', $registro->fresh()->evento);
        $this->assertDatabaseCount('auditorias', Auditoria::count());
    }

    public function test_entrada_no_sistema_e_registrada(): void
    {
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $this->actingAs($usuario);

        event(new Login('web', $usuario, false));

        $this->assertDatabaseHas('auditorias', ['evento' => 'entrou', 'descricao' => $usuario->email]);
    }

    public function test_falha_de_login_nao_guarda_a_senha(): void
    {
        event(new Failed('web', null, ['email' => 'alguem@exemplo.com', 'password' => 'segredo-digitado']));

        $registro = Auditoria::where('evento', 'falhou')->firstOrFail();
        $this->assertSame('alguem@exemplo.com', $registro->descricao);
        $this->assertStringNotContainsString('segredo-digitado', json_encode($registro->toArray()));
    }
}