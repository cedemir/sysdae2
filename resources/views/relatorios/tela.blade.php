<x-relatorios.pagina
    :titulo="$relatorio['titulo']"
    :caminho="[route('relatorios.index') => 'Relatórios', 'Resultado']"
>
    <x-slot name="acoes">
        <x-filament::button tag="a" :href="route('relatorios.index')" color="gray" icon="heroicon-o-arrow-left">
            Voltar
        </x-filament::button>
        <x-filament::button color="gray" icon="heroicon-o-printer" onclick="window.print()">
            Imprimir
        </x-filament::button>
        <x-filament::button tag="a" :href="request()->fullUrlWithQuery(['formato' => 'pdf'])" target="_blank" icon="heroicon-o-arrow-down-tray">
            Baixar PDF
        </x-filament::button>
    </x-slot>

    <x-filament::section>
        <div class="sd-relatorio">
            @include('relatorios.partials.conteudo', ['relatorio' => $relatorio])
        </div>
    </x-filament::section>
</x-relatorios.pagina>
