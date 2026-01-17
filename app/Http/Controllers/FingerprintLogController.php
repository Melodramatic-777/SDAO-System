<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class FingerprintLogController extends Controller
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
     * 
     * 
     * Scan fingerprint for Time In / Time Out
     */


       public function index()
    {
        return view('scholars-admin.fingerprint-log');
    }
    
 public function scan(Request $request)
{
    $request->validate([
        'action' => 'required|in:time_in,time_out'
    ]);

    $action = $request->action;

    /** 🔹 ESP32 VERIFY */
    $esp32Url = rtrim(config('services.esp32.base_url'), '/') . '/verify';
    $resp = \Illuminate\Support\Facades\Http::post($esp32Url);
    $json = $resp->json();

    if (!($json['found'] ?? false)) {
        return response()->json(['ok' => false, 'error' => 'Fingerprint not recognized']);
    }

    $slotId = (int) $json['id'];
    $now    = now();
    $today  = $now->toDateString();

    /** 🔹 LOAD DATA */
    $scholars      = $this->database->getReference('scholars')->getValue() ?? [];
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];
    $logsRef       = $this->database->getReference('fingerprint_log')->getValue() ?? [];

    /** 🔹 FIND SCHOLAR BY SLOT */
    $matchedScholar = null;

    foreach ($scholars as $years) {
        foreach ($years as $terms) {
            foreach ($terms as $types) {
                foreach ($types as $scholar) {
                    if (($scholar['fingerprint_slot_id'] ?? null) == $slotId) {
                        $matchedScholar = $scholar;
                        break 4;
                    }
                }
            }
        }
    }

    if (!$matchedScholar) {
        return response()->json([
            'ok' => false,
            'error' => 'Scholar not registered for fingerprint scanning'
        ]);
    }

    $studentNo = $matchedScholar['student_no'];
    $scholarName = trim(
        ($matchedScholar['first_name'] ?? '') . ' ' .
        ($matchedScholar['middle_name'] ?? '') . ' ' .
        ($matchedScholar['last_name'] ?? '')
    );

    /** 🔹 FIND JOINED OPPORTUNITY WITH TASK TODAY */
$matchedOpportunity = null;
$matchedTasks = [];

foreach ($opportunities as $oppId => $opp) {
    $participants = $opp['participants'] ?? [];

    foreach ($participants as $participant) {

        if (($participant['scholar_id'] ?? null) !== $studentNo) {
            continue;
        }

        // ✅ Must have task for today
        if (!empty($participant['tasks'][$today])) {

            $matchedTasks = is_array($participant['tasks'][$today])
                ? $participant['tasks'][$today]
                : [$participant['tasks'][$today]];

            $matchedOpportunity = [
                'id'    => $oppId,
                'title' => $opp['title'] ?? '—',
            ];

            break 2;
        }
    }
}

    /** ❌ NOT JOINED */
    if (!$matchedOpportunity) {
        return response()->json([
            'ok' => false,
            'error' => 'You have not joined any opportunities'
        ]);
    }

    /** 🔹 FIND ACTIVE LOG */
    $activeLogKey = null;

    foreach ($logsRef as $key => $log) {
        if (
            ($log['slot_id'] ?? null) == $slotId &&
            ($log['time_in_date'] ?? null) === $today &&
            empty($log['time_out'])
        ) {
            $activeLogKey = $key;
            break;
        }
    }

    /** 🔹 RULES */
    if ($action === 'time_in' && $activeLogKey) {
        return response()->json([
            'ok' => false,
            'error' => 'Already timed in. Please time out first.'
        ]);
    }

    if ($action === 'time_out' && !$activeLogKey) {
        return response()->json([
            'ok' => false,
            'error' => 'No active time-in found. Please time in first.'
        ]);
    }

    /** 🔹 SAVE LOG */
    if ($action === 'time_in') {
$this->database->getReference('fingerprint_log')->push([
    'slot_id'            => $slotId,
    'student_no'         => $studentNo,
    'scholar_name'       => $scholarName,
    'opportunity_id'     => $matchedOpportunity['id'],
    'opportunity_title'  => $matchedOpportunity['title'],
    'tasks'              => $matchedTasks, // ✅ STORED HERE
    'time_in'            => $now->toTimeString(),
    'time_out'           => null,
    'time_in_date'       => $today,
    'time_out_date'      => null,
    'status'             => 'Ongoing',
]);

    }

    if ($action === 'time_out') {
        $this->database
            ->getReference("fingerprint_log/{$activeLogKey}")
            ->update([
                'time_out'       => $now->toTimeString(),
                'time_out_date'  => $today,
                'status'         => 'Pending',
            ]);
    }

    return response()->json([
        'ok'        => true,
        'action'    => $action,
        'slot_id'   => $slotId,
        'datetime'  => $now->format('l, F j, Y h:i:s A'),
    ]);
}


