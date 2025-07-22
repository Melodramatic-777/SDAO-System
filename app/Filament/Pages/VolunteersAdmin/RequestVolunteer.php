<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class RequestVolunteer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static string $view = 'filament.pages.volunteers-admin.request-volunteer';

    public static function route(): string { return '/volunteers-admin/request-volunteer'; }
    public static function getNavigationLabel(): string { return 'Request Volunteer'; }
}
