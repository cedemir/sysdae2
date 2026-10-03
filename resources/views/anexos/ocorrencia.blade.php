<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anexos da ocorrência - SYSDAE</title>
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: #1d2433; margin: 0; background: #f4f5f8; }
        .caixa { background: #fff; max-width: 680px; margin: 30px auto; padding: 24px 28px; border-radius: 10px; border: 1px solid #d0d5dd; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        p.sub { color: #6b7385; margin: 0 0 18px; font-size: 14px; }
        ul { list-style: none; padding: 0; margin: 0; }
        li { padding: 10px 0; border-bottom: 1px solid #e4e7ec; display: flex; justify-content: space-between; gap: 12px; }
        a.baixar { color: #1f5eff; text-decoration: none; font-weight: 600; white-space: nowrap; }
        a.voltar { display: inline-block; margin-top: 16px; color: #1f5eff; text-decoration: none; font-size: 14px; }
        .vazio { color: #6b7385; font-style: italic; }
        .aviso { margin-top: 14px; font-size: 13px; color: #6b7385; }
    </style>
</head>
<body>
    <div class="caixa">
        <h1>Anexos da ocorrência</h1>
        <p class="sub">
            {{ $ocorrencia->aluno->nome }} - {{ $ocorrencia->data_ocorrencia->format('d/m/Y') }}
            @if ($ocorrencia->sigiloso) (sigilosa) @endif
        </p>

        @if (count($arquivos) === 0)
            <p class="vazio">Esta ocorrência não tem anexos.</p>
        @else
            <ul>
                @foreach ($arquivos as $indice => $arquivo)
                    <li>
                        <span>{{ $arquivo['nome'] }}</span>
                        <a class="baixar" href="{{ route('anexos.ocorrencia.baixar', [$ocorrencia, $indice]) }}">Baixar</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="aviso">Documentos de uso restrito: não compartilhe fora da equipe autorizada.</p>
        <a class="voltar" href="/admin/ocorrencias">&larr; Voltar às ocorrências</a>
    </div>
</body>
</html>