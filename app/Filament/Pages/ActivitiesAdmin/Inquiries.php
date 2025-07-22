<?php

namespace App\Filament\Pages\ActivitiesAdmin;

use Filament\Pages\Page;

class Inquiries extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center';
    protected static string $view = 'filament.pages.activities-admin.inquiries';

    public static function route(): string { return '/activities-admin/inquiries'; }
    public static function getNavigationLabel(): string { return 'Inquiries'; }
}
