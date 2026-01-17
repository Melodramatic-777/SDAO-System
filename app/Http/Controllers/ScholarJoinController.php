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

    $opportunityId = $request->input('opportunity_id');
    $taskDate      = $request->input('date');
    $taskName      = $request->input('task'); // 🔥 THIS IS ALSO THE FIREBASE KEY

    if (!$scholarId || !$opportunityId || !$taskDate || !$taskName) {
        return response()->json(['message' => 'Missing required fields'], 400);
    }

    $ref = $this->database->getReference("opportunities/$opportunityId");
    $opportunity = $ref->getValue();

    if (!$opportunity) {
        return response()->json(['message' => 'Opportunity not found'], 404);
    }

    /* ========== CHECK SLOT ========== */

    $taskRefPath = "intermittent_tasks_by_date/$taskDate/$taskName";

    $task = $opportunity['intermittent_tasks_by_date'][$taskDate][$taskName] ?? null;

    if (!$task) {
        return response()->json(['message' => 'Task not found'], 404);
    }

    $needed = (int) ($task['needed'] ?? 0);

    if ($needed <= 0) {
        return response()->json(['message' => 'This task is already full'], 400);
    }

    /* ========== PARTICIPANTS ========== */

    $participants = $opportunity['participants'] ?? [];
    if (!is_array($participants)) $participants = [];

    $existingIndex = null;
    foreach ($participants as $i => $p) {
        if (($p['scholar_id'] ?? '') === $scholarId) {
            $existingIndex = $i;
            break;
        }
    }

    $now = Carbon::now()->toDateTimeString();

    if ($existingIndex !== null) {

        $tasks = $participants[$existingIndex]['tasks'] ?? [];

        if (!isset($tasks[$taskDate])) {
            $tasks[$taskDate] = [];
        }

        if (count($tasks[$taskDate]) >= 1) {
            return response()->json(['message' => 'You can only join 1 task per date'], 400);
        }

        $tasks[$taskDate][] = $taskName;

        $participants[$existingIndex]['tasks'] = $tasks;
        $participants[$existingIndex]['joined_at'] = $now;
        $participants[$existingIndex]['status'] = 'Joined';

    } else {

        $participants[] = [
            'joined_at'  => $now,
            'name'       => $scholarName,
            'role'       => 'Volunteer',
            'scholar_id' => $scholarId,
            'status'     => 'Joined',
            'tasks'      => [
                $taskDate => [$taskName]
            ]
        ];
    }

    /* ========== UPDATE FIREBASE ========== */

    try {

        $this->database->getReference("opportunities/$opportunityId/participants")
            ->set(array_values($participants));

        $this->database->getReference("opportunities/$opportunityId/$taskRefPath/needed")
            ->set($needed - 1);

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
        return redirect()->route('login');
    }

    $scholarId = $scholar['student_no'] ?? null;
    $scholarTerm = $scholar['term'] ?? '1st'; // assume term is stored here

    if (!$scholarId) {
        return redirect()->route('login');
    }

    /* ======================================================
     * 🔹 FETCH JOINED OPPORTUNITIES
     * ====================================================== */
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];
    $joined = [];

    foreach ($opportunities as $oppKey => $opportunity) {
        $participants = $opportunity['participants'] ?? [];
        if (!is_array($participants)) continue;

        foreach ($participants as $participant) {
            if (($participant['scholar_id'] ?? '') === $scholarId) {
                $opportunity['opportunity_id'] = $oppKey;
                $joined[$oppKey] = $opportunity;
                break;
            }
        }
    }

    /* ======================================================
     * 🔹 FETCH FINGERPRINT LOGS
     * ====================================================== */
    $fingerprintLogs = $this->database
        ->getReference('fingerprint_log')
        ->getValue() ?? [];

    $fingerprintStatus = [];

    foreach ($fingerprintLogs as $logKey => $log) {
        if (!empty($log['opportunity_id']) &&
            !empty($log['time_in_date']) &&
            ($log['student_no'] ?? '') === $scholarId
        ) {
            $fingerprintStatus[$log['opportunity_id']][$log['time_in_date']] = [
                'status' => $log['status'] ?? '',
                'log_key' => $logKey,
            ];
        }
    }

    /* ======================================================
 * 🔹 CALCULATE OFFICE COUNTS FOR DONE OPPORTUNITIES
 * ====================================================== */
/* ======================================================
 * 🔹 CALCULATE OFFICE HOURS (TIME_IN → TIME_OUT)
 * ====================================================== */
$officeHours = []; // [officeName => totalHours]

foreach ($fingerprintLogs as $log) {

    if (
        ($log['student_no'] ?? '') === $scholarId &&
        ($log['status'] ?? '') === 'Done'
    ) {
        $opId = $log['opportunity_id'] ?? null;
        $timeIn  = $log['time_in'] ?? null;
        $timeOut = $log['time_out'] ?? null;

        if ($opId && isset($joined[$opId]) && $timeIn && $timeOut) {
            try {
                $in  = Carbon::createFromFormat('H:i:s', $timeIn);
                $out = Carbon::createFromFormat('H:i:s', $timeOut);

                $minutes = $in->diffInMinutes($out);
                $hours   = round($minutes / 60, 2);

                $officeName = $joined[$opId]['submitted_by_role'] ?? 'Unknown';

                if (!isset($officeHours[$officeName])) {
                    $officeHours[$officeName] = 0;
                }

                $officeHours[$officeName] += $hours;

            } catch (\Exception $e) {
                // skip invalid time format
            }
        }
    }
}

