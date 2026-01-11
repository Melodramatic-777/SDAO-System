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
    $scholarsData = $this->database
        ->getReference('scholars')
        ->getValue() ?? [];

    $scholars = [];

    if (!is_array($scholarsData)) {
        $scholarsData = [];
    }

    foreach ($scholarsData as $academicYear => $terms) {
        if (!is_array($terms)) continue;

        foreach ($terms as $term => $types) {
            if (!is_array($types)) continue;

            foreach ($types as $scholarshipType => $records) {
                if (!is_array($records)) continue;

                foreach ($records as $scholarId => $scholar) {
                    if (!is_array($scholar)) continue;

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
                        'term'             => $term,
                        'created_at'       => $scholar['created_at'] ?? '',
                        'gwa_records'      => is_array($scholar['gwa_records'] ?? null)
                                                ? $scholar['gwa_records']
                                                : [],
                    ];
                }
            }
        }
    }

    /* -------------------------------------------
     * COLLECTION + FILTERS (UNCHANGED)
     * ------------------------------------------- */
    $collection = collect($scholars);

    $academicYears = $collection->pluck('academic_year')->unique()->sortDesc()->values();
    $terms = $collection->pluck('term')->unique()->sort()->values();
    $scholarshipTypes = $collection->pluck('scholarship_type')->unique()->sort()->values();
    $programs = $collection->pluck('program')->unique()->sort()->values();
    $schools = $collection->pluck('school')->unique()->sort()->values();

    $latestYear = $academicYears->first();

    $latestTerm = $collection
        ->where('academic_year', $latestYear)
        ->pluck('term')
        ->unique()
        ->sortBy(fn($t) => ['1st'=>1,'2nd'=>2,'3rd'=>3][$t] ?? 99)
        ->last();

    // Default filter
    if ($request->filled('academic_year')) {
        $collection = $collection->where('academic_year', $request->academic_year);
    } else {
        $collection = $collection->where('academic_year', $latestYear)
                                 ->where('term', $latestTerm);
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

    // Pagination
    $perPage = 10;
    $page = $request->get('page', 1);

    $paginatedScholars = new LengthAwarePaginator(
        $collection->forPage($page, $perPage)->values(),
        $collection->count(),
        $perPage,
        $page,
        ['path' => request()->url()]
    );

    return view('scholars-admin.scholars_accounts', [
        'scholars'         => $paginatedScholars,
        'scholarshipTypes' => $scholarshipTypes,
        'programs'         => $programs,
        'schools'          => $schools,
        'academicYears'    => $academicYears,
        'terms'            => $terms,
        'latestYear'       => $latestYear,
        'latestTerm'       => $latestTerm,
    ]);
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
        'term'             => $term,
        'password'         => $hashed,
        'created_at'       => now()->toDateTimeString(),
        'status'           => 'Eligible', 
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

    // If empty DB
    if (!$scholarsData) {
        return view('volunteers-admin.student-volunteers', [
            'scholars' => [],
            'academicYears' => [],
            'terms' => [],
            'scholarshipTypes' => [],
            'programs' => [],
            'schools' => [],
            'latestYear' => null
        ]);
    }

    // Extract all scholars (whole DB)
    $flattened = [];

    foreach ($scholarsData as $year => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $scholarshipType => $records) {
                foreach ($records as $id => $scholar) {

                    $flattened[] = [
                        'student_no'       => $scholar['student_no'] ?? '',
                        'email'            => $scholar['email'] ?? '',
                        'first_name'       => $scholar['first_name'] ?? '',
                        'middle_name'      => $scholar['middle_name'] ?? '',
                        'last_name'        => $scholar['last_name'] ?? '',
                        'suffix'           => $scholar['suffix'] ?? '',
                        'birthday'         => $scholar['birthday'] ?? '',
                        'school'           => $scholar['school'] ?? '',
                        'program'          => $scholar['program'] ?? '',
                        'scholarship_type' => $scholarshipType,
                        'academic_year'    => $year,
                        'term'             => $term,
                        'volunteer_hours'  => $scholar['volunteer_hours'] ?? 0,
                        'gwa_records'      => $scholar['gwa_records'] ?? []
                    ];
                }
            }
        }
    }


