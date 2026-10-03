<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Support\Tema;
use Filament\Actions\Action;
use Filament\Enums\UserMenuPosition;
use Filament\FontProviders\BunnyFontProvider;
use Filament\FontProviders\LocalFontProvider;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Relatórios')
                    ->url('/relatorios')
                    ->isActiveWhen(fn (): bool => request()->routeIs('relatorios.*'))
                    ->icon('heroicon-o-document-text')
                    ->sort(100)
                    ->visible(fn (): bool => auth()->user() !== null && \App\Services\RelatorioService::permitidos(auth()->user()) !== []),
            ])
            ->brandName('SYSDAE')
            // Tema Moderno: sem barra no topo, logotipo no alto do menu e usuário no rodapé do menu.
            ->brandLogo(fn () => Tema::moderno() ? view('filament.marca') : null)
            ->brandLogoHeight(fn () => Tema::moderno() ? '2.5rem' : null)
            ->font(
                fn () => Tema::moderno() ? 'Jost' : null,
                provider: fn () => Tema::moderno() ? BunnyFontProvider::class : LocalFontProvider::class,
            )
            ->topbar(fn () => ! Tema::moderno())
            ->userMenu(position: fn () => Tema::moderno() ? UserMenuPosition::Sidebar : UserMenuPosition::Topbar)
            ->userMenuItems([
                Action::make('tema')
                    ->label(fn () => Tema::moderno() ? 'Usar tema clássico' : 'Usar tema moderno')
                    ->icon('heroicon-o-swatch')
                    ->url(fn () => route('tema.trocar', Tema::moderno() ? Tema::CLASSICO : Tema::MODERNO)),
            ])
            ->colors([
                'primary' => Color::Blue,
                // Cinzas puxados para o azul-marinho: no modo escuro o fundo fica marinho e o texto branco.
                'gray' => [
                    50 => 'oklch(0.984 0.003 247.858)',
                    100 => 'oklch(0.968 0.007 247.896)',
                    200 => 'oklch(0.929 0.013 255.508)',
                    300 => 'oklch(0.869 0.022 252.894)',
                    400 => 'oklch(0.80 0.035 255)',
                    500 => 'oklch(0.58 0.06 258)',
                    600 => 'oklch(0.46 0.075 261)',
                    700 => 'oklch(0.38 0.085 263)',
                    800 => 'oklch(0.32 0.09 264)',
                    900 => 'oklch(0.27 0.09 265)',
                    950 => 'oklch(0.21 0.075 266)',
                ],
            ])
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="' . asset(Tema::arquivoCss()) . '?v=' . filemtime(public_path(Tema::arquivoCss())) . '">'
                    . '<script src="' . asset('js/sysdae.js') . '?v=' . filemtime(public_path('js/sysdae.js')) . '"></script>',
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Painel::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
