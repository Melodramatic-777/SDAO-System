<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use App\Http\Controllers\OnTheSpotVolunteerController;

class ScholarJoinController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function join($id, Request $request)
    {
        $scholar = session('scholar_accounts');
        if (!$scholar) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $scholarId   = $scholar['student_no'] ?? null;
        $scholarName = trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''));

        if (!$scholarId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ref = $this->database->getReference('opportunities/' . $id);
        $opportunity = $ref->getValue();

        if (!$opportunity) {
            return response()->json(['error' => 'Opportunity not found'], 404);
        }

        $scheduleIndex = $request->input('schedule_index', null);
        $participants = $opportunity['participants'] ?? [];

        if (!is_array($participants)) {
            $participants = [];
        }

        // Find if the scholar already joined before
        $existingIndex = null;
        foreach ($participants as $i => $p) {
            if (($p['scholar_id'] ?? null) == $scholarId) {
                $existingIndex = $i;
                break;
            }
        }

        // If intermittent: add to array of joined schedules
        if ($scheduleIndex !== null && isset($opportunity['is_intermittent']) && $opportunity['is_intermittent'] == 1) {
            if ($existingIndex !== null) {
                // Scholar already exists → add new schedule if not duplicate
                $joinedSchedules = $participants[$existingIndex]['schedule_indices'] ?? [];
                if (!in_array($scheduleIndex, $joinedSchedules)) {
                    $joinedSchedules[] = $scheduleIndex;
                    $participants[$existingIndex]['schedule_indices'] = $joinedSchedules;
                    $participants[$existingIndex]['joined_at'] = Carbon::now()->toDateTimeString();
                } else {
                    return response()->json(['message' => 'Already joined this schedule'], 200);
                }
            } else {
                // New participant entry
                $participants[] = [
                    'scholar_id' => $scholarId,
                    'name'       => $scholarName,
                    'joined_at'  => Carbon::now()->toDateTimeString(),
                    'status'     => 'Accepted',
                    'schedule_indices' => [$scheduleIndex],
                ];
            }
        } 
        else {
            // Regular schedule
            if ($existingIndex !== null) {
                return response()->json(['message' => 'Already joined'], 200);
            }

            $target = $opportunity['schedule'][0]['target_number'] ?? null;
            if ($target !== null && count($participants) >= (int)$target) {
                return response()->json(['error' => 'Opportunity is full'], 400);
            }

            $participants[] = [
                'scholar_id' => $scholarId,
                'name'       => $scholarName,
                'joined_at'  => Carbon::now()->toDateTimeString(),
                'status'     => 'Accepted',
                'schedule_index' => 0,
            ];
        }

        try {
            $ref->update(['participants' => $participants]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save participant: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Joined successfully']);
    }

    public function getIntermittentDates($id)
{
    $dates = $this->database
        ->getReference("opportunities/$id/intermittent_schedule")
        ->getValue();

    return response()->json($dates ? array_values($dates) : []);
}

public function joinTask(Request $request)
{
    $scholar = session('scholar_accounts');
    if (!$scholar) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $scholarId   = $scholar['student_no'] ?? null;
    $scholarName = trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''));
    if (!$scholarId) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $opportunityId = $request->input('opportunity_id');
    $taskDate      = $request->input('date');
    $taskName      = $request->input('task_name') ?? $request->input('task'); // support both keys
    $scheduleIndex = $request->input('schedule_index', null); // for intermittent schedules

    if (!$opportunityId || !$taskDate || !$taskName) {
        return response()->json(['message' => 'Missing required fields'], 400);
    }

    $ref = $this->database->getReference('opportunities/' . $opportunityId);
    $opportunity = $ref->getValue();

    if (!$opportunity) {
        return response()->json(['message' => 'Opportunity not found'], 404);
    }

    $isIntermittent = isset($opportunity['is_intermittent']) && $opportunity['is_intermittent'] == 1;

    $participants = $opportunity['participants'] ?? [];
    if (!is_array($participants)) $participants = [];

    // Check if scholar already exists in participants
    $existingIndex = null;
    foreach ($participants as $i => $p) {
        if (($p['scholar_id'] ?? '') === $scholarId) {
            $existingIndex = $i;
            break;
        }
    }

    $now = Carbon::now()->toDateTimeString();

    if ($existingIndex !== null) {
        // Scholar exists → add task
        $tasks = $participants[$existingIndex]['tasks'] ?? [];

        // Initialize for this date if missing
        if (!isset($tasks[$taskDate])) {
            $tasks[$taskDate] = [];
        }

        // LIMIT: Only 1 task per date
        if (count($tasks[$taskDate]) >= 1) {
            return response()->json([
                'message' => 'You can only join 1 task per date.'
            ], 400);
        }

        // Add task
        $tasks[$taskDate][] = $taskName;

        $participants[$existingIndex]['tasks']     = $tasks;
        $participants[$existingIndex]['joined_at'] = $now;
        $participants[$existingIndex]['status']    = 'Joined';

        // Handle intermittent schedules
        if ($isIntermittent && $scheduleIndex !== null) {
            $joinedSchedules = $participants[$existingIndex]['schedule_indices'] ?? [];
            if (!in_array($scheduleIndex, $joinedSchedules)) {
                $joinedSchedules[] = $scheduleIndex;
                $participants[$existingIndex]['schedule_indices'] = $joinedSchedules;
            }
        }

    } else {
        // New participant
        $participants[] = [
            'joined_at'  => $now,
            'name'       => $scholarName,
            'role'       => 'Volunteer',
            'scholar_id' => $scholarId,
            'status'     => 'Joined',
            'tasks'      => [
                $taskDate => [$taskName] // Only 1 task per date
            ],
            'schedule_indices' => $isIntermittent && $scheduleIndex !== null ? [$scheduleIndex] : []
        ];
    }

    try {
        $ref->update(['participants' => $participants]);
        return response()->json(['message' => 'Successfully joined task']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to join task: ' . $e->getMessage()], 500);
    }
}

public function scholarOpportunities()
{
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];

    // Filter approved only
    $approved = array_filter($opportunities, fn($op) =>
        isset($op['status']) && $op['status'] === 'Approved'
    );

    foreach ($approved as $key => &$op) {
        $op['participants'] = array_values($op['participants'] ?? []);
        $op['schedule'] = !empty($op['schedule']) && is_array($op['schedule']) ? $op['schedule'] : [[]];
    }
    unset($op);

    // Sort urgent first then by submission date
    $approved = array_values($approved);
    usort($approved, function ($a, $b) {
        $aUrgent = isset($a['is_urgent']) && $a['is_urgent'] === 'yes';
        $bUrgent = isset($b['is_urgent']) && $b['is_urgent'] === 'yes';
        if ($aUrgent !== $bUrgent) return $bUrgent <=> $aUrgent;
        return strtotime($b['submitted_at'] ?? 'now') <=> strtotime($a['submitted_at'] ?? 'now');
    });

    // Paginate for initial display (5 per page)
    $perPage = 5;
    $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
    $items = collect($approved);
    $pagedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

    $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
        $pagedItems,
        $items->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    // Offices list
    $officesData = $this->database->getReference('offices')->getValue() ?? [];
    $offices = [];
    foreach ($officesData as $office) {
        if (isset($office['name'])) {
            $offices[] = ['name' => $office['name']];
        }
    }

    // ✅ Send both: full list and paginated list
    return view('scholars.volunteer-opportunities', [
        'opportunities' => $pagedItems,      // for default display
        'allOpportunities' => $approved,     // for client-side filtering
        'pagination' => $pagination,
        'offices' => $offices
    ]);
}


public function joinedOpportunities()
{
    $scholar = session('scholar_accounts');
    if (!$scholar) {
        return redirect()->route('login')->with('error', 'Please log in first.');
    }

    $scholarId = $scholar['student_no'] ?? null;
    if (!$scholarId) {
        return redirect()->route('login');
    }

    // Fetch joined opportunities
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];
    $joined = [];
    foreach ($opportunities as $key => $opportunity) {
        $participants = $opportunity['participants'] ?? [];
        if (!is_array($participants)) continue;
        foreach ($participants as $participant) {
            if (($participant['scholar_id'] ?? '') === $scholarId) {
                $opportunity['id'] = $key;
                $joined[$key] = $opportunity;
                break;
            }
        }
    }

    $perPage = 5;
    $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
    $items = collect($joined);
    $pagedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

    $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
        $pagedItems,
        $items->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    // 🔹 Fetch On-the-Spot Volunteers
    $otsvController = new OnTheSpotVolunteerController();
    $otsvList = $otsvController->fetchOTSV(); // get all OTSV entries

    return view('scholars.dashboard', [
        'joinedOpportunities' => $pagedItems,
        'pagination' => $pagination,
        'otsvList' => $otsvList, // ✅ pass it here
    ]);
}

}
