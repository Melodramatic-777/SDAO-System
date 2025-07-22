<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class Office extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static string $view = 'filament.pages.volunteers-admin.office';

    public static function route(): string { return '/volunteers-admin/office'; }
    public static function getNavigationLabel(): string { return 'Office'; }
}
