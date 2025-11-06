<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class ScholarRecordsController extends Controller
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
     * Show all scholars with their GWA records grouped by scholarship type
     */

    public function index(Request $request)
{
    $scholarsData = $this->database->getReference('scholars')->getValue() ?? [];
    $scholarshipTypes = $this->database->getReference('scholarship_types')->getValue() ?? [];

    // Convert scholarship types into an easy lookup array
    $typeLookup = [];
    foreach ($scholarshipTypes as $typeId => $type) {
        $typeLookup[$type['scholarship_name']] = [
            'grade_requirement'  => $type['grade_requirement'],
            'eligibility_cutoff' => $type['eligibility_cutoff']
        ];
    }

    $gwaRecords = [];

    foreach ($scholarsData as $academicYear => $terms) {
        foreach ($terms as $term => $types) {
            foreach ($types as $scholarshipType => $scholars) {
                foreach ($scholars as $id => $scholar) {

                    // ✅ MATCH SCHOLARSHIP TYPE REQUIREMENTS
                    $gradeRequirement = $typeLookup[$scholarshipType]['grade_requirement'] ?? null;
                    $eligibilityCutoff = $typeLookup[$scholarshipType]['eligibility_cutoff'] ?? null;

                    // Get GWA Records
                    $gwaData = $scholar['gwa_records'][$academicYear] ?? [
                        'gwa_1st' => null,
                        'gwa_2nd' => null,
                        'gwa_3rd' => null,
                        'status'  => 'Eligible'
                    ];

                    $gwaRecords[] = [
                        'firebase_id'       => $id,
                        'student_number'    => $scholar['student_no'] ?? '',
                        'scholar_name'      => trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($scholar['last_name'] ?? '')),
                        'program'           => $scholar['program'] ?? '',
                        'school'            => $scholar['school'] ?? '',
                        'academic_year'     => $academicYear,
                        'term'              => $term,
                        'scholarship_type'  => $scholarshipType,

                        // ✅ Use the values from scholarship_types, not scholar record
                        'grade_requirement'  => $gradeRequirement,
                        'eligibility_cutoff' => $eligibilityCutoff,

                        'gwa_1st'           => $gwaData['gwa_1st'] ?? null,
                        'gwa_2nd'           => $gwaData['gwa_2nd'] ?? null,
                        'gwa_3rd'           => $gwaData['gwa_3rd'] ?? null,
                        'status'            => $gwaData['status'] ?? 'Eligible'
                    ];
                }
            }
        }
    }

    $collection = collect($gwaRecords);

    // ✅ Filters
    if ($request->filled('academic_year')) $collection = $collection->where('academic_year', $request->academic_year);
    if ($request->filled('term')) $collection = $collection->where('term', $request->term);
    if ($request->filled('scholarship_type')) $collection = $collection->where('scholarship_type', $request->scholarship_type);
    if ($request->filled('school')) $collection = $collection->where('school', $request->school);
    if ($request->filled('program')) $collection = $collection->where('program', $request->program);

    // ✅ Dropdown data
    $academicYears    = collect($gwaRecords)->pluck('academic_year')->unique()->sortDesc()->values();
    $terms            = collect($gwaRecords)->pluck('term')->unique()->sort()->values();
    $scholarshipTypes = collect($gwaRecords)->pluck('scholarship_type')->unique()->sort()->values();
    $schools          = collect($gwaRecords)->pluck('school')->unique()->sort()->values();
    $programs         = collect($gwaRecords)->pluck('program')->unique()->sort()->values();

    return view('scholars-admin.scholars', [
        'gwaRecords'       => $collection->values(),
        'academicYears'    => $academicYears,
        'terms'            => $terms,
        'scholarshipTypes' => $scholarshipTypes,
        'schools'          => $schools,
        'programs'         => $programs
    ]);
}



    private function getStatusFromGwa($scholar, $scholarshipType)
    {
        $types = $this->database
            ->getReference('scholarship_types')
            ->orderByChild('scholarship_name')
            ->equalTo($scholarshipType)
            ->getValue();

        $type = $types ? reset($types) : null;
        if (!$type) {
            return 'Eligible';
        }

        $gradeRequirement = $type['grade_requirement'];
        $eligibilityCutoff = $type['eligibility_cutoff'];

        $gwaValues = [];
        foreach (['gwa_1st', 'gwa_2nd', 'gwa_3rd'] as $field) {
            if (isset($scholar[$field]) && is_numeric($scholar[$field])) {
                $gwaValues[] = $scholar[$field];
            }
        }

        $probationCount = 0;
        $revoked = false;

        foreach ($gwaValues as $gwa) {
            if ($gwa <= $gradeRequirement) {
                continue;
            } elseif ($gwa <= $eligibilityCutoff) {
                $probationCount++;
            } else {
                $revoked = true;
            }
        }

        if ($revoked || $probationCount >= 2) {
            return 'Revoked';
        } elseif ($probationCount === 1) {
            return 'Probationary';
        }
        return 'Eligible';
    }

    /**
     * Update a single term's GWA
     */
    public function updateGwa(Request $request)
    {
        $request->validate([
            'firebase_id'      => 'required|string',
            'academic_year'    => 'required|string',
            'scholarship_type' => 'required|string',
            'term'             => 'required|string|in:1st,2nd,3rd',
            'gwa'              => 'required|numeric'
        ]);

        $firebaseId = $request->firebase_id;
        $term = $request->term;
        $gwa = $request->gwa;

        $path = "scholars/{$request->academic_year}/{$request->scholarship_type}/{$firebaseId}";
        $ref = $this->database->getReference($path);
        $scholar = $ref->getValue();

        if (!$scholar) {
            return response()->json(['success' => false, 'message' => 'Scholar not found'], 404);
        }

        // Ensure gwa_records node exists
        $gwaPath = "$path/gwa_records/{$request->academic_year}";
        $gwaRef = $this->database->getReference($gwaPath);
        $gwaRecord = $gwaRef->getValue() ?? [];

        // Update only the selected term
        if ($term === '1st') $gwaRecord['gwa_1st'] = $gwa;
        if ($term === '2nd') $gwaRecord['gwa_2nd'] = $gwa;
        if ($term === '3rd') $gwaRecord['gwa_3rd'] = $gwa;

        // Recalculate status
        $status = $this->getStatusFromGwa($gwaRecord, $request->scholarship_type);
        $gwaRecord['status'] = $status;

        $gwaRef->set($gwaRecord);

        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }

    /**
     * Bulk save all GWA updates
     */
public function saveAllGwa(Request $request)
{
    $request->validate([
        'scholars' => 'required|array',
    ]);

    foreach ($request->scholars as $scholar) {

        $path = "scholars/{$scholar['academic_year']}/{$scholar['term']}/{$scholar['scholarship_type']}";
        $ref = $this->database->getReference($path)->getValue() ?? [];

        foreach ($ref as $id => $record) {
            if (($record['student_no'] ?? '') === $scholar['student_number']) {

                $gwaPath = "$path/$id/gwa_records/{$scholar['academic_year']}";

                $this->database->getReference($gwaPath)->update([
                    'gwa_1st' => $scholar['gwa_1st'] ?: null,
                    'gwa_2nd' => $scholar['gwa_2nd'] ?: null,
                    'gwa_3rd' => $scholar['gwa_3rd'] ?: null,
                    'status'  => $scholar['status'] ?: 'Eligible'
                ]);

                break;
            }
        }
    }

    return response()->json(['success' => true]);
}


}
