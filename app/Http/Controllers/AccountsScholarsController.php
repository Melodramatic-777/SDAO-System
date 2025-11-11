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

class AccountsScholarsController extends Controller 
{
    protected $database;

    public function __construct() 
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    /**
     * Show paginated scholars list
     */
    public function showScholarsPage(Request $request) 
    {
        // Fetch all scholars from Firebase
        $scholarsData = $this->database->getReference('scholars')->getValue();
        $scholars = [];

        if ($scholarsData) {
        foreach ($scholarsData as $academicYear => $terms) {
            foreach ($terms as $term => $types) {  // ✅ NEW level loop
                foreach ($types as $scholarshipType => $records) {
                    foreach ($records as $scholar) {
                        $scholars[] = [
                            'student_no'       => $scholar['student_no'] ?? '',
                            'email'            => $scholar['email'] ?? '',
                            'first_name'       => $scholar['first_name'] ?? '',
                            'middle_name'      => $scholar['middle_name'] ?? '',
                            'last_name'        => $scholar['last_name'] ?? '',
                            'school'           => $scholar['school'] ?? '',
                            'program'          => $scholar['program'] ?? '',
                            'birthday'         => $scholar['birthday'] ?? '',
                            'scholarship_type' => $scholarshipType,
                            'academic_year'    => $academicYear,
                            'term'             => $term, // ✅ include term in table dataset
                            'created_at'       => $scholar['created_at'] ?? ''
                        ];
                    }
                }
            }
        }

$collection = collect($scholars);

// ✅ Extract dynamic filter data
$academicYears = $collection->pluck('academic_year')->unique()->sortDesc()->values();
$terms         = $collection->pluck('term')->unique()->sort()->values();
$scholarshipTypes = $collection->pluck('scholarship_type')->unique()->sort()->values();
$programs         = $collection->pluck('program')->unique()->sort()->values();
$schools          = $collection->pluck('school')->unique()->sort()->values();

// ✅ Apply Filters
if ($request->filled('academic_year')) {
    $collection = $collection->where('academic_year', $request->academic_year);
}

if ($request->filled('term')) {
    $collection = $collection->where('term', $request->term);
}
if ($request->filled('scholarship_type')) {
    $collection = $collection->where('scholarship_type', $request->scholarship_type);
}

if ($request->filled('program')) {
    $collection = $collection->where('program', $request->program);
}

if ($request->filled('school')) {
    $collection = $collection->where('school', $request->school);
}

// ✅ PAGINATE AFTER FILTERING
$perPage = 10;
$page = $request->get('page', 1);
$total = $collection->count();
$results = $collection->slice(($page - 1) * $perPage, $perPage)->values();


$paginatedScholars = (new LengthAwarePaginator(
    $results,
    $total,
    $perPage,
    $page,
    ['path' => request()->url()]
))->appends(request()->query());

$academicYears = collect($scholars)->pluck('academic_year')->unique()->sortDesc()->values();
$latestYear = $academicYears->first();

// Filter scholars for latest A.Y.
$filtered = collect($scholars)->where('academic_year', $latestYear);

// Determine latest term in sequence (1st → 2nd → 3rd)
$termOrder = ["1st", "2nd", "3rd"];
$latestTerm = $filtered->pluck('term')
    ->unique()
    ->sort(function($a, $b) use ($termOrder) {
        return array_search($a, $termOrder) <=> array_search($b, $termOrder);
    })
    ->last();

return view('scholars-admin.scholars_accounts', [
    'scholars'         => $paginatedScholars,
    'scholarshipTypes' => $scholarshipTypes,
    'programs'         => $programs,
    'schools'          => $schools,
    'academicYears'    => $academicYears,
    'terms'            => $terms,
    'latestYear'       => $latestYear,
    'latestTerm'       => $latestTerm   
]);
    }
}

    /**
     * Import scholars via uploaded Excel/CSV
     */
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
        $emailsToSend = [];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray(null, true, true, true);

