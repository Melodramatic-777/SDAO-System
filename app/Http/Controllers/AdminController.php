<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Factory;
use App\Mail\TemporaryCredentialsMailable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use App\Mail\PasswordChangedMailable;


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


    public function changePassword(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'current_password' => 'required|string',
        'new_password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
        ],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Get logged-in admin email from session
    $adminEmail = session('volunteers_admin.email');

    if (!$adminEmail) {
        return back()->withErrors(['auth' => 'Unauthorized action.']);
    }

    // Fetch admins from Firebase
    $adminsRef = $this->database->getReference('admins');
    $admins = $adminsRef->getValue();

    $adminKey = null;
    $adminData = null;

    foreach ($admins as $key => $admin) {
        if (($admin['email'] ?? null) === $adminEmail) {
            $adminKey = $key;
            $adminData = $admin;
            break;
        }
    }

    if (!$adminData) {
        return back()->withErrors(['auth' => 'Admin account not found.']);
    }

    // Verify current password
    if (!Hash::check($request->current_password, $adminData['password'])) {
        return back()->withErrors([
            'current_password' => 'Current password is incorrect.',
        ]);
    }

    // Prevent reusing same password
    if (Hash::check($request->new_password, $adminData['password'])) {
        return back()->withErrors([
            'new_password' => 'New password must be different from the current password.',
        ]);
    }

    // Update password
    $adminsRef->getChild($adminKey)->update([
        'password' => Hash::make($request->new_password),
        'password_changed_at' => now()->toDateTimeString(),
    ]);

    // Send notification email (queued)
Mail::to($adminData['email'])->queue(
    new PasswordChangedMailable(
        trim("{$adminData['first_name']} {$adminData['middle_name']} {$adminData['last_name']}"),
        $adminData['email']
    )
);

    return back()->with('success', 'Password changed successfully.');
}



