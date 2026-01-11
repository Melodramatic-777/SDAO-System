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
     *  ADMIN DASHBOARD
     * ============================================================ */
    public function dashboard()
    {
        /* -------------------------------------------
         * FETCH SCHOLARS
         * ------------------------------------------- */
        $scholars = $this->database
            ->getReference('scholars')
            ->getValue() ?? [];

        /* -------------------------------------------
         * FETCH OTSV LIST
         * ------------------------------------------- */
        $otsvList = $this->database
            ->getReference('on_the_spot_volunteers')
            ->getValue() ?? [];

        $otsvList = is_array($otsvList)
            ? array_map(function ($id, $item) {
                $item['id'] = $id;
                return $item;
            }, array_keys($otsvList), $otsvList)
            : [];

        /* -------------------------------------------
         * INITIALIZE STATS
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

        /* -------------------------------------------
         * PROCESS SCHOLARS
         * ------------------------------------------- */
        if (is_array($scholars)) {

            $allAcademicYears = array_keys($scholars);
            sort($allAcademicYears);
            $recentYear = end($allAcademicYears);

            foreach ($scholars as $year => $terms) {
                $termsByYear[$year] = is_array($terms) ? array_keys($terms) : [];
            }

            foreach ($scholars as $academicYear => $terms) {
                if (!is_array($terms)) continue;

                foreach ($terms as $term => $types) {
                    if (!is_array($types)) continue;

                    foreach ($types as $scholarshipType => $students) {
                        if (!is_array($students)) continue;

                        foreach ($students as $studentId => $studentData) {
                            if (!is_array($studentData)) continue;

                            $total++;

                            $byScholarship[$scholarshipType] =
                                ($byScholarship[$scholarshipType] ?? 0) + 1;

                            $status = strtolower(
                                $studentData['gwa_records'][$academicYear]['status']
                                ?? $studentData['status']
                                ?? ''
                            );

                            if ($status === 'eligible') $eligible++;
                            elseif ($status === 'probationary') $probationary++;
                            elseif ($status === 'revoked') $revoked++;

                            $normalizedTerm = preg_replace('/(st|nd|rd|th)/', '', $term);

                            if (
                                $academicYear === $recentYear &&
                                isset($termStats[$normalizedTerm])
                            ) {
                                $termStats[$normalizedTerm][$status] =
                                    ($termStats[$normalizedTerm][$status] ?? 0) + 1;
                            }

                            if ($academicYear === $recentYear) {
                                $recentYearTotal++;
                                if ($status === 'revoked') $recentYearRevoked++;
                            }
                        }
                    }
                }
            }

            if ($recentYear && isset($scholars[$recentYear])) {
                $yrTerms = array_keys($scholars[$recentYear]);
                sort($yrTerms);
                $currentTerm = end($yrTerms);
            }
        }

        /* -------------------------------------------
         * RETENTION RATE
         * ------------------------------------------- */
        $retentionRate = $recentYearTotal > 0
            ? round((($recentYearTotal - $recentYearRevoked) / $recentYearTotal) * 100, 2)
            : 0;

        /* -------------------------------------------
         * SCHOLARSHIP BY TERM
         * ------------------------------------------- */
        $scholarshipByTerm = ["1" => [], "2" => [], "3" => []];

        foreach ($scholars as $academicYear => $terms) {
            if (!is_array($terms)) continue;

            foreach ($terms as $term => $types) {
                if (!is_array($types)) continue;

                $normalizedTerm = preg_replace('/(st|nd|rd|th)/', '', $term);

                foreach ($types as $scholarshipType => $students) {
                    if (!is_array($students)) continue;

                    $scholarshipByTerm[$normalizedTerm][$scholarshipType] =
                        ($scholarshipByTerm[$normalizedTerm][$scholarshipType] ?? 0)
                        + count($students);
                }
            }
        }

        /* -------------------------------------------
         * RETURN VIEW
         * ------------------------------------------- */
        return view('scholars-admin.dashboard', compact(
            'total',
            'eligible',
            'probationary',
            'revoked',
            'byScholarship',
            'retentionRate',
            'recentYear',
            'termStats',
            'currentTerm',
            'scholarshipByTerm',
            'allAcademicYears',
            'termsByYear',
            'otsvList'
        ));
    }

    /* ============================================================
     *  SCHOLAR PERSONAL DASHBOARD
     * ============================================================ */
    public function joinedOpportunities()
    {
        $scholars = $this->database
            ->getReference('scholars')
            ->getValue() ?? [];

        $otsvList = $this->database
            ->getReference('on_the_spot_volunteers')
            ->orderByChild('status')
            ->equalTo('available')
            ->getValue() ?? [];

        $otsvList = is_array($otsvList)
            ? array_map(function ($id, $item) {
                $item['id'] = $id;
                return $item;
            }, array_keys($otsvList), $otsvList)
            : [];

        return view('scholars.dashboard', compact('scholars', 'otsvList'));
    }
}
