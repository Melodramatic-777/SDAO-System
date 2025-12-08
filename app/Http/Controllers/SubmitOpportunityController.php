<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Carbon\Carbon;

class SubmitOpportunityController extends Controller
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
     * Sanitize a string to be used as a Firebase key.
     */
    private function sanitizeFirebaseKey(string $key): string
    {
        // Replace invalid characters in Firebase keys
        return str_replace(['.', '#', '$', '[', ']', '/'], '_', $key);
    }

    public function store(Request $request)
    {
        $isIntermittent = $request->boolean('intermittent', false);

        // -------------------------------
        // Validation
        // -------------------------------
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string',
            'start_date' => 'nullable|date',
            'tasks_by_date' => 'nullable|array',
            'intermittent_summary' => 'nullable|array',
            'description' => 'required|string',
            'schedule' => 'nullable|array',
            'schedule.*.date' => 'nullable|date',
            'schedule.*.start_time' => 'nullable|string',
            'schedule.*.end_time' => 'nullable|string',
            'schedule.*.target_number' => 'nullable|integer',
            'intermittent_details' => 'nullable|string',
            'food_provided' => 'required|in:yes,no',
        ]);

        // -------------------------------
        // Session user info
        // -------------------------------
        $user = session('volunteers_office') ?? session('volunteers_admin') ?? [];
        $userEmail = $user['email'] ?? abort(403, 'No logged in user email found.');
        $userRole = session()->has('volunteers_office')
            ? ($user['office_name'] ?? 'Office')
            : ($user['role'] ?? null);

        $fullName = trim(
            ($user['first_name'] ?? '') . ' ' .
            ($user['middle_name'] ?? '') . ' ' .
            ($user['last_name'] ?? '') . ' ' .
            ($user['suffix'] ?? '')
        );

        $opportunityId = Str::uuid();
        $status = (strcasecmp(trim($userRole), 'SDA Assistant') === 0) ? 'Approved' : 'Pending';

        // -------------------------------
        // Task definitions
        // -------------------------------
        $taskDefinitions = [
            "Marshalling" => ["skills" => "Crowd management, clear communication, alert, patient", "description" => "Helps manage crowd flow, guide participants, and keep pathways clear; recommended for volunteers who are confident, alert, and comfortable giving clear verbal directions."],
            "Registration" => ["skills" => "Organized, detail-oriented, friendly, basic data encoding", "description" => "Handles participant check-in and attendance lists; ideal for detail-oriented volunteers who are organized, patient, and comfortable talking to many people."],
            "Ushering" => ["skills" => "Approachable, good communication, presentable", "description" => "Guides attendees to their seats or assigned areas and assists with basic inquiries; suited for approachable volunteers with good people skills and patience."],
            "Photography" => ["skills" => "Basic photography skills, attention to detail", "description" => "Takes photos of key moments, guests, and activities during the event; ideal for volunteers comfortable using a camera/phone and who have a basic eye for composition."],
            "Videography" => ["skills" => "Basic video recording, timing awareness", "description" => "Records event highlights and important segments for recap or publicity; suited for volunteers who can handle basic video framing and steady recording."],
            "Technical Support" => ["skills" => "Basic IT skills, calm under pressure", "description" => "Helps set up and monitor microphones, speakers, projectors, and presentations; recommended for volunteers who are tech-savvy, attentive, and comfortable troubleshooting simple issues."],
            "Creatives/Design" => ["skills" => "Basic design sense, creativity", "description" => "Assists with event visuals such as tarps, layouts, social media posts, and other collaterals; recommended for volunteers with basic design sense or familiarity with Canva/graphic tools."],
            "Hosting" => ["skills" => "Confident speaker, energetic, good diction", "description" => "Leads the program, introduces speakers, and keeps the event flowing; recommended for volunteers who are confident, articulate, and comfortable speaking in front of a crowd."],
            "Logistics" => ["skills" => "Physically capable, organized", "description" => "Handles movement of materials, documents, or equipment between venues or stations; recommended for physically able volunteers who can move quickly and follow instructions."],
            "Admin Support" => ["skills" => "Detail-oriented, encoding, filing", "description" => "Helps prepare documents, attendance sheets, certificates, and encodes data after the event; suited for volunteers who are organized, can focus on desk work, and are comfortable with basic computer tasks."],
        ];

        // -------------------------------
        // Build tasks by date safely
        // -------------------------------
        $tasksByDateFormatted = [];
        $intermittentTasksByDate = [];
        $taskTotals = [];

        $inputTasks = $isIntermittent
    ? $request->input('intermittent_summary', [])
    : $request->input('tasks_by_date', []);

     foreach ($inputTasks as $rawDate => $tasks) {
    try {
        $date = Carbon::parse($rawDate, 'Asia/Manila')->toDateString();
        $firebaseDateKey = $this->sanitizeFirebaseKey($date);
    } catch (\Exception $e) {
        continue;
    }

    if (!is_array($tasks) || empty($tasks)) continue;

    $intermittentTasksByDate[$firebaseDateKey] = [];

    // Find schedule entry for this date
    $scheduleEntry = collect($request->input('schedule', []))
        ->firstWhere('date', $date);

    $startTime = $scheduleEntry['start_time'] ?? null;
    $endTime   = $scheduleEntry['end_time'] ?? null;

    foreach ($tasks as $taskName => $neededRaw) {
        $taskName = trim((string)$taskName);
        $needed = intval($neededRaw);

        if ($taskName === '' || $needed <= 0) continue;

        $firebaseTaskName = $this->sanitizeFirebaseKey($taskName);

        $taskData = [
            'task' => $taskName,
            'skills' => $taskDefinitions[$taskName]['skills'] ?? null,
            'description' => $taskDefinitions[$taskName]['description'] ?? null,
            'needed' => $needed,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        $intermittentTasksByDate[$firebaseDateKey][$firebaseTaskName] = $taskData;

        $tasksByDateFormatted[] = array_merge(['date' => $date], $taskData);

        $taskTotals[$taskName] = ($taskTotals[$taskName] ?? 0) + $needed;
    }
}

        // -------------------------------
        // Flattened task list
        // -------------------------------
        $taskList = [];
        foreach ($taskTotals as $taskName => $needed) {
            $taskList[] = [
                'task' => $taskName,
                'skills' => $taskDefinitions[$taskName]['skills'] ?? null,
                'description' => $taskDefinitions[$taskName]['description'] ?? null,
                'needed' => $needed,
            ];
        }

        // -------------------------------
        // Schedule handling
        // -------------------------------
        if ($isIntermittent) {
            $scheduleData = collect($request->input('schedule', []))->map(function ($item) use ($validated) {
                return [
                    'date' => $item['date'] ?? null,
                    'start_time' => $item['start_time'] ?? null,
                    'end_time' => $item['end_time'] ?? null,
                    'target_number' => $item['target_number'] ?? null,
                    'food_provided' => $item['food_provided'] ?? $validated['food_provided'] ?? 'no',
                ];
            })->values()->toArray();
        } else {
            $scheduleData = [[
                'date' => $validated['start_date'],
                'start_time' => $request->input('schedule.0.start_time'),
                'end_time' => $request->input('schedule.0.end_time'),
                'target_number' => $request->input('schedule.0.target_number'),
                'food_provided' => $validated['food_provided'],
            ]];
        }

        // -------------------------------
        // Urgency detection
        // -------------------------------
        $today = Carbon::now('Asia/Manila')->startOfDay();
        if ($isIntermittent) {
            $firstDate = collect($scheduleData)->pluck('date')->filter()->map(fn($d) => Carbon::parse($d, 'Asia/Manila'))->sort()->first();
        } else {
            $firstDate = $validated['start_date'] ? Carbon::parse($validated['start_date'], 'Asia/Manila') : null;
        }
        $isUrgent = ($firstDate && $today->diffInDays($firstDate, false) <= 3) ? 'yes' : 'no';

        // -------------------------------
        // Prepare data for Firebase
        // -------------------------------
        $opportunityData = [
            'opportunity_id' => $opportunityId,
            'submitted_by' => $userEmail,
            'submitted_by_role' => $userRole,
            'submitted_by_name' => $fullName,
            'title' => $validated['title'],
            'location' => $validated['location'],
            'start_date' => $validated['start_date'],
            'description' => $validated['description'],
            'tasks' => $taskList,
            'tasks_by_date' => $tasksByDateFormatted,
            'intermittent_tasks_by_date' => $intermittentTasksByDate,
            'status' => $status,
            'submitted_at' => now()->toDateTimeString(),
            'schedule' => $scheduleData,
            'is_intermittent' => $isIntermittent,
            'intermittent_details' => $validated['intermittent_details'] ?? null,
            'is_urgent' => $isUrgent,
            'food_provided' => $validated['food_provided'],
        ];

        // -------------------------------
        // Save to Firebase
        // -------------------------------
        $this->database
            ->getReference('opportunities/' . $opportunityId)
            ->set($opportunityData);

        // -------------------------------
        // Redirect
        // -------------------------------
        return ($status === 'Approved')
            ? redirect()->route('volunteers-admin.opportunities')->with('success', 'Opportunity submitted and auto-approved!')
            : redirect()->route('volunteers-offices.request-volunteer')->with('success', 'Opportunity submitted! Pending approval.');
    }
}
