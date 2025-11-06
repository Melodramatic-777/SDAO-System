<?php

namespace App\Filament\Pages\ActivitiesAdmin;

use Filament\Pages\Page;

class Documents extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static string $view = 'filament.pages.activities-admin.documents';

    public static function route(): string { return '/activities-admin/documents'; }
    public static function getNavigationLabel(): string { return 'Documents'; }
}
