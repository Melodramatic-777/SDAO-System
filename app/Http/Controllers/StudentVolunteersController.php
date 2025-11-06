<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class StudentVolunteersController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

public function viewStudent($student_no)
{
    $scholarsData = $this->database->getReference('scholars')->getValue();
    $studentData = null;

    if ($scholarsData) {
        foreach ($scholarsData as $academicYear => $types) {
            foreach ($types as $scholarshipType => $records) {
                foreach ($records as $id => $student) {
                    if (($student['student_no'] ?? null) == $student_no) {
                        $studentData = $student;
                        break 3; // ✅ exit all loops
                    }
                }
            }
        }
    }

    if (!$studentData) {
        abort(404, 'Student not found');
    }

    // ✅ Fetch offices for dropdown
    $officesData = $this->database->getReference('offices')->getValue() ?? [];
    $offices = [];

    foreach ($officesData as $office) {
        if (isset($office['name'])) {
            $offices[] = ['name' => $office['name']];
        }
    }

    return view('volunteers-admin.student-volunteer-detail', [
        'studentData' => $studentData,
        'offices' => $offices, // ✅ passed to view
    ]);
}

}