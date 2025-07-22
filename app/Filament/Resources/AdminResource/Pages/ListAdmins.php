<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Kreait\Firebase\Factory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListAdmins extends Page
{
    protected static string $resource = AdminResource::class;

    protected static string $view = 'filament.pages.list-admins';

    public function admins(): array
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        $admins = $database->getReference('admins')->getValue();

        return $admins ? collect($admins)->values()->all() : [];
    }

    /**
     * Get all admins for a specific role.
     */
    public function adminsByRole(string $role): array
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        $admins = $database->getReference('admins')->getValue();

        return $admins
            ? collect($admins)
                ->filter(fn ($admin) => ($admin['role'] ?? '') === $role)
                ->sortByDesc(fn ($admin) => $admin['created_at'] ?? '')
                ->values()
                ->all()
            : [];
    }

    /**
     * Get the most recent admin for each role.
     */
    public function latestAdmins(): array
    {
        $roles = ['SDAO Coordinator', 'Volunteers Admin', 'Activities Admin'];

        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        $admins = $database->getReference('admins')->getValue();

        $collection = $admins ? collect($admins) : collect();

        return collect($roles)->map(function ($role) use ($collection) {
            return $collection
                ->filter(fn ($admin) => ($admin['role'] ?? '') === $role)
                ->sortByDesc(fn ($admin) => $admin['created_at'] ?? '')
                ->first();
        })->filter()->values()->all();
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'admins_export_' . now()->format('Ymd_His') . '.csv';

        $records = $this->admins();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($records) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'First Name', 'Middle Name', 'Last Name', 'Email', 'Role', 'Created At',
            ]);

            foreach ($records as $admin) {
                fputcsv($handle, [
                    $admin['first_name'] ?? '',
                    $admin['middle_name'] ?? '',
                    $admin['last_name'] ?? '',
                    $admin['email'] ?? '',
                    $admin['role'] ?? '',
                    $admin['created_at'] ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getHeaderActions(): array
    {
        return [

            Action::make('add_admin')
                ->label('Add Admin')
                ->icon('heroicon-o-plus')
                ->url(route('filament.admin.resources.admins.create'))
                ->button(),

            Action::make('export_csv')
                ->label('Export as CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('filament.admin.resources.admins.export')),
        ];
    }
}
