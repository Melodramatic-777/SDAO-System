<?php

namespace App\Filament\Pages\ActivitiesAdmin;

use Filament\Pages\Page;

class Accounts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static string $view = 'filament.pages.activities-admin.accounts';

    public static function route(): string { return '/activities-admin/accounts'; }
    public static function getNavigationLabel(): string { return 'Accounts'; }
}
