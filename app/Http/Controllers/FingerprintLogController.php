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

    // ESP32 verification logic...
    $esp32Url = rtrim(config('services.esp32.base_url'), '/') . '/verify';
    $resp = \Illuminate\Support\Facades\Http::post($esp32Url);
    $json = $resp->json();

    if (!($json['found'] ?? false)) {
        return response()->json(['ok' => false, 'error' => 'Fingerprint not recognized']);
    }

    $slotId = (int) $json['id'];
    $now = now();
    $today = $now->toDateString();

    $logsRef = $this->database->getReference('fingerprint_log')->getValue() ?? [];
    $activeLogKey = null;
    $activeLog = null;

    foreach ($logsRef as $key => $log) {
        if (
            ($log['slot_id'] ?? null) == $slotId &&
            ($log['time_in_date'] ?? null) === $today &&
            !empty($log['time_in']) &&
            empty($log['time_out'])
        ) {
            $activeLogKey = $key;
            $activeLog = $log;
            break;
        }
    }

    // Rules
    if ($action === 'time_in' && $activeLog) {
        return response()->json(['ok' => false, 'error' => 'Already timed in. Please time out first.']);
    }
    if ($action === 'time_out' && !$activeLog) {
        return response()->json(['ok' => false, 'error' => 'No active time-in found. Please time in first.']);
    }

    // Save log
    if ($action === 'time_in') {
        $logData = [
            'slot_id' => $slotId,
            'time_in' => $now->toTimeString(),
            'time_out' => null,
            'time_in_date' => $today,
            'time_out_date' => null,
            'status' => 'Ongoing',
        ];
        $this->database->getReference('fingerprint_log')->push($logData);
    }
    if ($action === 'time_out') {
        $this->database->getReference("fingerprint_log/{$activeLogKey}")
            ->update([
                'time_out' => $now->toTimeString(),
                'time_out_date' => $today,
                'status' => 'Pending',
            ]);
    }

    // 🔹 Build updated volunteer logs for frontend
    $volunteerLogs = $this->buildVolunteerLogs();

    return response()->json([
        'ok' => true,
        'slot_id' => $slotId,
        'action' => $action,
        'time' => $now->toTimeString(),
        'datetime' => $now->format('l, F j, Y h:i:s A'),
        'volunteerLogs' => $volunteerLogs, // ✅ send updated logs
    ]);
}


private function buildVolunteerLogs()
{
    $fingerprintLogs = $this->database->getReference('fingerprint_log')->getValue() ?? [];
    $scholarsData    = $this->database->getReference('scholars')->getValue() ?? [];
    $opportunities   = $this->database->getReference('opportunities')->getValue() ?? [];

    $slotMap = [];
    foreach ($scholarsData as $years) {
        foreach ($years as $terms) {
            foreach ($terms as $types) {
                foreach ($types as $scholar) {
                    if (!empty($scholar['fingerprint_slot_id'])) {
                        $slotMap[$scholar['fingerprint_slot_id']] = $scholar;
                    }
                }
            }
        }
    }

    $rows = [];

    foreach ($fingerprintLogs as $logKey => $log) {

        $slotId = $log['slot_id'] ?? null;
        if (!$slotId || !isset($slotMap[$slotId])) continue;

        $scholar = $slotMap[$slotId];
        $studentId = $scholar['student_no'] ?? '—';
        $name = trim(
            ($scholar['first_name'] ?? '') . ' ' .
            ($scholar['middle_name'] ?? '') . ' ' .
            ($scholar['last_name'] ?? '')
        );

        $logDate = $log['time_in_date'] ?? null;
        $opportunityName = '—';
        $taskName = '—';

        // 🔹 Loop opportunities
        foreach ($opportunities as $opp) {
            $participants = $opp['participants'] ?? [];

            foreach ($participants as $p) {
                if (($p['scholar_id'] ?? null) !== $studentId) continue;

                // Participant matches, now check if task exists for the log date
                $participantTasks = $p['tasks'][$logDate] ?? [];
                if (!empty($participantTasks)) {
                    $taskName = implode(', ', $participantTasks); // handle multiple tasks
                }

                $opportunityName = $opp['title'] ?? '—';
                break 2; // stop after first matching opportunity
            }
        }

        // 🔹 Calculate total hours
        $totalHours = '—';
        if (!empty($log['time_in']) && !empty($log['time_out']) && $log['time_in'] !== '—' && $log['time_out'] !== '—') {
            try {
                $timeIn = \Carbon\Carbon::createFromFormat('H:i:s', $log['time_in']);
                $timeOut = \Carbon\Carbon::createFromFormat('H:i:s', $log['time_out']);
                $diffInMinutes = $timeIn->diffInMinutes($timeOut);
                $hours = floor($diffInMinutes / 60);
                $minutes = $diffInMinutes % 60;
                $totalHours = "{$hours}h {$minutes}m";
            } catch (\Exception $e) {
                $totalHours = '—';
            }
        }

        $rows[] = [
            'log_key'             => $logKey,
            'student_id'          => $studentId,
            'name'                => $name,
            'fingerprint_slot_id' => $slotId,
            'opportunity_name'    => $opportunityName,
            'task_name'           => $taskName, // ✅ now from participants/tasks/date
            'time_in'             => $log['time_in'] ?? '—',
            'time_out'            => $log['time_out'] ?? '—',
            'total_hours'         => $totalHours,
            'date'                => $logDate ?? '—',
            'status'              => $log['status'] ?? (!empty($log['time_in']) && empty($log['time_out']) ? 'Ongoing' : 'Pending'),
        ];
    }

    return $rows;
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