            // Academic year is in row 2, col A
$academicYear = $request->academic_year;
$term = $request->term;
            foreach ($rows as $index => $row) {
                if ($index < 5) continue; // skip headers

                $studentNo = trim($row['A'] ?? '');
                $email     = trim($row['B'] ?? '');
                $name      = trim($row['C'] ?? '');
                $school    = trim($row['D'] ?? '');
                $program   = trim($row['E'] ?? '');
                $birthday  = trim($row['F'] ?? '');

                if (empty($studentNo)) {
                    Log::warning("⚠️ Skipped row {$index} - student number empty");
                    continue;
                }

                // Split name
                $lastName = '';
                $firstName = '';
                $middleName = '';

                if (strpos($name, ',') !== false) {
                    [$lastNamePart, $firstMiddlePart] = explode(',', $name, 2);
                    $lastName = trim($lastNamePart);
                    $firstMiddle = trim(preg_replace('/\s+/', ' ', $firstMiddlePart));
                    $firstNameParts = explode(' ', $firstMiddle);
                    $firstName = array_shift($firstNameParts);
                    $middleName = implode(' ', $firstNameParts);
                } else {
                    $lastName = trim($name);
                }

                // Password
                $tempPass = Str::random(8);
                $hashed = Hash::make($tempPass);

                // Save to Firebase
                $this->database
                      ->getReference("scholars/{$academicYear}/{$term}/{$sheetName}/" . Str::uuid())
                    ->set([
                        'student_no'       => $studentNo,
                        'email'            => $email,
                        'first_name'       => $firstName,
                        'middle_name'      => $middleName,
                        'last_name'        => $lastName,
                        'school'           => $school,
                        'program'          => $program,
                        'birthday'         => $birthday,
                        'scholarship_type' => $sheetName,
                        'academic_year'    => $academicYear,
                        'term'             => $term, // ✅ NEW
                        'password'         => $hashed,
                        'created_at'       => now()->toDateTimeString(),
                    ]);

                // Queue email if exists
                if (!empty($email)) {
                    $emailsToSend[] = [
                        'full_name' => "{$firstName} {$middleName} {$lastName}",
                        'email'     => $email,
                        'temp_pass' => $tempPass
                    ];
                }

                Log::info("✅ Imported row {$index}: {$studentNo} - {$lastName}, {$firstName} {$middleName}");
            }
        }

        // Send emails after import
        foreach ($emailsToSend as $data) {
            try {
                Mail::to($data['email'])->queue(
                    new TemporaryCredentialsMailable(
                        $data['full_name'],
                        $data['email'],
                        $data['temp_pass']
                    )
                );
            } catch (\Exception $e) {
                Log::error("❌ Failed to send email to {$data['email']}: " . $e->getMessage());
            }
        }

        return back()->with('success', '✅ Scholars imported and emails sent!');
    }


    