$aspiringData = $this->database->getReference('aspiring_scholar_accounts')->getValue() ?? [];

foreach ($aspiringData as $id => $scholar) {
    $flattened[] = [
       'student_no'       => $scholar['student_no'] ?? '',
        'email'            => $scholar['email'] ?? '',
        'first_name'       => $scholar['first_name'] ?? '',
        'middle_name'      => $scholar['middle_name'] ?? '',
        'last_name'        => $scholar['last_name'] ?? '',
        'suffix'           => $scholar['suffix'] ?? '',
        'birthday'         => $scholar['birthday'] ?? '',
        'school'           => $scholar['school'] ?? '',
        'program'          => $scholar['program'] ?? '',
        'scholarship_type' => null,
        'academic_year'    => null,
        'term'             => null,
        'volunteer_hours'  => 0,
        'gwa_records'      => [],
        'is_aspiring'      => true,
    ];
}


    $collection = collect($flattened);

    // Build dropdown data
$academicYears = $collection->pluck('academic_year')
    ->filter(fn($v) => !empty($v))
    ->unique()
    ->sortDesc()
    ->values();

$terms = $collection->pluck('term')
    ->filter(fn($v) => !empty($v))
    ->unique()
    ->sort()
    ->values();

$scholarshipTypes = $collection->pluck('scholarship_type')
    ->filter(fn($v) => !empty($v))
    ->unique()
    ->sort()
    ->values();

$programs = $collection->pluck('program')
    ->filter(fn($v) => !empty($v))
    ->unique()
    ->sort()
    ->values();

$schools = $collection->pluck('school')
    ->filter(fn($v) => !empty($v))
    ->unique()
    ->sort()
    ->values();


    // Latest Year
    $latestYear = $academicYears->first();

    // Filters
    if ($request->filled('academic_year')) {
        $collection = $collection->where('academic_year', $request->academic_year);
    }

    if ($request->filled('term')) {
        $collection = $collection->where('term', $request->term);
    }

if ($request->filled('scholarship_type')) {
    if ($request->scholarship_type === 'Aspiring Scholar') {
        $collection = $collection->where('is_aspiring', true);
    } else {
        $collection = $collection->where('scholarship_type', $request->scholarship_type);
    }
}

if (!$request->filled('scholarship_type')) {
    $collection = $collection->filter(function ($item) {
        return empty($item['is_aspiring']) || $item['is_aspiring'] == false;
    });
}

    if ($request->filled('program')) {
        $collection = $collection->where('program', $request->program);
    }

    if ($request->filled('school')) {
        $collection = $collection->where('school', $request->school);
    }

    if ($request->filled('search')) {
        $search = strtolower($request->search);
        $collection = $collection->filter(function ($item) use ($search) {
            return str_contains(strtolower($item['first_name']), $search) ||
                   str_contains(strtolower($item['last_name']), $search) ||
                   str_contains(strtolower($item['student_no']), $search);
        });
    }

    // Pagination
    $perPage = 10;
    $page = $request->get('page', 1);
    $total = $collection->count();
    $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();

    $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
        $items,
        $total,
        $perPage,
        $page,
        ['path' => $request->url()]
    );

    return view('volunteers-admin.student-volunteers', [
        'scholars' => $paginated,
        'academicYears' => $academicYears,
        'terms' => $terms,
        'scholarshipTypes' => $scholarshipTypes,
        'programs' => $programs,
        'schools' => $schools,
        'latestYear' => $latestYear
    ]);
}


