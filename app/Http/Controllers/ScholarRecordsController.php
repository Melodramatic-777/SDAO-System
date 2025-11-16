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


    public function indexRecord(Request $request)
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
    $latestAcademicYear = $academicYears->first();
    $latestTerm = $terms->last();

    return view('scholars-admin.scholars', [
        'gwaRecords'       => $collection->values(),
        'academicYears'    => $academicYears,
        'terms'            => $terms,
        'scholarshipTypes' => $scholarshipTypes,
        'schools'          => $schools,
        'programs'         => $programs,
        'latestAcademicYear' => $latestAcademicYear,
        'latestTerm' => $latestTerm
    ]);
}


public function saveGwa(Request $request)
{
    $records = $request->records;

    foreach ($records as $rec) {

        $path = "scholars/{$rec['academic_year']}/{$rec['term']}/{$rec['scholarship_type']}/{$rec['firebase_id']}/gwa_records/{$rec['academic_year']}";

        $this->database->getReference($path)->update([
            'gwa_1st' => $rec['gwa_1st'],
            'gwa_2nd' => $rec['gwa_2nd'],
            'gwa_3rd' => $rec['gwa_3rd'],
            'status'  => $rec['status']
        ]);
    }

    return response()->json(['message' => 'Saved']);
}


    

}
