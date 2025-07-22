<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Factory;
use App\Mail\TemporaryCredentialsMailable;

class AdminController extends Controller
{
    /**
     * Show the form to create a new Admin.
     */
    public function createAdminForm()
    {
        return view('superadmin.create-admin');
    }

    /**
     * Store a new Admin in Firebase.
     */
    public function storeAdmin(Request $request)
    {
        // Validate required fields
        $data = $request->validate([
            'first_name'  => 'required|string',
            'middle_name' => 'required|string',
            'last_name'   => 'required|string',
            'email'       => 'required|email',
            'role'        => 'required|string',
        ]);

        // Init Firebase
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        $uid = (string) Str::uuid();
        $temporaryPassword = Str::random(8);
        $hashedPassword = Hash::make($temporaryPassword);

        $database->getReference('admins/' . $uid)->set([
            'first_name'  => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name'   => $data['last_name'],
            'email'       => $data['email'],
            'role'        => $data['role'],
            'created_at'  => now()->setTimezone('Asia/Manila')->toDateTimeString(),
            'password'    => $hashedPassword,
        ]);

        $fullName = trim("{$data['first_name']} {$data['middle_name']} {$data['last_name']}");

        Mail::to($data['email'])->send(
            new TemporaryCredentialsMailable(
                $fullName,
                $data['email'],
                $temporaryPassword // Plain password emailed
            )
        );

        return redirect()->route('superadmin.admins.list')->with('success', 'Admin created successfully!');
    }

    /**
     * Show all admins (same as your ListAdmins logic).
     */
    public function listAdmins()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        $admins = $database->getReference('admins')->getValue();

        return view('superadmin.list-admins', [
            'admins' => $admins ? collect($admins)->values()->all() : [],
        ]);
    }
}
