<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class VolunteerInvitationController extends Controller
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
     * SHOW SCHOLARS (ADMIN + OFFICE)
     */
    public function showScholars(Request $request)
    {
        $scholarsRaw = $this->database->getReference('scholars')->getValue();

        $latestYear = null;
        $latestTerm = null;

        if (is_array($scholarsRaw)) {
            $years = array_keys($scholarsRaw);

            usort($years, fn ($a, $b) =>
                (int)substr($b, 0, 4) <=> (int)substr($a, 0, 4)
            );

            $latestYear = $years[0] ?? null;

            if ($latestYear && isset($scholarsRaw[$latestYear])) {
                $terms = array_keys($scholarsRaw[$latestYear]);
                usort($terms, fn ($a, $b) =>
                    preg_replace('/\D/', '', $b) <=> preg_replace('/\D/', '', $a)
                );
                $latestTerm = $terms[0] ?? null;
            }
        }

        $flattened = [];

        if ($latestYear && $latestTerm) {
            foreach ($scholarsRaw[$latestYear][$latestTerm] ?? [] as $group) {
                foreach ($group ?? [] as $scholar) {
                    $flattened[] = $scholar;
                }
            }
        }

        $collection = collect($flattened);

        if ($request->filled('scholarship_type')) {
            $collection = $collection->where('scholarship_type', $request->scholarship_type);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $collection = $collection->filter(function ($s) use ($search) {
                return str_contains(
                    strtolower(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')),
                    $search
                );
            });
        }

        $perPage = 10;
        $page = $request->get('page', 1);

        $scholars = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $scholarshipTypes = collect($flattened)
            ->pluck('scholarship_type')
            ->unique()
            ->filter()
            ->values();

        $opportunities = array_filter(
            $this->database->getReference('opportunities')->getValue() ?? [],
            fn ($op) => ($op['status'] ?? '') === 'Approved'
        );

        return view('volunteers-admin.invite-volunteer', compact(
            'scholars',
            'scholarshipTypes',
            'opportunities',
            'latestYear',
            'latestTerm'
        ));
    }

    /**
     * INVITE SCHOLAR
     */
    public function inviteScholar(Request $request)
    {
        $validated = $request->validate([
            'scholar_id'     => 'required|string',
            'opportunity_id' => 'required|string',
            'tasks'          => 'required|array',
            'schedule_indices' => 'array'
        ]);

        $scholarId     = $validated['scholar_id'];
        $opportunityId = $validated['opportunity_id'];

        $scholars = $this->database->getReference('scholars')->getValue();
        $scholar = null;
        $scholarPath = null;

        foreach ($scholars ?? [] as $year => $terms) {
            foreach ($terms as $term => $types) {
                foreach ($types as $type => $students) {
                    foreach ($students as $key => $s) {
                        if (($s['student_no'] ?? '') === $scholarId) {
                            $scholar = $s;
                            $scholarPath = "scholars/$year/$term/$type/$key";
                            break 4;
                        }
                    }
                }
            }
        }

        if (!$scholar) {
            return back()->with('error', 'Scholar not found.');
        }

        $opportunity = $this->database
            ->getReference("opportunities/$opportunityId")
            ->getValue();

        if (!$opportunity) {
            return back()->with('error', 'Opportunity not found.');
        }

        $office = $opportunity['submitted_by_role'] ?? 'Issuing Office';

        $participant = [
            'scholar_id' => $scholarId,
            'name'       => trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['last_name'] ?? '')),
            'role'       => 'Volunteer',
            'status'     => 'Pending Invite',
            'tasks'      => $validated['tasks'],
            'invited_at' => Carbon::now()->toDateTimeString(),
            'office'     => $office,
        ];

        $this->database
            ->getReference("opportunities/$opportunityId/participants")
            ->push($participant);

        $this->database
            ->getReference("$scholarPath/invited/$opportunityId")
            ->set([
                'opportunity_id' => $opportunityId,
                'status'         => 'Pending Invite',
                'tasks'          => $validated['tasks'],
                'office'         => $office,
                'invited_at'     => Carbon::now()->toDateTimeString(),
            ]);

        if (!empty($scholar['email'])) {
            Mail::to($scholar['email'])->send(
                new \App\Mail\VolunteerInvitationMail($participant['name'], $office)
            );
        }

        return back()->with('success', 'Scholar invited successfully!');
    }
}
