<?php

namespace App\Providers\Filament;

use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
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
            ->path(config('dante.admin_path'))
            ->login()
            ->colors([
                'primary' => Color::hex('#191919'),
            ])
            ->brandName('Panel Dante')
            // 2FA por email, opt-in por usuario — ver ADR-003. Nunca
            // obligatorio (`isRequired: false`): quien no lo activó ve la
            // alerta persistente de abajo en vez de quedar bloqueado.
            ->multiFactorAuthentication([
                EmailAuthentication::make(),
            ], isRequired: false)
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn (): string => auth()->check() && ! auth()->user()->hasEmailAuthentication()
                    ? view('filament.partials.two-factor-nag')->render()
                    : '',
            )
            // Arrastrar/anidar del árbol de "Menús" (App\Livewire\ManageMenuItems) —
            // se registra acá, una sola vez para todo el panel, en vez de adentro del
            // propio componente Livewire (ver el comentario del partial).
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => view('filament.partials.menu-tree-scripts')->render(),
            )
            // "Ir a sitio web", al lado del ícono de perfil (pedido del cliente,
            // Fase 10) — abre en pestaña nueva para no perder el trabajo en curso
            // del panel.
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => view('filament.partials.visit-site-link')->render(),
            )
            ->passwordReset()
            ->profile()
            // El Dashboard (App\Filament\Pages\Dashboard) reemplaza al genérico de
            // Filament — se descubre solo, vive en la misma carpeta ya escaneada acá.
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // Orden fijo pedido por el cliente — sin esto, Filament ordena los
            // grupos alfabéticamente.
            ->navigationGroups([
                'General',
                'Contenido',
                'Configuraciones',
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
