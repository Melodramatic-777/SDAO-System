<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class InviteVolunteer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static string $view = 'filament.pages.volunteers-admin.invite-volunteer';

    public static function route(): string { return '/volunteers-admin/invite-volunteer'; }
    public static function getNavigationLabel(): string { return 'Invite Volunteer'; }
}
