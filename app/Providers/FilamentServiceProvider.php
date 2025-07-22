<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Panel;
use Filament\Facades\Filament;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\ActivitiesPanelProvider;
use App\Providers\Filament\VolunteersPanelProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::registerPanel(fn (): Panel => app(AdminPanelProvider::class)->panel(new Panel));
        Filament::registerPanel(fn (): Panel => app(ActivitiesPanelProvider::class)->panel(new Panel));
        Filament::registerPanel(fn (): Panel => app(VolunteersPanelProvider::class)->panel(new Panel));

    }
}