public function viewStudentProfile($student_no)
{
    $scholarsData = $this->database->getReference('scholars')->getValue() ?? [];
    $aspiringData = $this->database->getReference('aspiring_scholar_accounts')->getValue() ?? [];

    $foundStudent = null;

    // 🔵 FIRST: Search in regular scholars
    foreach ($scholarsData as $academicYear => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $scholarshipType => $students) {
                foreach ($students as $record) {
                    if (($record['student_no'] ?? null) == $student_no) {
                        $foundStudent = $record;
                        $foundStudent['academic_year'] = $academicYear;
                        $foundStudent['term'] = $term;
                        $foundStudent['scholarship_type'] = $scholarshipType;
                        $foundStudent['is_aspiring'] = false;
                        break 4;
                    }
                }
            }
        }
    }

    // 🔵 SECOND: If not found, search aspiring scholars
    if (!$foundStudent) {
        foreach ($aspiringData as $id => $record) {
            if (($record['student_no'] ?? null) == $student_no) {
                $foundStudent = $record;
                $foundStudent['academic_year'] = null;
                $foundStudent['term'] = null;
                $foundStudent['scholarship_type'] = null;
                $foundStudent['is_aspiring'] = true;
                break;
            }
        }
    }

    if (!$foundStudent) {
        abort(404, 'Student not found.');
    }

    return view('volunteers-admin.student-volunteer-detail', [
        'student' => $foundStudent
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

public function showVolunteerDetail($student_no)
{
    $scholarsData = $this->database->getReference('scholars')->getValue() ?? [];

    $studentData = null;

    foreach ($scholarsData as $year => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $type => $records) {
                foreach ($records as $id => $scholar) {
                    if (($scholar['student_no'] ?? '') == $student_no) {
                        $studentData = $scholar;
                        break 4; // exit all loops
                    }
                }
            }
        }
    }

    if (!$studentData) {
        return redirect()->back()->with('error', 'Student not found.');
    }

    // Load offices (needed by your dropdown)
    $offices = $this->database->getReference('offices')->getValue() ?? [];

    return view('volunteers-admin.student-volunteer-detail', [
        'studentData' => $studentData,
        'offices'     => $offices
    ]);
}


public function showAssignFingerprint($student_no)
{


    $scholarsData = $this->database->getReference('scholars')->getValue() ?? [];

    $selectedScholar = null;

    foreach ($scholarsData as $year => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $type => $records) {
                foreach ($records as $id => $scholar) {
                    if (($scholar['student_no'] ?? null) == $student_no) {
                        $selectedScholar = $scholar;
                        $selectedScholar['firebase_key'] = $id;
                        $selectedScholar['academic_year'] = $year;
                        $selectedScholar['term'] = $term;
                        $selectedScholar['scholarship_type'] = $type;
                        break 4;
                    }
                }
            }
        }
    }

    if (!$selectedScholar) {
        abort(404, 'Scholar not found');
    }

    return view('scholars-admin.assignFingerprint', compact('selectedScholar'));
}


public function storeAssignFingerprint(Request $request, $student_no)
{
    $data = $request->validate([
        'firebase_key' => ['required', 'string'],
        'academic_year' => ['required', 'string'],
        'term' => ['required', 'string'],
        'scholarship_type' => ['required', 'string'],
        'fingerprint_slot_id' => ['required', 'integer', 'min:1'],
    ]);

    // IMPORTANT: remove "/records"
    $path = "scholars/{$data['academic_year']}/{$data['term']}/{$data['scholarship_type']}/{$data['firebase_key']}";

    $existing = $this->database->getReference($path)->getValue();
    if (!$existing) {
        return redirect()->route('scholars-admin.scholars_accounts')
            ->with('error', 'Scholar record not found in Firebase. No changes saved.');
    }

    $this->database->getReference($path)->update([
        'fingerprint_slot_id' => (int) $data['fingerprint_slot_id'],
    ]);

    return redirect()->route('scholars-admin.scholars_accounts')
        ->with('success', 'Fingerprint assigned successfully.');
    }
}
