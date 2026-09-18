<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\CmsStatsOverview;
use App\Filament\Widgets\RecentArticles;
use App\Filament\Widgets\RecentRequests;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
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
            ->brandName('Meet AJ CMS')
            ->brandLogo(asset('assets/img/logo.png'))
            ->brandLogoHeight('1.75rem')
            ->favicon(asset('assets/img/favicon.png'))
            ->colors([
                'primary' => Color::hex('#2563eb'),
                'gray' => Color::Slate,
                'success' => Color::hex('#16a34a'),
                'warning' => Color::hex('#d97706'),
                'danger' => Color::hex('#dc2626'),
                'info' => Color::hex('#0ea5e9'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups()
            ->unsavedChangesAlerts()
            ->globalSearch()
            ->navigationGroups([
                NavigationGroup::make('Content')->icon(Heroicon::OutlinedDocumentText),
                NavigationGroup::make('Communications')->icon(Heroicon::OutlinedInbox),
                NavigationGroup::make('Administration')->icon(Heroicon::OutlinedCog6Tooth),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([Dashboard::class])
            ->widgets([
                CmsStatsOverview::class,
                RecentArticles::class,
                RecentRequests::class,
            ])
            ->assets([
                Css::make('meet-aj-admin', resource_path('css/filament-admin.css')),
            ])
            ->authGuard('web')
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
            ->authMiddleware([Authenticate::class]);
    }
}
