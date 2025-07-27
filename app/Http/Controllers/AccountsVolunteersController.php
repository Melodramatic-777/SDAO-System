<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Mail;
use App\Mail\TemporaryCredentialsMailable;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


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

    public function showAccountsPage(Request $request)  // add Request here to get page
    {
        $aspiringAccounts = $this->database->getReference('aspiring_scholar_accounts')->getValue();
        $aspiringScholars = [];

        if ($aspiringAccounts) {
            foreach ($aspiringAccounts as $acc) {
                $aspiringScholars[] = [
                    'first_name' => $acc['first_name'],
                    'middle_name' => $acc['middle_name'] ?? '',
                    'last_name' => $acc['last_name'],
                    'suffix' => $acc['suffix'] ?? '',
                    'email' => $acc['email'],
                    'created_at' => $acc['created_at'] ?? '',
                ];
            }
        }

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
                    'first_name' => $acc['first_name'],
                    'middle_name' => $acc['middle_name'] ?? '',
                    'last_name' => $acc['last_name'],
                    'suffix' => $acc['suffix'] ?? '',
                    'email' => $acc['email'],
                    'created_at' => $acc['created_at'] ?? '',
                ];
            }
        }

        $scholarsData = $this->database->getReference('scholar_accounts')->getValue();
        $scholars = [];
        if ($scholarsData) {
            foreach ($scholarsData as $scholar) {
                $scholars[] = [
                    'student_no' => $scholar['student_no'],
                    'first_name' => $scholar['first_name'],
                    'middle_name' => $scholar['middle_name'] ?? '',
                    'last_name' => $scholar['last_name'],
                    'school' => $scholar['school'] ?? '',
                    'program' => $scholar['program'] ?? '',
                    'birthday' => $scholar['birthday'],
                    'scholarship_type' => $scholar['scholarship_type'] ?? '',
                ];
            }
        }

        // Pagination setup
        $perPage = 5;
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

        $paginatedAspiringScholars = $paginateArray($aspiringScholars);
        $paginatedScholars = $paginateArray($scholars);
        $paginatedOffices = $paginateArray($offices);
        $paginatedOfficeAccounts = $paginateArray($officeAccounts);

        return view('volunteers-admin.accounts', [
            'aspiringScholars' => $paginatedAspiringScholars,
            'officeAccounts' => $paginatedOfficeAccounts,
            'scholars' => $paginatedScholars,
            'offices' => $paginatedOffices,
        ]);
    }

public function importScholars(Request $request)
{
    $request->validate([
        'scholars_file' => 'required|mimes:xlsx,csv',
    ]);

    $fullPath = $request->file('scholars_file')->getRealPath();

    if (!file_exists($fullPath)) {
        return back()->with('error', "❌ File not found at: {$fullPath}");
    }

    $spreadsheet = IOFactory::load($fullPath);

    foreach ($spreadsheet->getSheetNames() as $sheetName) {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        $rows = $sheet->toArray(null, true, true, true); // get rows with A, B, C, etc.

        foreach ($rows as $index => $row) {
            // Skip header rows 1–4, start at row 5
            if ($index < 5) continue;

            $studentNo = trim($row['A'] ?? '');
            $email = trim($row['B'] ?? '');
            $name = trim($row['C'] ?? '');
            $school = trim($row['D'] ?? '');
            $program = trim($row['E'] ?? '');
            $birthday = trim($row['F'] ?? '');

            if (empty($studentNo)) {
                Log::warning("⚠️ Skipped row {$index} - student number empty");
                continue;
            }

            $lastName = '';
            $firstName = '';
            $middleName = '';

            // Handle "LastName, FirstName MiddleName" format
            if (strpos($name, ',') !== false) {
                [$lastNamePart, $firstMiddlePart] = explode(',', $name, 2);
                $lastName = trim($lastNamePart);
                $firstMiddle = trim(preg_replace('/\s+/', ' ', $firstMiddlePart)); // normalize spaces

                $firstNameParts = explode(' ', $firstMiddle);
                $firstName = array_shift($firstNameParts);
                $middleName = implode(' ', $firstNameParts);
            } else {
                $lastName = trim($name);
            }

            // Save to Firebase
            $this->database->getReference('scholar_accounts/' . Str::uuid())
                ->set([
                    'student_no' => $studentNo,
                    'email' => $email,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'school' => $school,
                    'program' => $program,
                    'birthday' => $birthday,
                    'scholarship_type' => $sheetName, // sheet tab name
                    'created_at' => now()->toDateTimeString(),
                ]);

            Log::info("✅ Imported row {$index}: {$studentNo} - {$lastName}, {$firstName} {$middleName}");
        }
    }

    return back()->with('success', '✅ Scholars imported successfully!');
}




    public function storeAspiringScholarAccount(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'suffix' => 'nullable|string',
            'email' => 'required|email',
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
        'office_id'   => 'required|string', // new: required office
        'first_name'  => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name'   => 'required|string',
        'suffix'      => 'nullable|string',
        'email'       => 'required|email',
    ]);

    // Fetch office name from Firebase using office_id
    $office = $this->database->getReference('offices/' . $data['office_id'])->getValue();
    $officeName = $office['name'] ?? 'Unknown Office';

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('office_accounts/' . Str::uuid())
        ->set([
            'office_id'   => $data['office_id'],
            'office_name' => $officeName, // save name for display
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

}
