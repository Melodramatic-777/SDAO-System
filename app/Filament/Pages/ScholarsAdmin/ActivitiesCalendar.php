<?php

namespace App\Filament\Pages\ActivitiesAdmin;

use Filament\Pages\Page;

class ActivitiesCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static string $view = 'filament.pages.activities-admin.activities-calendar';

    public static function route(): string { return '/activities-admin/calendar'; }
    public static function getNavigationLabel(): string { return 'Activities Calendar'; }
}
