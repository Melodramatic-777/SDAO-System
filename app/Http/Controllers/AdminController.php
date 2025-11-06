<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Factory;
use App\Mail\TemporaryCredentialsMailable;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    /* =========================================================
     * OFFICES MANAGEMENT
     * ========================================================= */

    public function showOfficesPage(Request $request)
    {
        $officesData = $this->database->getReference('offices')->getValue();
        $offices = [];
        if ($officesData) {
            foreach ($officesData as $id => $office) {
                $offices[] = [
                    'id' => $id,
                    'name' => $office['name'],
                    'created_at' => $office['created_at'] ?? '',
                ];
            }
        }

        $officeData = $this->database->getReference('office_accounts')->getValue();
        $officeAccounts = [];
        if ($officeData) {
            foreach ($officeData as $acc) {
                $officeAccounts[] = [
                    'office_name' => $acc['office_name'] ?? 'N/A',
                    'first_name'  => $acc['first_name'],
                    'middle_name' => $acc['middle_name'] ?? '',
                    'last_name'   => $acc['last_name'],
                    'suffix'      => $acc['suffix'] ?? '',
                    'email'       => $acc['email'],
                    'created_at'  => $acc['created_at'] ?? '',
                ];
            }
        }

        $perPage = 10;
        $page = $request->get('page', 1);

        $paginateArray = function ($items) use ($page, $perPage) {
            $collection = collect($items);
            $total = $collection->count();
            $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
            return new LengthAwarePaginator($results, $total, $perPage, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        };

        $paginatedOffices = $paginateArray($offices);
        $paginatedOfficeAccounts = $paginateArray($officeAccounts);

        return view('superadmin.offices', [
            'offices' => $paginatedOffices,
            'officeAccounts' => $paginatedOfficeAccounts,
        ]);
    }

    public function storeOffice(Request $request)
    {
        $data = $request->validate([
            'office_name' => 'required|string',
        ]);

        $this->database->getReference('offices/' . Str::uuid())
            ->set([
                'name' => $data['office_name'],
                'created_at' => now()->toDateTimeString(),
            ]);

        return back()->with('success', 'Office created successfully!');
    }

    public function storeOfficeAccount(Request $request)
    {
        $data = $request->validate([
            'office_id'   => 'required|string',
            'first_name'  => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name'   => 'required|string',
            'suffix'      => 'nullable|string',
            'email'       => 'required|email',
        ]);

        $office = $this->database->getReference('offices/' . $data['office_id'])->getValue();
        $officeName = $office['name'] ?? 'Unknown Office';

        $tempPass = Str::random(8);
        $hashed = Hash::make($tempPass);

        $this->database->getReference('office_accounts/' . Str::uuid())
            ->set([
                'office_id'   => $data['office_id'],
                'office_name' => $officeName,
                'first_name'  => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name'   => $data['last_name'],
                'suffix'      => $data['suffix'],
                'email'       => $data['email'],
                'password'    => $hashed,
                'created_at'  => now()->toDateTimeString(),
            ]);

        Mail::to($data['email'])->send(
            new TemporaryCredentialsMailable(
                "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
                $data['email'],
                $tempPass
            )
        );

        return back()->with('success', 'Office account created!');
    }

    /* =========================================================
     * ADMINS MANAGEMENT
     * ========================================================= */

    public function createAdminForm()
    {
        return view('superadmin.create-admin');
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'required|string',
            'middle_name' => 'required|string',
            'last_name'   => 'required|string',
            'email'       => 'required|email',
            'role'        => 'required|string',
        ]);

        $uid = (string) Str::uuid();
        $temporaryPassword = Str::random(8);
        $hashedPassword = Hash::make($temporaryPassword);

        $this->database->getReference('admins/' . $uid)->set([
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
                $temporaryPassword
            )
        );

        return redirect()->route('superadmin.admins.list')->with('success', 'Admin created successfully!');
    }


public function listAdmins(Request $request)
{
    $admins = $this->database->getReference('admins')->getValue();
    $admins = $admins ? collect($admins)->values()->all() : [];

    // OFFICES
    $officesData = $this->database->getReference('offices')->getValue();
    $offices = [];
    if ($officesData) {
        foreach ($officesData as $id => $office) {
            $offices[] = [
                'id' => $id,
                'name' => $office['name'],
                'created_at' => $office['created_at'] ?? '',
            ];
        }
    }

    // OFFICE ACCOUNTS
    $officeData = $this->database->getReference('office_accounts')->getValue();
    $officeAccounts = [];
    if ($officeData) {
        foreach ($officeData as $acc) {
            $officeAccounts[] = [
                'office_name' => $acc['office_name'] ?? 'N/A',
                'first_name'  => $acc['first_name'],
                'middle_name' => $acc['middle_name'] ?? '',
                'last_name'   => $acc['last_name'],
                'suffix'      => $acc['suffix'] ?? '',
                'email'       => $acc['email'],
                'created_at'  => $acc['created_at'] ?? '',
            ];
        }
    }

    // PAGINATION
    $perPage = 10;
    $page = $request->get('page', 1);

    $paginateArray = function ($items) use ($page, $perPage, $request) {
        $collection = collect($items);
        $total = $collection->count();
        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    };

    $paginatedOffices = $paginateArray($offices);
    $paginatedOfficeAccounts = $paginateArray($officeAccounts);

    return view('superadmin.list-admins', [
        'admins' => $admins,
        'offices' => $paginatedOffices,
        'officeAccounts' => $paginatedOfficeAccounts,
    ]);
}


// Revoke Admin Access
public function revokeAdmin($email)
{
    $adminsRef = $this->database->getReference('admins');
    $admins = $adminsRef->getValue();

    if ($admins) {
        foreach ($admins as $key => $admin) {
            if ($admin['email'] === $email) {
                $adminsRef->getChild($key)->remove();
                return back()->with('success', "Admin access for {$email} revoked!");
            }
        }
    }

    return back()->with('error', 'Admin not found.');
}

// Revoke Office User Access
public function revokeOfficeUser($email)
{
    $officeRef = $this->database->getReference('office_accounts');
    $users = $officeRef->getValue();

    if ($users) {
        foreach ($users as $key => $user) {
            if ($user['email'] === $email) {
                $officeRef->getChild($key)->remove();
                return back()->with('success', "Office user access for {$email} revoked!");
            }
        }
    }

    return back()->with('error', 'User not found.');
}



}
