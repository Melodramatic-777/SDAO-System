<?php

namespace App\Filament\Pages\VolunteersAdmin;

use Filament\Pages\Page;

class StudentVolunteers extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.pages.volunteers-admin.student-volunteers';

    public static function route(): string { return '/volunteers-admin/student-volunteers'; }
    public static function getNavigationLabel(): string { return 'Student Volunteers'; }
}
