<div class="sistema">SYSDAE - Sistema de Registros DAE</div>
<h1>{{ $relatorio['titulo'] }}</h1>
<div class="meta">Emitido em {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()?->name }}</div>

@foreach ($relatorio['filtros'] as $filtro)
    <div class="filtro">{{ $filtro }}</div>
@endforeach

@if (! empty($relatorio['resumo']))
    <ul class="resumo">
        @foreach ($relatorio['resumo'] as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
@endif

@foreach ($relatorio['secoes'] as $secao)
    @if (! empty($secao['titulo']))
        <h2>{{ $secao['titulo'] }}</h2>
    @endif

    @if (count($secao['linhas']) === 0)
        <p class="vazio">Nenhum registro encontrado.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($secao['colunas'] as $coluna)
                        <th>{{ $coluna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($secao['linhas'] as $linha)
                    <tr>
                        @foreach ($linha as $celula)
                            <td>
                                @if (is_array($celula) && array_key_exists('imagem', $celula))
                                    @if ($celula['imagem'])
                                        <img src="{{ $celula['imagem'] }}" alt="Foto" style="width: 60px; height: auto;">
                                    @else
                                        <span class="vazio">sem foto</span>
                                    @endif
                                @else
                                    {!! nl2br(e((string) $celula)) !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach