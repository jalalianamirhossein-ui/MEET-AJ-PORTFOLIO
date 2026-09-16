<?php
namespace App\Providers\Filament;

use Filament\Http\Middleware\{Authenticate, AuthenticateSession, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Pages\Dashboard;
use Filament\{Panel, PanelProvider};
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel {
        return $panel->default()->id('admin')->path('admin')->login()->brandName('Meet AJ CMS')
            ->colors(['primary' => Color::Blue])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([Dashboard::class])
            ->widgets([\App\Filament\Widgets\NewRequests::class])
            ->middleware([EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class, AuthenticateSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class, SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class])
            ->authMiddleware([Authenticate::class]);
    }
}
