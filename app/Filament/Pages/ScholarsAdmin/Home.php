<?php

namespace App\Filament\Pages\ActivitiesAdmin;

use Filament\Pages\Page;

class Home extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.activities-admin.home';

    public static function route(): string { return '/activities-admin/home'; }
    public static function getNavigationLabel(): string { return 'Home'; }
}
