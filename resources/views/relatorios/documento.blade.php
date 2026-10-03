<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $relatorio['titulo'] }}</title>
    <style>
        @page { margin: 28px 30px 40px 30px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1d2433; }
        .sistema { color: #6b7385; font-size: 9px; }
        h1 { font-size: 16px; margin: 4px 0 2px; }
        h2 { font-size: 12px; margin: 16px 0 6px; border-bottom: 1px solid #c8cdd8; padding-bottom: 2px; }
        .meta { color: #6b7385; font-size: 9px; margin-bottom: 8px; }
        .filtro { font-size: 10px; }
        .resumo { margin: 8px 0; padding-left: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #eef0f5; text-align: left; }
        th, td { border: 1px solid #c8cdd8; padding: 4px 5px; vertical-align: top; }
        .vazio { color: #6b7385; font-style: italic; }
        .rodape { position: fixed; bottom: -22px; left: 0; right: 0; text-align: center; font-size: 8px; color: #6b7385; }
        .rodape .pagina:before { content: counter(page); }
    </style>
</head>
<body>
    <div class="rodape">SYSDAE - página <span class="pagina"></span></div>
    @include('relatorios.partials.conteudo', ['relatorio' => $relatorio])
</body>
</html>