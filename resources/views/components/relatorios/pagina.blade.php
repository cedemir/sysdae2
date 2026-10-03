{{-- Página de relatório dentro do layout do painel (menu, barra do topo, tema e modo claro/escuro). --}}
@props([
    'titulo',
    'subtitulo' => null,
    'caminho' => [],
])

<x-filament-panels::layout.index>
    <style>
        .sd-rel .oculto { display: none; }
        .sd-rel-form { display: grid; gap: 1.25rem; max-width: 42rem; }
        .sd-rel-linha { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 640px) { .sd-rel-linha { grid-template-columns: 1fr; } }
        .sd-rel-rotulo { display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 500; color: var(--gray-950); }
        .dark .sd-rel-rotulo { color: #fff; }
        .sd-rel-rotulo small { font-weight: 400; color: var(--gray-500); }
        .sd-rel-erro { margin-top: 0.4rem; font-size: 0.875rem; color: var(--danger-600); }
        .dark .sd-rel-erro { color: var(--danger-400); }
        .sd-rel-acoes { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .sd-rel-campo { position: relative; }
        .sd-rel-sugestoes { list-style: none; margin: 0.25rem 0 0; padding: 0.25rem; position: absolute; left: 0; right: 0; z-index: 20;
                            max-height: 16rem; overflow: auto; border-radius: 0.5rem; background: #fff;
                            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.15); border: 1px solid var(--gray-200); }
        .dark .sd-rel-sugestoes { background: var(--gray-900); border-color: var(--gray-700); }
        .sd-rel-sugestoes li { padding: 0.5rem 0.75rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; }
        .sd-rel-sugestoes li:hover { background: var(--primary-50); color: var(--primary-700); }
        .dark .sd-rel-sugestoes li:hover { background: var(--gray-800); color: #fff; }
        .sd-rel-escolhido { margin-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--gray-600); }
        .dark .sd-rel-escolhido { color: var(--gray-300); }

        /* Conteúdo do relatório (o mesmo usado no PDF). */
        .sd-relatorio { font-size: 0.875rem; overflow-x: auto; }
        .sd-relatorio > .sistema, .sd-relatorio > h1 { display: none; }
        .sd-relatorio .meta, .sd-relatorio .filtro, .sd-relatorio .vazio { color: var(--gray-500); }
        .dark .sd-relatorio .meta, .dark .sd-relatorio .filtro, .dark .sd-relatorio .vazio { color: var(--gray-400); }
        .sd-relatorio h2 { font-size: 1rem; font-weight: 600; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
                           border-bottom: 1px solid var(--gray-200); }
        .dark .sd-relatorio h2 { border-color: var(--gray-700); }
        .sd-relatorio .resumo { margin: 0.75rem 0; padding-left: 1.25rem; list-style: disc; }
        .sd-relatorio table { width: 100%; border-collapse: collapse; }
        .sd-relatorio th { text-align: left; font-weight: 600; background: var(--gray-50); color: var(--gray-700); }
        .dark .sd-relatorio th { background: var(--gray-800); color: var(--gray-200); }
        .sd-relatorio th, .sd-relatorio td { padding: 0.55rem 0.75rem; border-bottom: 1px solid var(--gray-200); vertical-align: top; }
        .dark .sd-relatorio th, .dark .sd-relatorio td { border-color: var(--gray-700); }

        @media print {
            .fi-sidebar, .fi-topbar-ctn, .fi-header, .fi-layout-sidebar-toggle-btn-ctn { display: none !important; }
            .fi-main-ctn, .fi-main { margin: 0 !important; padding: 0 !important; max-width: none !important; }
            .fi-section { box-shadow: none !important; --tw-ring-color: transparent !important; }
            .sd-relatorio > .sistema, .sd-relatorio > h1 { display: block; }
            .sd-relatorio > h1 { font-size: 1.4rem; font-weight: 700; margin: 0.25rem 0; }
        }
    </style>

    <div class="fi-page sd-rel">
        <div class="fi-page-header-main-ctn">
            <header @class(['fi-header', 'fi-header-has-breadcrumbs' => $caminho, 'fi-header-has-subheading' => filled($subtitulo)])>
                <div>
                    @if ($caminho)
                        <x-filament::breadcrumbs :breadcrumbs="$caminho" />
                    @endif
                    <h1 class="fi-header-heading">{{ $titulo }}</h1>
                    @if (filled($subtitulo))
                        <p class="fi-header-subheading">{{ $subtitulo }}</p>
                    @endif
                </div>

                @isset($acoes)
                    <div class="fi-header-actions-ctn">
                        <div class="sd-rel-acoes">{{ $acoes }}</div>
                    </div>
                @endisset
            </header>

            <div class="fi-page-main">
                <div class="fi-page-content">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::layout.index>
