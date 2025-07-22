<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class LeaderBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static string $view = 'filament.pages.volunteers-admin.leaderboard';

    public static function route(): string { return '/volunteers-admin/leaderboard'; }
    public static function getNavigationLabel(): string { return 'LeaderBoard'; }
}
