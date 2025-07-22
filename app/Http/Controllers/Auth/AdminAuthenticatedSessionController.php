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
     * Display the custom admin login form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.admin-login');
    }

    /**
     * Handle an incoming admin login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Initialize Firebase connection
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $database = $factory->createDatabase();

        // Retrieve all admins from Firebase
        $admins = $database->getReference('admins')->getValue();

        $authenticatedAdmin = null;

        if ($admins) {
            foreach ($admins as $uid => $admin) {
                if (
                    isset($admin['email'], $admin['password']) &&
                    $admin['email'] === $request->input('email') &&
                    Hash::check($request->input('password'), $admin['password'])
                ) {
                    $authenticatedAdmin = $admin;
                    break;
                }
            }
        }

        if (! $authenticatedAdmin) {
            return back()->withErrors([
                'email' => 'Invalid credentials. Please check your email and password.',
            ])->withInput();
        }

        // Save the admin session
        Session::put('admin', [
            'first_name'  => $authenticatedAdmin['first_name'] ?? '',
            'middle_name' => $authenticatedAdmin['middle_name'] ?? '',
            'last_name'   => $authenticatedAdmin['last_name'] ?? '',
            'email'       => $authenticatedAdmin['email'],
            'role'        => $authenticatedAdmin['role'],
        ]);

        // Redirect based on role
        $role = $authenticatedAdmin['role'] ?? '';

        return match ($role) {
            'Activities Admin' => redirect()->route('activities-admin.home'),
            'Volunteers Admin' => redirect()->route('volunteers-admin.leader-board'),
            'SDAO Coordinator' => redirect()->route('sdao-coordinator.home'),
            'Super Admin' => redirect()->route('superadmin.admins.list'),
            default => redirect('/'),
        };
    }

    /**
     * Log the admin out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Session::forget('admin');

        return redirect('/admin/login');
    }
}
