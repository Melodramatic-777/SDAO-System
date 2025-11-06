<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Mail;
use App\Mail\TemporaryCredentialsMailable;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountsVolunteersController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

public function showAspiringScholarsPage(Request $request)
{
    // Get aspiring scholar accounts
    $aspiringAccounts = $this->database->getReference('aspiring_scholar_accounts')->getValue();
    $aspiringScholars = [];

    if ($aspiringAccounts) {
foreach ($aspiringAccounts as $id => $acc) {
            $aspiringScholars[] = [
                'id' => $id,
                'first_name' => $acc['first_name'],
                'middle_name' => $acc['middle_name'] ?? '',
                'last_name' => $acc['last_name'],
                'suffix' => $acc['suffix'] ?? '',
                'email' => $acc['email'],
                'school' => $acc['school'] ?? 'N/A',   // ✅ ADD THIS
                 'program' => $acc['program'] ?? 'N/A', // ✅ ADD THIS
                'created_at' => $acc['created_at'] ?? '',
            ];
        }
    }

    // ✅ Fetch all schools
    $schools = $this->database->getReference('schools')->getValue();
    $schoolsList = [];

    if ($schools) {
        foreach ($schools as $id => $school) {
            $schoolsList[] = [
                'id' => $id,
                'name' => $school['name'],
                'programs' => $school['programs'] ?? []
            ];
        }
    }

    // Pagination
    $perPage = 10;
    $page = $request->get('page', 1);
    $collection = collect($aspiringScholars);

if ($request->filled('school')) {
    $collection = $collection->where('school', $request->school);
}

if ($request->filled('program')) {
    $collection = $collection->where('program', $request->program);
}

$total = $collection->count();
    $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
    $paginatedAspiringScholars = new LengthAwarePaginator($results, $total, $perPage, $page, [
        'path' => request()->url(),
        'query' => request()->query(),
    ]);

    return view('volunteers-admin.accounts', [
        'aspiringScholars' => $paginatedAspiringScholars,
        'schools' => $schoolsList, // ✅ send to view
    ]);
}

public function storeAspiringScholarAccount(Request $request)
{
    $data = $request->validate([
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
        'school' => 'required|string',
        'program' => 'required|string',
    ]);

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('aspiring_scholar_accounts/' . Str::uuid())
        ->set([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'school' => $data['school'],
            'program' => $data['program'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'Aspiring Scholar account created!');
}

public function archiveAspiringScholar(Request $request)
{
    $selected = $request->input('selected_accounts');

    if (!$selected || count($selected) == 0) {
        return back()->with('error', 'No accounts selected.');
    }

    foreach ($selected as $id) {

        $accountRef = $this->database->getReference("aspiring_scholar_accounts/$id");
        $account = $accountRef->getValue();

        if ($account) {
            // ✅ Move to archive
            $this->database->getReference("archived_accounts/aspiring_scholars/$id")->set($account);

            // ✅ Remove from active list
            $accountRef->remove();
        }
    }

    return back()->with('success', 'Selected accounts have been archived.');
}



}
