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

        // === 1) Check Admins ===
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

        // === 2) Check other accounts ===
        if (!$authenticatedUser) {
            $tables = [
                'organization_accounts',
                'department_accounts',
                'comex_accounts',
                'deans',
                'director_users',
                'marketing_accounts',
                'program_chairs',
                'scholar_accounts',
                'office_accounts',
            ];

            foreach ($tables as $table) {
                $accounts = $database->getReference($table)->getValue();
                if ($accounts) {
                    foreach ($accounts as $account) {
                        if (
                            isset($account['email'], $account['password']) &&
                            $account['email'] === $request->input('email') &&
                            Hash::check($request->input('password'), $account['password'])
                        ) {
                            $authenticatedUser = $account;
                            $userType = $table;
                            break 2;
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
            'activities_admin',
            'volunteers_admin',
            'activities_user',
            'activities_approver',
            'volunteers_user',
            'volunteers_office',
        ]);

        // === Admin routes ===
        if ($userType === 'admin') {
            $role = $authenticatedUser['role'] ?? '';

            if ($role === 'Activities Admin') {
                Session::put('activities_admin', $authenticatedUser);

                // 👇 Also grant them the approver session as SDAO!
                $authenticatedUser['role'] = 'SDAO'; // Role must match your approval_chain exactly!
                Session::put('activities_approver', $authenticatedUser);

                return redirect()->route('activities-admin.home');
            }


            if ($role === 'Volunteers Admin') {
                Session::put('volunteers_admin', $authenticatedUser);
                return redirect()->route('volunteers-admin.leader-board');
            }

            if ($role === 'Super Admin') {
                Session::put('admin', $authenticatedUser);
                return redirect()->route('superadmin.admins.list');
            }

            if ($role === 'SDAO Coordinator') {
                Session::put('admin', $authenticatedUser);
                return redirect()->route('sdao-coordinator.home');
            }

            return redirect('/');
        }

        // === Organization Accounts ===
        if ($userType === 'organization_accounts') {

            $role = strtolower($authenticatedUser['role'] ?? '');

            if ($role === 'adviser') {
                Session::put('activities_approver', $authenticatedUser);
                return redirect()->route('activities-approver.home');
            }

            // Not an adviser, so it's a regular org user
            if (!empty($authenticatedUser['organization_id'])) {
                $org = $database->getReference('organizations/' . $authenticatedUser['organization_id'])->getValue();
                if ($org && !empty($org['organization_name'])) {
                    $authenticatedUser['organization_name'] = $org['organization_name'];
                } else {
                    $authenticatedUser['organization_name'] = 'Organization ' . $authenticatedUser['organization_id'];
                }
            }

            Session::put('activities_user', $authenticatedUser);
            return redirect()->route('activities-user.home');
        }

        // === Department Accounts ===
        if ($userType === 'department_accounts') {
            Session::put('activities_user', $authenticatedUser);
            return redirect()->route('activities-user.home');
        }

        // === Approvers: all other roles ===
    if (in_array($userType, [
        'comex_accounts',
        'deans',
        'director_users',
        'marketing_accounts',
        'program_chairs',
    ])) {
        // 👇 Force the expected role for each type
        switch ($userType) {
            case 'program_chairs':
                $authenticatedUser['role'] = 'Program Chair';
                break;
            case 'deans':
                $authenticatedUser['role'] = 'Dean';
                break;
            case 'comex_accounts':
                $authenticatedUser['role'] = 'ComEx';
                break;
            case 'marketing_accounts':
                $authenticatedUser['role'] = 'Marketing';
                break;
            case 'director_users':
                $authenticatedUser['role'] = 'Director';
                break;
        }

        // ✅ Always put them in the approver session
        Session::put('activities_approver', $authenticatedUser);
        return redirect()->route('activities-approver.home');
    }



        // === Volunteers: Scholars ===
        if ($userType === 'scholar_accounts') {
            Session::put('volunteers_user', $authenticatedUser);
            return redirect()->route('volunteers-user.dashboard');
        }

        // === Volunteers: Office ===
        if ($userType === 'office_accounts') {
            Session::put('volunteers_office', $authenticatedUser);
            return redirect()->route('volunteers-offices.request-volunteer');
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
            'activities_admin',
            'volunteers_admin',
            'activities_user',
            'activities_approver',
            'volunteers_user',
            'volunteers_office',
        ]);

        return redirect('/admin/login');
    }
}
