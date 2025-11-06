<?php

namespace App\Filament\Pages\ActivitiesAdmin;

use Filament\Pages\Page;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.activities-admin.profile';

    public static function route(): string { return '/activities-admin/profile'; }
    public static function getNavigationLabel(): string { return 'Profile'; }
}
