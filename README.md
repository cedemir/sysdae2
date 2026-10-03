<<<<<<< HEAD
# sysdae2
sysdae 2.0
=======

1. Acesso e usuários
Login com opção "lembrar de mim".
Alterar a própria senha: botão no Painel e página própria.
Usuários: cadastro, ativação e inativação, e filtro por perfil e situação. Quando um usuário é inativado ou excluído, as sessões dele caem na hora e o "lembrar de mim" deixa de valer.
Cinco perfis: Administrador, DAE Central, Residência Estudantil, Psicossocial e Somente consulta.
Acessos por perfil: uma tela para definir, em cada cadastro e perfil, o nível de acesso (Sem acesso, Consulta ou Edição). Cada relatório também é liberado por perfil, como "Pode gerar".
Tema por usuário: Clássico ou Moderno, trocado pelo menu do perfil.

2. Cadastros acadêmicos
Cursos: sigla, nível, duração e ativo.
Séries: ordem e ativa.
Turmas: curso, série, ano letivo, turno e código.
Alunos: CPF validado e sem duplicidade, foto, sexo, contatos, responsáveis, contato de emergência, município, programa de benefícios (auxílio moradia ou permanência) e situação.
Matrículas: número, turma e situação. A situação da matrícula mais recente passa a ser a situação do aluno automaticamente.

3. Residência estudantil
Alojamentos: nome, localização, público e ativo.
Apartamentos: andar, capacidade, ocupação e vagas.
Regimes de residência.
Residências (aluno ↔ apartamento): categoria residente ou semirresidente, regime e data de entrada.
Trocas de apartamento: o histórico é gerado sozinho sempre que o apartamento de uma residência muda, guardando quem registrou a troca.
Faltas na residência: justificada ou não, com observação.
Autorizações de pernoite: parcial ou não, forma, quem autorizou e justificativa.

4. Área disciplinar e psicossocial
Ocorrências disciplinares: advertência, suspensão da residência, perda da vaga, atividades orientadas (horas recebidas e cumpridas), práticas restaurativas e encaminhamentos.
Aceitam anexos, com download controlado (/anexos/ocorrencias/...). Os arquivos são apagados do disco quando a ocorrência é excluída.
Podem ser marcadas como sigilosas.
Atendimentos psicossociais: data, hora, forma, servidores envolvidos e sigilo.
Sigilo: registros sigilosos de ocorrências e atendimentos só aparecem para Administrador e Psicossocial, em todas as telas, relatórios e relacionamentos.

Ficha de saúde: tipo sanguíneo, alergias, restrições alimentares, necessidades especiais, cartão SUS, plano de saúde e unidade de referência. Guarda quem atualizou por último.
Atas: número, assunto, alunos citados e impressão em PDF.
5. Relatórios
Podem ser vistos na tela ou baixados em PDF, com filtro por aluno e/ou período.

Relatório	O que mostra
Estatísticas da residência	Por categoria, por apartamento (ocupação), por curso e por sexo
Alunos de um apartamento	Lista com foto dos alunos
Trocas de apartamento	Geral ou de um aluno
Faltas na residência	Faltas no filtro escolhido
Autorizações de pernoite	Pernoites no filtro escolhido
Ocorrências disciplinares	Ocorrências no filtro escolhido
Atendimentos psicossociais	Atendimentos no filtro escolhido
Ficha do aluno	Dados completos de um aluno
Na hora de escolher o aluno, a busca aceita nome ou CPF.

6. Auditoria
Registra criação, alteração e exclusão em praticamente todos os cadastros.
Registra entradas e tentativas de login que falharam.
Registra mudanças de perfil de usuário, no formato perfil antigo → perfil novo.
Registra consultas a dados sensíveis (lista, visualização e edição de ficha de saúde e de atendimentos), sem repetir o mesmo registro em sequência curta.
A tela permite filtrar por evento e por entidade e mostra usuário, IP e detalhes.

7. Comandos de console
php artisan sysdae:perfil {email} {perfil}: atribui um perfil a um usuário.
php artisan sysdae:permissoes-sincronizar: cria as linhas que faltam na tabela de acessos por perfil, usando o padrão de cada cadastro.
