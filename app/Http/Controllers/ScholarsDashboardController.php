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
        $eligible = 0;
        $probationary = 0;
        $revoked = 0;

        $byScholarship = [];

        // Retention per term
        $termStats = [
            "1" => ["eligible" => 0, "probationary" => 0, "revoked" => 0],
            "2" => ["eligible" => 0, "probationary" => 0, "revoked" => 0],
            "3" => ["eligible" => 0, "probationary" => 0, "revoked" => 0],
        ];

        $recentYear = null;
        $currentTerm = null;
        $recentYearTotal = 0;
        $recentYearRevoked = 0;
        $allAcademicYears = [];
        $termsByYear = [];

        if ($scholars) {

            // Collect academic years
            $allAcademicYears = array_keys($scholars);
            sort($allAcademicYears);

            // Most recent academic year
            $recentYear = end($allAcademicYears);

            // Build list of terms per academic year
            foreach ($scholars as $year => $terms) {
                $termsByYear[$year] = array_keys($terms);
            }

            foreach ($scholars as $academicYear => $terms) {
                foreach ($terms as $term => $types) {

                    foreach ($types as $scholarshipType => $students) {
                        foreach ($students as $studentId => $studentData) {

                            $total++;

                            if (!isset($byScholarship[$scholarshipType])) {
                                $byScholarship[$scholarshipType] = 0;
                            }
                            $byScholarship[$scholarshipType]++;

                            $status = strtolower($studentData['gwa_records'][$academicYear]['status'] ?? '');

                            if ($status === 'eligible') $eligible++;
                            if ($status === 'probationary') $probationary++;
                            if ($status === 'revoked') $revoked++;

                            // Normalize "1st" → "1"
                            $normalizedTerm = str_replace(['st','nd','rd'], '', $term);

                            // Count retention for recent year only
                            if ($academicYear == $recentYear && isset($termStats[$normalizedTerm])) {
                                if ($status === 'eligible') $termStats[$normalizedTerm]['eligible']++;
                                if ($status === 'probationary') $termStats[$normalizedTerm]['probationary']++;
                                if ($status === 'revoked') $termStats[$normalizedTerm]['revoked']++;
                            }

                            // Determine current term (last term in recent year)
                            if ($recentYear && isset($scholars[$recentYear])) {
                                $yrTerms = array_keys($scholars[$recentYear]);
                                sort($yrTerms);
                                $currentTerm = end($yrTerms); 
                            }

                            // Retention rate calculation input
                            if ($academicYear == $recentYear) {
                                $recentYearTotal++;
                                if ($status === 'revoked') $recentYearRevoked++;
                            }
                        }
                    }
                }
            }
        }

        // Compute retention rate
        $retentionRate = 0;
        if ($recentYearTotal > 0) {
            $retentionRate = round((($recentYearTotal - $recentYearRevoked) / $recentYearTotal) * 100, 2);
        }

        // Count scholarship distribution per term
        $scholarshipByTerm = [
            "1" => [],
            "2" => [],
            "3" => []
        ];

        if ($scholars) {
            foreach ($scholars as $academicYear => $terms) {
                foreach ($terms as $term => $types) {

                    $normalizedTerm = str_replace(['st','nd','rd'], '', $term);

                    if (!isset($scholarshipByTerm[$normalizedTerm])) continue;

                    foreach ($types as $scholarshipType => $students) {

                        if (!isset($scholarshipByTerm[$normalizedTerm][$scholarshipType])) {
                            $scholarshipByTerm[$normalizedTerm][$scholarshipType] = 0;
                        }

                        $scholarshipByTerm[$normalizedTerm][$scholarshipType] += count($students);
                    }
                }
            }
        }

        // Return to view
        return view('scholars-admin.dashboard', [
            'total'             => $total,
            'eligible'          => $eligible,
            'probationary'      => $probationary,
            'revoked'           => $revoked,
            'byScholarship'     => $byScholarship,
            'retentionRate'     => $retentionRate,
            'recentYear'        => $recentYear,
            'termStats'         => $termStats,
            'currentTerm'       => $currentTerm,
            'scholarshipByTerm' => $scholarshipByTerm,
            'allAcademicYears'  => $allAcademicYears,
            'termsByYear'       => $termsByYear
        ]);
    }
}
