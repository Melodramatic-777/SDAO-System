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

    /* ============================================================
     *  ADMIN DASHBOARD (scholars-admin.dashboard)
     * ============================================================ */
    public function dashboard()
    {
        /* -------------------------------------------
         * FETCH SCHOLARS COLLECTION
         * ------------------------------------------- */
        $ref = $this->database->getReference('scholars');
        $scholars = $ref->getValue() ?? [];

        /* -------------------------------------------
         * FETCH OTSV LIST FOR SIDE PANEL
         * ------------------------------------------- */
        $otsvList = $this->database->getReference('on_the_spot_volunteers')->getValue() ?? [];

        // Convert Firebase associative array → indexed
        $otsvList = $otsvList ? array_map(function ($id, $item) {
            $item['id'] = $id;
            return $item;
        }, array_keys($otsvList), $otsvList) : [];

        /* -------------------------------------------
         * SCHOLAR STATISTICS
         * ------------------------------------------- */
        $total = 0;
        $eligible = 0;
        $probationary = 0;
        $revoked = 0;
        $byScholarship = [];

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

            $allAcademicYears = array_keys($scholars);
            sort($allAcademicYears);
            $recentYear = end($allAcademicYears);

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

                            $normalizedTerm = preg_replace('/(st|nd|rd|th)/', '', $term);

                            if ($academicYear == $recentYear && isset($termStats[$normalizedTerm])) {
                                $termStats[$normalizedTerm][$status] = ($termStats[$normalizedTerm][$status] ?? 0) + 1;
                            }

                            if ($recentYear && isset($scholars[$recentYear])) {
                                $yrTerms = array_keys($scholars[$recentYear]);
                                sort($yrTerms);
                                $currentTerm = end($yrTerms);
                            }

                            if ($academicYear == $recentYear) {
                                $recentYearTotal++;
                                if ($status === 'revoked') $recentYearRevoked++;
                            }
                        }
                    }
                }
            }
        }

        $retentionRate = $recentYearTotal > 0
            ? round((($recentYearTotal - $recentYearRevoked) / $recentYearTotal) * 100, 2)
            : 0;

        $scholarshipByTerm = [
            "1" => [], "2" => [], "3" => []
        ];

        if ($scholars) {
            foreach ($scholars as $academicYear => $terms) {
                foreach ($terms as $term => $types) {

                    $normalizedTerm = preg_replace('/(st|nd|rd|th)/', '', $term);

                    foreach ($types as $scholarshipType => $students) {
                        if (!isset($scholarshipByTerm[$normalizedTerm][$scholarshipType])) {
                            $scholarshipByTerm[$normalizedTerm][$scholarshipType] = 0;
                        }
                        $scholarshipByTerm[$normalizedTerm][$scholarshipType] += count($students);
                    }
                }
            }
        }

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
            'termsByYear'       => $termsByYear,
            'otsvList'          => $otsvList,
        ]);
    }

    /* ============================================================
     *  SCHOLAR PERSONAL DASHBOARD (scholars.dashboard)
     * ============================================================ */
    public function joinedOpportunities()
    {
        $studentId = session('scholar_accounts.student_no');

        /* -------------------------------------------
         * FETCH SCHOLARS LIST (for left panel widgets)
         * ------------------------------------------- */
        $scholars = $this->database->getReference('scholars')->getValue() ?? [];

        /* -------------------------------------------
         * FETCH OTSV LIST (only available)
         * ------------------------------------------- */
        $otsvList = $this->database
            ->getReference('on_the_spot_volunteers')
            ->orderByChild('status')
            ->equalTo('available')
            ->getValue();

        $otsvList = $otsvList ? array_map(function ($id, $item) {
            $item['id'] = $id;
            return $item;
        }, array_keys($otsvList), $otsvList) : [];

        return view('scholars.dashboard', [
            'scholars' => $scholars,
            'otsvList' => $otsvList,
        ]);
    }
}