private function buildVolunteerLogs()
{
    $fingerprintLogs = $this->database
        ->getReference('fingerprint_log')
        ->getValue() ?? [];

    $rows = [];

    foreach ($fingerprintLogs as $logKey => $log) {

        $timeIn  = $log['time_in']  ?? null;
        $timeOut = $log['time_out'] ?? null;

        /** 🔹 Calculate total hours */
        $totalHours = '—';
        if ($timeIn && $timeOut) {
            try {
                $in  = \Carbon\Carbon::createFromFormat('H:i:s', $timeIn);
                $out = \Carbon\Carbon::createFromFormat('H:i:s', $timeOut);
                $mins = $in->diffInMinutes($out);
                $totalHours = floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
            } catch (\Exception $e) {
                $totalHours = '—';
            }
        }

        /** 🔹 Tasks display */
        $tasks = '—';
        if (!empty($log['tasks']) && is_array($log['tasks'])) {
            $tasks = implode(', ', $log['tasks']);
        }

        $rows[] = [
            'log_key'          => $logKey,
            'student_id'       => $log['student_no'] ?? '—',
            'name'             => $log['scholar_name'] ?? '—',
            'opportunity_name' => $log['opportunity_title'] ?? '—',
            'task_name'        => $tasks,
            'date'             => $log['time_in_date'] ?? '—',
            'time_in'          => $timeIn ?? '—',
            'time_out'         => $timeOut ?? '—',
            'total_hours'      => $totalHours,
            'status'           => $log['status'] ?? 'Pending',
        ];
    }

    return collect($rows)
        ->sortByDesc('date')
        ->values();
}




public function volunteerLogsTable()
{
    // Build all volunteer logs
    $rows = $this->buildVolunteerLogs();

    // 🔹 Filter out "Complete" logs
    $rows = collect($rows)->filter(function($row) {
        return ($row['status'] ?? '') !== 'Complete';
    })->values(); // reset keys

    // Optional: collect unique schools/programs for filters
    $schools = collect($rows)->pluck('school')->filter()->unique()->sort()->values();
    $programs = collect($rows)->pluck('program')->filter()->unique()->sort()->values();

    // Return view with filtered rows
    return view('volunteers-admin.validate-attendance', [
        'rows' => $rows,
        'schools' => $schools,
        'programs' => $programs,
    ]);
}



public function validateLog(Request $request)
{
    $request->validate([
        'log_key' => 'required|string',
        'action'  => 'required|in:approve,reject',
    ]);

    $status = $request->action === 'approve'
        ? 'Approved'
        : 'Rejected';

    $now = now();

    $this->database
        ->getReference('fingerprint_log/' . $request->log_key)
        ->update([
            'status' => $status,
            'validated_at' => $now->toDateTimeString(), // ✅ date + time
        ]);

    return back()->with('success', "Log {$status} successfully.");
}


public function submitFeedback(Request $request)
{
    $request->validate([
        'log_key' => 'required|string',
        'feedback' => 'required|string|max:2000',
    ]);

    // 🔍 Identify submitter
    if (Session::has('volunteers_admin')) {
        $user = Session::get('volunteers_admin');
        $submittedByRole = 'SDA Assistant';
        $submittedByName = $user['name'] ?? $user['email'];
    }
    elseif (Session::has('volunteers_office')) {
        $user = Session::get('volunteers_office');
        $submittedByRole = 'Office';
        $submittedByName = $user['office_name'] ?? $user['email'];
    }
    else {
        abort(403);
    }

    $logKey = $request->log_key;

    // 🔹 Save feedback
    $this->database
        ->getReference("fingerprint_log/{$logKey}/feedback")
        ->push([
            'message' => $request->feedback,
            'submitted_by' => $submittedByName,
            'submitted_by_role' => $submittedByRole,
            'submitted_at' => now()->toDateTimeString(),
        ]);

    // 🔹 Update status to "Complete"
    $this->database
        ->getReference("fingerprint_log/{$logKey}")
        ->update([
            'status' => 'Complete',
            'completed_at' => now()->toDateTimeString(), // optional timestamp
        ]);

    // 🔹 Return back with success flag to show modal
    return back()->with('feedback_moved', true);
}



}
