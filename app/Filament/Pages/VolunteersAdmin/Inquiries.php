<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class Inquiries extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static string $view = 'filament.pages.volunteers-admin.inquiries';

    public static function route(): string { return '/volunteers-admin/inquiries'; }
    public static function getNavigationLabel(): string { return 'Inquiries'; }
}
