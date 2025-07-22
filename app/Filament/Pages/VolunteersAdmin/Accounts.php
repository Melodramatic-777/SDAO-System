<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class Accounts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static string $view = 'filament.pages.volunteers-admin.accounts';

    public static function route(): string { return '/volunteers-admin/accounts'; }
    public static function getNavigationLabel(): string { return 'Accounts'; }
}
