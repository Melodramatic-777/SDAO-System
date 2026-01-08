<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Factory;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    // =========================
    // SHOW EMAIL FORM
    // =========================
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    // =========================
    // SEND RESET LINK
    // =========================
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;

        // 🔍 Find user in all locations
        $userPath = $this->findUserPathByEmail($email);

        if (!$userPath) {
            return back()->with('status', 'If this email exists, a reset link was sent.');
        }

        $token = Str::random(64);

        $this->database->getReference("password_resets/$token")->set([
            'email' => $email,
            'path' => $userPath,
            'expires_at' => Carbon::now()->addMinutes(30)->timestamp,
        ]);

        $link = url("/reset-password/$token");

        Mail::raw("Click to reset your password:\n$link\n\nThis link expires in 30 minutes.", function ($message) use ($email) {
            $message->to($email)->subject('Password Reset - IsKonnect');
        });

        return back()->with('status', 'If this email exists, a reset link was sent.');
    }

    // =========================
    // SHOW RESET FORM
    // =========================
    public function showResetForm($token)
    {
        $data = $this->database->getReference("password_resets/$token")->getValue();

        if (!$data || $data['expires_at'] < now()->timestamp) {
            return redirect('/login')->withErrors(['link' => 'Reset link expired.']);
        }

        return view('auth.reset-password', compact('token'));
    }

    // =========================
    // UPDATE PASSWORD
    // =========================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $token = $request->token;

        $data = $this->database->getReference("password_resets/$token")->getValue();

        if (!$data || $data['expires_at'] < now()->timestamp) {
            return redirect('/login')->withErrors(['link' => 'Reset link expired.']);
        }

        $hashed = Hash::make($request->password);

        // 🔁 Update password in original user location
        $this->database->getReference($data['path'] . '/password')->set($hashed);

        // ❌ Delete token
        $this->database->getReference("password_resets/$token")->remove();

        return redirect('/login')->with('status', 'Password successfully reset.');
    }

    // =========================
    // FIND USER IN FIREBASE
    // =========================
    private function findUserPathByEmail($email)
    {
        // ADMINS
        $admins = $this->database->getReference('admins')->getValue();
        if ($admins) {
            foreach ($admins as $key => $admin) {
                if (($admin['email'] ?? '') === $email) {
                    return "admins/$key";
                }
            }
        }

        // SCHOLARS
        $scholars = $this->database->getReference('scholars')->getValue();
        if ($scholars) {
            foreach ($scholars as $year => $terms) {
                foreach ($terms as $term => $types) {
                    foreach ($types as $type => $records) {
                        foreach ($records as $key => $record) {
                            if (($record['email'] ?? '') === $email) {
                                return "scholars/$year/$term/$type/$key";
                            }
                        }
                    }
                }
            }
        }

        // OFFICE ACCOUNTS
        $offices = $this->database->getReference('office_accounts')->getValue();
        if ($offices) {
            foreach ($offices as $key => $office) {
                if (($office['email'] ?? '') === $email) {
                    return "office_accounts/$key";
                }
            }
        }

        return null;
    }
}