public function showStudentVolunteers(Request $request)
{
    $scholarsData = $this->database->getReference('scholars')->getValue() ?? [];

    if (!$scholarsData) {
        return view('volunteers-admin.student-volunteers', [
            'scholars' => [],
            'scholarshipTypes' => [],
            'offices' => [],
            'latestYear' => null
        ]);
    }

    // ✅ Get latest academic year
    $academicYears = array_keys($scholarsData);
    rsort($academicYears);
    $latestYear = $academicYears[0];

    $flattened = [];

    foreach ($scholarsData[$latestYear] as $term => $types) {
        foreach ($types as $scholarshipType => $records) {
            foreach ($records as $scholarId => $scholar) {

                $flattened[] = [
                    'student_no'       => $scholar['student_no'] ?? '',
                    'first_name'       => $scholar['first_name'] ?? '',
                    'middle_name'      => $scholar['middle_name'] ?? '',
                    'last_name'        => $scholar['last_name'] ?? '',
                    'suffix'           => $scholar['suffix'] ?? '',
                    'volunteer_hours'  => $scholar['volunteer_hours'] ?? 0,
                    'scholarship_type' => $scholarshipType,
                    'term'             => $term,
                    'academic_year'    => $latestYear
                ];
            }
        }
    }

    $collection = collect($flattened);

    // ✅ Filters
    if ($request->filled('scholarship_type')) {
        $collection = $collection->where('scholarship_type', $request->scholarship_type);
    }

    if ($request->filled('search')) {
        $search = strtolower($request->search);
        $collection = $collection->filter(function ($scholar) use ($search) {
            $fullName = strtolower(($scholar['first_name'] ?? '') . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''));
            return str_contains($fullName, $search);
        });
    }

    // ✅ Pagination
    $perPage = 10;
    $page = $request->get('page', 1);
    $scholarsPaginated = new LengthAwarePaginator(
        $collection->forPage($page, $perPage)->values(),
        $collection->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    // ✅ Scholarship Types for Filter Dropdown
    $scholarshipTypes = $collection->pluck('scholarship_type')->unique()->values();

    return view('volunteers-admin.student-volunteers', [
        'scholars' => $scholarsPaginated,
        'scholarshipTypes' => $scholarshipTypes,
        'latestYear' => $latestYear
    ]);
}

public function viewStudentProfile($student_no)
{
    $scholarsData = $this->database->getReference('scholars')->getValue();

    $foundStudent = null;

    foreach ($scholarsData as $academicYear => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $scholarshipType => $students) {
                foreach ($students as $record) {
                    if (($record['student_no'] ?? null) == $student_no) {
                        $foundStudent = $record;
                        $foundStudent['academic_year'] = $academicYear;
                        $foundStudent['term'] = $term;
                        $foundStudent['scholarship_type'] = $scholarshipType;
                        break 4;
                    }
                }
            }
        }
    }

    if (!$foundStudent) {
        abort(404, 'Student not found.');
    }

    // Dummy placeholders temporarily (since you haven't linked volunteer hours history yet)
    $foundStudent['hours_completed'] = $record['volunteer_hours'] ?? 0;
    $foundStudent['activities'] = 0;
    $foundStudent['hours_month'] = 0;
    $foundStudent['remaining_hours'] = max(0, 10 - ($record['volunteer_hours'] ?? 0));

    // Offices (for dropdown)
    $offices = $this->database->getReference('offices')->getValue() ?? [];

    return view('volunteers-admin.student-volunteer-detail', [
        'studentData' => $foundStudent,
        'offices' => $offices
    ]);
}


public function scholarProfile()
{

$student_no = session('scholar_accounts.student_no');

    if (!$student_no) {
        abort(403, 'Not logged in.');
    }

    $scholarsData = $this->database->getReference('scholars')->getValue();

    $foundStudent = null;

    foreach ($scholarsData as $academicYear => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $scholarshipType => $students) {
                foreach ($students as $record) {
                    if (($record['student_no'] ?? null) == $student_no) {
                        $foundStudent = $record;
                        $foundStudent['academic_year'] = $academicYear;
                        $foundStudent['term'] = $term;
                        $foundStudent['scholarship_type'] = $scholarshipType;
                        break 4;
                    }
                }
            }
        }
    }

    if (!$foundStudent) {
        abort(404, 'Scholar profile not found.');
    }

    // Add temporary volunteer fields like the admin version
    $foundStudent['hours_completed'] = $record['volunteer_hours'] ?? 0;
    $foundStudent['activities'] = 0;
    $foundStudent['hours_month'] = 0;
    $foundStudent['remaining_hours'] = max(0, 10 - ($record['volunteer_hours'] ?? 0));

    $offices = $this->database->getReference('offices')->getValue() ?? [];

    return view('scholars.profile', [
        'studentData' => $foundStudent,
        'offices' => $offices

    ]);
}


}
