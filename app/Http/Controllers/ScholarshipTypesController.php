<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class ScholarshipTypesController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function index()
    {
        $types = $this->database->getReference('scholarship_types')->getValue() ?? [];
        return view('scholars-admin.scholarships', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scholarship_name'   => 'required|string',
            'grade_requirement'  => 'required|numeric',
            'eligibility_cutoff' => 'required|numeric',
            'requirements_pdf'   => 'required|mimes:pdf|max:2048',
        ]);

        $path = $request->file('requirements_pdf')->store('scholarship_pdfs', 'public');
        $pdfUrl = asset('storage/' . $path);

        $this->database->getReference('scholarship_types')->push([
            'scholarship_name'   => $request->scholarship_name,
            'grade_requirement'  => $request->grade_requirement,
            'eligibility_cutoff' => $request->eligibility_cutoff,
            'requirements_pdf'   => $pdfUrl,
        ]);

        return redirect()->route('scholars-admin.scholarships')
            ->with('success', 'Scholarship added with PDF.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'scholarship_name'   => 'required|string',
            'grade_requirement'  => 'required|numeric',
            'eligibility_cutoff' => 'required|numeric',
            'requirements_pdf'   => 'nullable|mimes:pdf|max:2048',
        ]);

        $updateData = [
            'scholarship_name'   => $request->scholarship_name,
            'grade_requirement'  => $request->grade_requirement,
            'eligibility_cutoff' => $request->eligibility_cutoff,
        ];

        if ($request->hasFile('requirements_pdf')) {
            $path = $request->file('requirements_pdf')->store('scholarship_pdfs', 'public');
            $updateData['requirements_pdf'] = asset('storage/' . $path);
        }

        $this->database->getReference('scholarship_types/' . $id)->update($updateData);

        return redirect()->route('scholars-admin.scholarships')
            ->with('success', 'Scholarship updated.');
    }

    public function destroy($id)
    {
        $this->database->getReference('scholarship_types/' . $id)->remove();

        return redirect()->route('scholars-admin.scholarships')
            ->with('success', 'Scholarship deleted.');
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
    $gradeCutoff = $type['eligibility_cutoff'];

    $gwaValues = [];
    foreach (['gwa_1st', 'gwa_2nd', 'gwa_3rd'] as $field) {
        if (isset($scholar[$field]) && is_numeric($scholar[$field])) {
            $gwaValues[] = $scholar[$field];
        }
    }

    // Count how many times the scholar goes below grade requirement
    $belowRequirementCount = 0;

    foreach ($gwaValues as $gwa) {
        // GWA higher than grade requirement means not meeting requirement
        if ($gwa > $gradeRequirement) {
            $belowRequirementCount++;
        }
    }

    if ($belowRequirementCount >= 2) {
        return 'Revoked';
    } elseif ($belowRequirementCount === 1) {
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
