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
use Filament\Navigation\NavigationItem;
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

            // ── Sidebar Styling ───────────────────────────────────────────────
            // Filament defaults to lg:bg-transparent on desktop; override so the
            // sidebar is visually distinct from the content area in both themes.
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => new \Illuminate\Support\HtmlString('
<style>
    @media (min-width: 1024px) {
        .fi-sidebar {
            background-color: #ffffff !important;
            border-inline-end: 1px solid #e5e7eb !important;
        }
        .dark .fi-sidebar {
            background-color: #1e293b !important;
            border-inline-end-color: #334155 !important;
        }
    }
</style>
'),
            )

            // ── Developer Tools (sidebar links) ──────────────────────────────
            ->navigationItems([
                NavigationItem::make('API Docs')
                    ->url('/docs/api', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-document-text')
                    ->group('Developer')
                    ->sort(10),

                NavigationItem::make('Queue Monitor')
                    ->url('/horizon', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-queue-list')
                    ->group('Developer')
                    ->sort(20),
            ])

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

            // ── Dark Mode ────────────────────────────────────────────────────
            ->darkMode()

            // ── Database Notifications ───────────────────────────────────────
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')

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
