<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $factory = (new \Kreait\Firebase\Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        $uid = (string) Str::uuid();

        $temporaryPassword = Str::random(8);

        // ✅ Hash the password before storing
        $hashedPassword = Hash::make($temporaryPassword);

        $database->getReference('admins/' . $uid)->set([
            'first_name'  => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name'   => $data['last_name'],
            'email'       => $data['email'],
            'role'        => $data['role'],
            'created_at'  => now()->setTimezone('Asia/Manila')->toDateTimeString(),
            'password'    => $hashedPassword, // 🔒 hashed!
        ]);

        $fullName = trim("{$data['first_name']} {$data['middle_name']} {$data['last_name']}");

        \Illuminate\Support\Facades\Mail::to($data['email'])->send(
            new \App\Mail\TemporaryCredentialsMailable(
                $fullName,
                $data['email'],
                $temporaryPassword // Plain password emailed
            )
        );

        // Return dummy Eloquent model to satisfy Filament
        return new class extends \Illuminate\Database\Eloquent\Model {
            public $exists = true;
        };
    }
}
