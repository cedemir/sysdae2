<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ata {{ $ata->numero }}</title>
    <style>
        @page { margin: 40px 45px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1d2433; }
        .sistema { color: #6b7385; font-size: 9px; }
        h1 { font-size: 17px; margin: 4px 0 10px; }
        h2 { font-size: 12px; margin: 16px 0 4px; border-bottom: 1px solid #c8cdd8; padding-bottom: 2px; }
        .campo { margin: 2px 0; }
        .texto { white-space: pre-line; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #eef0f5; text-align: left; }
        th, td { border: 1px solid #c8cdd8; padding: 4px 6px; }
        .assinaturas { margin-top: 50px; }
        .linha-assinatura { border-top: 1px solid #1d2433; width: 60%; margin: 40px 0 2px; }
    </style>
</head>
<body>
    <div class="sistema">SYSDAE - Sistema de Registros DAE</div>
    <h1>Ata nº {{ $ata->numero }}</h1>

    <div class="campo"><strong>Data da reunião:</strong> {{ $ata->data_reuniao->format('d/m/Y') }}</div>
    <div class="campo"><strong>Assunto:</strong> {{ $ata->assunto }}</div>

    @if (filled($ata->participantes))
        <h2>Participantes</h2>
        <p class="texto">{{ $ata->participantes }}</p>
    @endif

    @if (filled($ata->pauta))
        <h2>Pauta</h2>
        <p class="texto">{{ $ata->pauta }}</p>
    @endif

    @if ($ata->alunos->isNotEmpty())
        <h2>Alunos citados</h2>
        <table>
            <thead><tr><th>Nome</th><th>CPF</th></tr></thead>
            <tbody>
                @foreach ($ata->alunos as $aluno)
                    <tr><td>{{ $aluno->nome }}</td><td>{{ \App\Support\Formatos::cpf($aluno->cpf) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (filled($ata->deliberacoes))
        <h2>Deliberações</h2>
        <p class="texto">{{ $ata->deliberacoes }}</p>
    @endif

    @if (filled($ata->encaminhamentos))
        <h2>Encaminhamentos</h2>
        <p class="texto">{{ $ata->encaminhamentos }}</p>
    @endif

    <div class="assinaturas">
        <div class="linha-assinatura"></div>
        <div>Assinatura do responsável</div>
    </div>
</body>
</html>