/* ======================================================
 * 🔹 CALCULATE OFFICE PARTICIPATION COUNTS (OLD LOGIC)
 * ====================================================== */
$officeParticipationCounts = []; // [officeName => count]

foreach ($fingerprintLogs as $log) {
    if (
        ($log['student_no'] ?? '') === $scholarId &&
        ($log['status'] ?? '') === 'Done'
    ) {
        $opId = $log['opportunity_id'] ?? null;

        if ($opId && isset($joined[$opId])) {
            $officeName = $joined[$opId]['submitted_by_role'] ?? 'Unknown';

            if (!isset($officeParticipationCounts[$officeName])) {
                $officeParticipationCounts[$officeName] = 0;
            }

            $officeParticipationCounts[$officeName]++;
        }
    }
}

    /* ======================================================
     * 🔹 CALCULATE TOTAL HOURS, TERM HOURS, AND DONE ACTIVITIES
     * ====================================================== */
    $totalMinutes = 0;
    $termMinutes  = 0;
    $activitiesDone = 0;

    foreach ($fingerprintLogs as $log) {
        if (($log['student_no'] ?? '') === $scholarId) {
            $timeIn  = $log['time_in'] ?? null;
            $timeOut = $log['time_out'] ?? null;
            $timeInDate = $log['time_in_date'] ?? null;

            if ($timeIn && $timeOut) {
                try {
                    $in  = \Carbon\Carbon::createFromFormat('H:i:s', $timeIn);
                    $out = \Carbon\Carbon::createFromFormat('H:i:s', $timeOut);
                    $minutes = $in->diffInMinutes($out);
                    $totalMinutes += $minutes;

                    // Check term — assume we map date to term
                    if ($this->isDateInTerm($timeInDate, $scholarTerm)) {
                        $termMinutes += $minutes;
                    }

                } catch (\Exception $e) {
                    // skip invalid time format
                }
            }

            if (($log['status'] ?? '') === 'Done') {
                $activitiesDone++;
            }
        }
    }

    $totalHours = round($totalMinutes / 60, 2);
    $termHours  = round($termMinutes / 60, 2);
    $goalHours  = 10;
    $remainingHours = max($goalHours - $totalHours, 0);

    /* ======================================================
     * 🔹 PAGINATION
     * ====================================================== */
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

    return view('scholars.dashboard', [
        'joinedOpportunities' => $pagedItems,
        'pagination' => $pagination,
        'fingerprintStatus' => $fingerprintStatus,
        'totalHours' => $totalHours,
        'termHours'  => $termHours,
        'activitiesDone' => $activitiesDone,
        'remainingHours' => $remainingHours,
'officeHours' => $officeHours,
'officeParticipationCounts' => $officeParticipationCounts,


    ]);
}

/* ======================================================
 * 🔹 HELPER: Check if a date belongs to a term
 * ====================================================== */
private function isDateInTerm($date, $term)
{
    if (!$date) return false;

    $carbonDate = Carbon::parse($date);

    switch ($term) {
        case '1st':
            return $carbonDate->month >= 6 && $carbonDate->month <= 10;
        case '2nd':
            return $carbonDate->month >= 11 || $carbonDate->month <= 3;
        case '3rd':
            return $carbonDate->month >= 4 && $carbonDate->month <= 5;
        default:
            return false;
    }
}


public function markFingerprintDone(Request $request)
{
    $scholar = session('scholar_accounts');
    if (!$scholar) {
        abort(403);
    }

    $request->validate([
        'log_key' => 'required|string',
    ]);

    $logKey = $request->log_key;

    $this->database
        ->getReference("fingerprint_log/{$logKey}")
        ->update([
            'status' => 'Done',
            'done_at' => now()->toDateTimeString(),
        ]);

    return back()->with('success', 'Opportunity marked as done.');
}

public function submitScholarFeedback(Request $request) 
{
    $scholar = session('scholar_accounts');
    if (!$scholar) {
        abort(403);
    }

    $request->validate([
        'log_key'  => 'required|string',
        'feedback' => 'required|string|max:2000',
    ]);

    $submittedByName = trim(
        ($scholar['first_name'] ?? '') . ' ' . ($scholar['last_name'] ?? '')
    );

    $logKey = $request->log_key;

    // 🔹 Save feedback
    $this->database
        ->getReference("fingerprint_log/{$logKey}/feedback")
        ->push([
            'message' => $request->feedback,
            'submitted_by' => $submittedByName,
            'submitted_by_role' => 'Scholar',
            'submitted_at' => now()->toDateTimeString(),
        ]);

    // 🔹 Update log status to DONE
    $this->database
        ->getReference("fingerprint_log/{$logKey}")
        ->update([
            'status' => 'Done', // <-- changed from 'Complete'
            'completed_at' => now()->toDateTimeString(),
        ]);

    return back()->with('feedback_submitted', true);
}


}
