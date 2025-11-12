<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class ScholarsDashboardController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

public function dashboard()
{
    $ref = $this->database->getReference('scholars');
    $scholars = $ref->getValue();

    $total = 0;
    $probationary = 0;
    $byScholarship = [];

    if ($scholars) {
        foreach ($scholars as $academicYear => $terms) {
            foreach ($terms as $term => $types) {
                foreach ($types as $scholarshipType => $students) {
                    foreach ($students as $studentId => $studentData) {
                        $total++;

                        // ✅ Count per scholarship type
                        if (!isset($byScholarship[$scholarshipType])) {
                            $byScholarship[$scholarshipType] = 0;
                        }
                        $byScholarship[$scholarshipType]++;

                        // ✅ Count probationary
                        if (
                            isset($studentData['gwa_records'][$academicYear]['status']) &&
                            strtolower($studentData['gwa_records'][$academicYear]['status']) === 'probationary'
                        ) {
                            $probationary++;
                        }
                    }
                }
            }
        }
    }

    return view('scholars-admin.dashboard', compact('total', 'probationary', 'byScholarship'));
}

}

