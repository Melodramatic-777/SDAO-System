<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Factory;

class AdminAuthenticatedSessionController extends Controller
{
    /**
     * Show the login form.
     */
    public function create()
    {
        return view('auth.admin-login');
    }

    /**
     * Handle login.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        $authenticatedUser = null;
        $userType = null;

        // === Check Admins ===
        $admins = $database->getReference('admins')->getValue();
        if ($admins) {
            foreach ($admins as $admin) {
                if (
                    isset($admin['email'], $admin['password']) &&
                    $admin['email'] === $request->input('email') &&
                    Hash::check($request->input('password'), $admin['password'])
                ) {
                    $authenticatedUser = $admin;
                    $userType = 'admin';
                    break;
                }
            }
        }

        // === Check only Scholar + Volunteer Office Accounts ===
if (!$authenticatedUser) {
$scholarsRoot = $database->getReference('scholars')->getValue();
if ($scholarsRoot) {
    foreach ($scholarsRoot as $year => $terms) {
        foreach ($terms as $term => $types) { // ✅ NEW LEVEL
            foreach ($types as $type => $records) {
                foreach ($records as $record) {

                    if (
                        isset($record['email'], $record['password']) &&
                        $record['email'] === $request->input('email') &&
                        Hash::check($request->input('password'), $record['password'])
                    ) {
                        $authenticatedUser = $record;
                        $authenticatedUser['academic_year'] = $year;
                        $authenticatedUser['term'] = $term;
                        $authenticatedUser['scholarship_type'] = $type;
                        $userType = 'scholars';
                        break 4; // ✅ exit all loops
                    }

                }
            }
        }
    }
}


    // === Check Volunteer Office Accounts ===
    if (!$authenticatedUser) {
        $officeAccounts = $database->getReference('office_accounts')->getValue();
        if ($officeAccounts) {
            foreach ($officeAccounts as $account) {
                if (isset($account['email'], $account['password']) &&
                    $account['email'] === $request->input('email') &&
                    Hash::check($request->input('password'), $account['password'])) {
                    $authenticatedUser = $account;
                    $userType = 'office_accounts';
                    break;
                }
            }
        }
    }
}

        if (!$authenticatedUser) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput();
        }

        // === Clear all session keys ===
        Session::forget([
            'admin',
            'scholars_admin',
            'volunteers_admin',
            'volunteers_user',
            'volunteers_office',
        ]);

        // === Admin roles ===
        if ($userType === 'admin') {
            $role = $authenticatedUser['role'] ?? '';

            if ($role === 'SDA Associate') {
                Session::put('scholars_admin', $authenticatedUser);
                
                return redirect()->route('scholars-admin.dashboard');
            }

            if ($role === 'SDA Assistant') {
                Session::put('volunteers_admin', $authenticatedUser);
                return redirect()->route('volunteers-admin.dashboard');
            }

            if ($role === 'Super Admin') {
                Session::put('admin', $authenticatedUser);
                return redirect()->route('superadmin.admins.list');
            }

            return redirect('/');
        }

// === Volunteers: Scholars ===
if ($userType === 'scholars') {
    $authenticatedUser['id'] = $authenticatedUser['student_no']; // normalize key
    Session::put('scholar_accounts', $authenticatedUser);
    return redirect()->route('scholars.dashboard');
}


        // === Volunteers: Office ===
        if ($userType === 'office_accounts') {
            Session::put('volunteers_office', $authenticatedUser);
            return redirect()->route('volunteers-offices.dashboard-office');
        }

        return redirect('/');
    }

    /**
     * Log out.
     */
    public function destroy(Request $request)
    {
        Session::forget([
            'admin',
            'scholars_admin',
            'volunteers_admin',
            'activities_approver',
            'volunteers_user',
            'volunteers_office',
        ]);

        return redirect('/login');
    }
}
