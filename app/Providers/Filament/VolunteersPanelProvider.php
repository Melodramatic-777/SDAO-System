<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class VolunteersPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('volunteers')
            ->path('volunteers')
            ->login()
            ->colors(['primary' => Color::Amber])
            ->pages([
                \App\Filament\Pages\VolunteersAdmin\LeaderBoard::class,
                \App\Filament\Pages\VolunteersAdmin\Office::class,
                \App\Filament\Pages\VolunteersAdmin\StudentVolunteers::class,
                \App\Filament\Pages\VolunteersAdmin\Accounts::class,
                \App\Filament\Pages\VolunteersAdmin\Inquiries::class,
                \App\Filament\Pages\VolunteersAdmin\RequestVolunteer::class,
                \App\Filament\Pages\VolunteersAdmin\MyRequests::class,
                \App\Filament\Pages\VolunteersAdmin\InviteVolunteer::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
