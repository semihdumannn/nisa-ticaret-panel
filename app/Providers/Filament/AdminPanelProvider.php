<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\OrdersByStatusWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\TopProductsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ->spa()

            // ── Branding ─────────────────────────────────────────────────────
            ->brandName('Nisa Ticaret')
            ->favicon(asset('favicon.ico'))

            // ── Colors ───────────────────────────────────────────────────────
            ->colors([
                'primary' => Color::hex('#E73A99'),
                'gray'    => Color::Slate,
                'info'    => Color::hex('#00A6AB'),
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger'  => Color::Red,
            ])

            // ── Layout ───────────────────────────────────────────────────────
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])

            // ── Resources & Pages ────────────────────────────────────────────
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])

            // ── Widgets ──────────────────────────────────────────────────────
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                DashboardStatsWidget::class,
                RevenueChartWidget::class,
                OrdersByStatusWidget::class,
                TopProductsWidget::class,
                RecentOrdersWidget::class,
            ])

            // ── Middleware ───────────────────────────────────────────────────
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