public function changeOfficePassword(Request $request)
{
    // 1. Validate input
    $validator = Validator::make($request->all(), [
        'current_password' => 'required|string',
        'new_password' => [
            'required',
            'string',
            'min:8',
            'confirmed', // checks new_password_confirmation
        ],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // 2. Get logged-in office user email from session
    $officeEmail = session('volunteers_office.email');
    if (!$officeEmail) {
        return back()->withErrors(['auth' => 'Unauthorized action.']);
    }

    // 3. Fetch office accounts from Firebase
    $officeRef = $this->database->getReference('office_accounts');
    $officeAccounts = $officeRef->getValue();

    $officeKey = null;
    $officeUser = null;

    if ($officeAccounts) {
        foreach ($officeAccounts as $key => $account) {
            if (($account['email'] ?? null) === $officeEmail) {
                $officeKey = $key;
                $officeUser = $account;
                break;
            }
        }
    }

    if (!$officeUser) {
        return back()->withErrors(['auth' => 'Office account not found.']);
    }

    // 4. Verify current password
    if (!Hash::check($request->current_password, $officeUser['password'])) {
        return back()->withErrors(['current_password' => 'Current password is incorrect.']);
    }

    // 5. Prevent reusing the same password
    if (Hash::check($request->new_password, $officeUser['password'])) {
        return back()->withErrors(['new_password' => 'New password must be different from the current password.']);
    }

    // 6. Update password in Firebase
    $officeRef->getChild($officeKey)->update([
        'password' => Hash::make($request->new_password),
        'password_changed_at' => now()->toDateTimeString(),
    ]);

    // 7. Send email notification (queued)
$fullName = trim(
    ($officeUser['first_name'] ?? '') . ' ' .
    ($officeUser['middle_name'] ?? '') . ' ' .
    ($officeUser['last_name'] ?? '')
);

Mail::to($officeUser['email'])->queue(
    new PasswordChangedMailable($fullName, $officeUser['email'])
);

 

    return back()->with('success', 'Password changed successfully.');
}



public function changeScholarPassword(Request $request)
{
    // 1. Validate input
    $validator = Validator::make($request->all(), [
        'current_password' => 'required|string',
        'new_password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
        ],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // 2. Get logged-in scholar student number from session
    $studentNo = session('scholar_accounts.student_no');

    if (!$studentNo) {
        return back()->withErrors(['auth' => 'Unauthorized action.']);
    }

    // 3. Fetch scholars from Firebase (nested structure)
    $scholarsRef = $this->database->getReference('scholars');
    $scholarsData = $scholarsRef->getValue();

    $foundPath = null;
    $scholarData = null;

    foreach ($scholarsData as $academicYear => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $scholarshipType => $records) {
                foreach ($records as $key => $scholar) {
                    if (($scholar['student_no'] ?? null) == $studentNo) {
                        $foundPath = "scholars/{$academicYear}/{$term}/{$scholarshipType}/{$key}";
                        $scholarData = $scholar;
                        break 4;
                    }
                }
            }
        }
    }

    if (!$scholarData || !$foundPath) {
        return back()->withErrors(['auth' => 'Scholar account not found.']);
    }

    // 4. Verify current password
    if (!Hash::check($request->current_password, $scholarData['password'])) {
        return back()->withErrors([
            'current_password' => 'Current password is incorrect.',
        ]);
    }

    // 5. Prevent reusing old password
    if (Hash::check($request->new_password, $scholarData['password'])) {
        return back()->withErrors([
            'new_password' => 'New password must be different from the current password.',
        ]);
    }

    // 6. Update password
    $this->database->getReference($foundPath)->update([
        'password' => Hash::make($request->new_password),
        'password_changed_at' => now()->toDateTimeString(),
    ]);

    // 7. Send email notification
    $fullName = trim(
        ($scholarData['first_name'] ?? '') . ' ' .
        ($scholarData['middle_name'] ?? '') . ' ' .
        ($scholarData['last_name'] ?? '')
    );

    if (!empty($scholarData['email'])) {
        Mail::to($scholarData['email'])->queue(
            new PasswordChangedMailable(
                $fullName,
                $scholarData['email']
            )
        );
    }

    return back()->with('success', 'Password changed successfully.');
}


 public function changeScholarsAdminPassword(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'current_password' => 'required|string',
        'new_password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
        ],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Get logged-in admin email from session
    $adminEmail = session('scholars_admin.email');

    if (!$adminEmail) {
        return back()->withErrors(['auth' => 'Unauthorized action.']);
    }

    // Fetch admins from Firebase
    $adminsRef = $this->database->getReference('admins');
    $admins = $adminsRef->getValue();

    $adminKey = null;
    $adminData = null;

    foreach ($admins as $key => $admin) {
        if (($admin['email'] ?? null) === $adminEmail) {
            $adminKey = $key;
            $adminData = $admin;
            break;
        }
    }

    if (!$adminData) {
        return back()->withErrors(['auth' => 'Admin account not found.']);
    }

    // Verify current password
    if (!Hash::check($request->current_password, $adminData['password'])) {
        return back()->withErrors([
            'current_password' => 'Current password is incorrect.',
        ]);
    }

    // Prevent reusing same password
    if (Hash::check($request->new_password, $adminData['password'])) {
        return back()->withErrors([
            'new_password' => 'New password must be different from the current password.',
        ]);
    }

    // Update password
    $adminsRef->getChild($adminKey)->update([
        'password' => Hash::make($request->new_password),
        'password_changed_at' => now()->toDateTimeString(),
    ]);

    // Send notification email (queued)
Mail::to($adminData['email'])->queue(
    new PasswordChangedMailable(
        trim("{$adminData['first_name']} {$adminData['middle_name']} {$adminData['last_name']}"),
        $adminData['email']
    )
);

    return back()->with('success', 'Password changed successfully.');
}




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

        return view('volunteers-offices.offices', [
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
        return view('volunteers-offices.create-admin');
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

        return redirect()->route('volunteers-offices.admins.list')->with('success', 'Admin created successfully!');
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

    return view('volunteers-offices.list-admins', [
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



public function showOfficeProfile()
{
    // Fetch office user email from session
$officeEmail = session('volunteers_office.email');

    if (!$officeEmail) {
        abort(403, 'You are not logged in as an office user.');
    }

    // Fetch office accounts from Firebase
    $officeAccounts = $this->database
        ->getReference('office_accounts')
        ->getValue();

    $foundOfficeUser = null;

    if ($officeAccounts) {
        foreach ($officeAccounts as $uid => $office) {
            if (($office['email'] ?? null) === $officeEmail) {
                $foundOfficeUser = $office;
                $foundOfficeUser['uid'] = $uid;
                break;
            }
        }
    }

    if (!$foundOfficeUser) {
        abort(404, 'Office user profile not found.');
    }

    // Optional metadata (same pattern as admin)
    $foundOfficeUser['login_count'] = $foundOfficeUser['login_count'] ?? 0;
    $foundOfficeUser['last_login']  = $foundOfficeUser['last_login'] ?? null;

    return view('volunteers-offices.profile', [
        'officeUser' => $foundOfficeUser,
    ]);
}





public function showProfile()
{
    // Fetch admin email from session
    $adminEmail = session('volunteers_admin.email'); // assuming you stored the admin array on login

    if (!$adminEmail) {
        abort(403, 'You are not logged in as admin.');
    }

    $adminsData = $this->database->getReference('admins')->getValue();

    $foundAdmin = null;

    if ($adminsData) {
        foreach ($adminsData as $uid => $admin) {
            if (($admin['email'] ?? null) === $adminEmail) {
                $foundAdmin = $admin;
                $foundAdmin['uid'] = $uid;
                break;
            }
        }
    }

    if (!$foundAdmin) {
        abort(404, 'Admin profile not found.');
    }

    // Optionally, add extra fields like temporary statistics if needed
    $foundAdmin['login_count'] = $foundAdmin['login_count'] ?? 0;
    $foundAdmin['last_login'] = $foundAdmin['last_login'] ?? null;

    $offices = $this->database->getReference('offices')->getValue() ?? [];

    return view('volunteers-admin.profile', [
        'admin' => $foundAdmin,
        'offices' => $offices,
    ]);
}




public function showScholarsAdminProfile()
{
    // Fetch admin email from session
    $adminEmail = session('scholars_admin.email');

    if (!$adminEmail) {
        abort(403, 'You are not logged in as admin.');
    }

    $adminsData = $this->database->getReference('admins')->getValue();

    $foundAdmin = null;

    if ($adminsData) {
        foreach ($adminsData as $uid => $admin) {
            if (($admin['email'] ?? null) === $adminEmail) {
                $foundAdmin = $admin;
                $foundAdmin['uid'] = $uid;
                break;
            }
        }
    }

    if (!$foundAdmin) {
        abort(404, 'Admin profile not found.');
    }

    // Optionally, add extra fields like temporary statistics if needed
    $foundAdmin['login_count'] = $foundAdmin['login_count'] ?? 0;
    $foundAdmin['last_login'] = $foundAdmin['last_login'] ?? null;

    $offices = $this->database->getReference('offices')->getValue() ?? [];

    return view('scholars-admin.profile', [
        'admin' => $foundAdmin,
        'offices' => $offices,
    ]);
}







}
