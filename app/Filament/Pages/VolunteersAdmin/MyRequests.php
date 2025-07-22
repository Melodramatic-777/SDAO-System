<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class MyRequests extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string $view = 'filament.pages.volunteers-admin.my-requests';

    public static function route(): string { return '/volunteers-admin/my-requests'; }
    public static function getNavigationLabel(): string { return 'My Requests'; }
}
