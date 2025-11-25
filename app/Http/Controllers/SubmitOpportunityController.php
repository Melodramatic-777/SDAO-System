<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * Handle storing new opportunities with “Food Provided” field.
     */
 public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'location' => 'required|string',
        'start_date' => 'nullable|date',

        // ✨ NEW FIELDS FROM YOUR TASK SYSTEM
        'tasks' => 'nullable|array',
        'task_other_skills' => 'nullable|string',
        'task_other_description' => 'nullable|string',

        // Existing
        'description' => 'required|string',
        'task_details' => 'nullable|string', // no longer required since tasks list replaces it
        'skills_needed' => 'nullable|string',
        'schedule' => 'nullable|array',
        'schedule.*.date' => 'nullable|date',
        'schedule.*.event_name' => 'nullable|string',
        'schedule.*.start_time' => 'nullable|string',
        'schedule.*.end_time' => 'nullable|string',
        'schedule.*.target_participants' => 'nullable|string',
        'schedule.*.target_number' => 'nullable|integer',

        'intermittent' => 'nullable|boolean',
        'intermittent_details' => 'nullable|string',
        'food_provided' => 'required|in:yes,no',
    ]);

    // ---------------------------------------------------------------------
    // USER FROM SESSION
    // ---------------------------------------------------------------------
    $user = session('volunteers_office') ?? session('volunteers_admin') ?? [];
    $userEmail = $user['email'] ?? null;

    if (!$userEmail) {
        abort(403, 'No logged in user email found.');
    }

    if (session()->has('volunteers_office')) {
        $userRole = $user['office_name'] ?? 'Office';
    } else {
        $userRole = $user['role'] ?? null;
    }

    // FULL NAME
    $firstName  = $user['first_name'] ?? '';
    $middleName = $user['middle_name'] ?? '';
    $lastName   = $user['last_name'] ?? '';
    $suffix     = $user['suffix'] ?? '';
    $fullName   = trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix);

    // ID + STATUS
    $opportunityId = Str::uuid();
    $status = (strcasecmp(trim($userRole), 'SDA Assistant') === 0) ? 'Approved' : 'Pending';

    $isIntermittent = $request->boolean('intermittent', false);

    // ---------------------------------------------------------------------
    // SCHEDULE HANDLING (KEEP EXISTING)
    // ---------------------------------------------------------------------
    if ($isIntermittent) {
        $scheduleData = collect($request->input('schedule', []))->map(function ($item) use ($validated) {
            return [
                'date'           => $item['date'] ?? null,
                'start_time'     => $item['start_time'] ?? null,
                'end_time'       => $item['end_time'] ?? null,
                'target_number'  => $item['target_number'] ?? null,
                'food_provided'  => $item['food_provided'] ?? $validated['food_provided'] ?? 'no',
            ];
        })->values()->toArray();
    } else {
        $scheduleData = [[
            'date'           => $request->input('start_date'),
            'start_time'     => $request->input('schedule.0.start_time'),
            'end_time'       => $request->input('schedule.0.end_time'),
            'target_number'  => $request->input('schedule.0.target_number'),
            'food_provided'  => $validated['food_provided'],
        ]];
    }

    // ---------------------------------------------------------------------
    // URGENT AUTO-DETECTION
    // ---------------------------------------------------------------------
    $today = now()->startOfDay();

    if ($isIntermittent) {
        $firstDate = collect($request->input('schedule', []))
            ->pluck('date')
            ->filter()
            ->map(fn($d) => \Carbon\Carbon::parse($d))
            ->sort()
            ->first();
    } else {
        $firstDate = $validated['start_date']
            ? \Carbon\Carbon::parse($validated['start_date'])
            : null;
    }

    $isUrgent = 'no';
    if ($firstDate && $firstDate->diffInDays($today) <= 3) {
        $isUrgent = 'yes';
    }

    // ---------------------------------------------------------------------
    // ✨ NEW TASK SYSTEM
    // ---------------------------------------------------------------------
    $selectedTasks = $request->input('tasks', []);
    $taskList = [];

    $taskDefinitions = [
        "Marshalling" => [
            "skills" => "Crowd management, clear communication, alert, patient",
            "description" => "Helps manage crowd flow, guide participants, and keep pathways clear."
        ],
        "Registration" => [
            "skills" => "Organized, detail-oriented, friendly, basic data encoding",
            "description" => "Handles check-in, attendance lists, and assists participants."
        ],
        "Ushering" => [
            "skills" => "Approachable, good communication, presentable",
            "description" => "Guides attendees to seats and helps answer questions."
        ],
        "Photography" => [
            "skills" => "Basic photography skills, attention to detail",
            "description" => "Takes photos of key moments and activities."
        ],
        "Videography" => [
            "skills" => "Basic video recording, timing awareness",
            "description" => "Records event highlights and important segments."
        ],
        "Technical Support" => [
            "skills" => "Basic IT skills, calm under pressure",
            "description" => "Helps manage audio, projector, and technical systems."
        ],
        "Creatives/Design" => [
            "skills" => "Basic design sense, creativity",
            "description" => "Prepares visual materials, layouts, and social media assets."
        ],
        "Hosting" => [
            "skills" => "Confident speaker, energetic, good diction",
            "description" => "Leads the program, introduces speakers, engages the audience."
        ],
        "Logistics" => [
            "skills" => "Physically capable, organized",
            "description" => "Transfers equipment/materials and supports operations."
        ],
        "Admin Support" => [
            "skills" => "Detail-oriented, encoding, filing",
            "description" => "Prepares documents and encodes data."
        ],
    ];

    foreach ($selectedTasks as $taskName) {
        if ($taskName === "Others") continue;

        if (isset($taskDefinitions[$taskName])) {
            $taskList[] = [
                "task" => $taskName,
                "skills" => $taskDefinitions[$taskName]["skills"],
                "description" => $taskDefinitions[$taskName]["description"]
            ];
        }
    }

    if (in_array("Others", $selectedTasks)) {
        $taskList[] = [
            "task" => "Others",
            "skills" => $request->input('task_other_skills'),
            "description" => $request->input('task_other_description'),
        ];
    }

    // ---------------------------------------------------------------------
    // FINAL DATA TO SAVE
    // ---------------------------------------------------------------------
    $opportunityData = [
        'opportunity_id'       => $opportunityId,
        'submitted_by'         => $userEmail,
        'submitted_by_role'    => $userRole,
        'submitted_by_name'    => $fullName,

        'title'                => $validated['title'],
        'location'             => $validated['location'],
        'start_date'           => $validated['start_date'],
        'description'          => $validated['description'],

        // ✨ NEW TASK SYSTEM SAVED HERE
        'tasks'                => $taskList,

        'status'               => $status,
        'submitted_at'         => now()->toDateTimeString(),
        'schedule'             => $scheduleData,
        'is_intermittent'      => $isIntermittent,
        'intermittent_details' => $validated['intermittent_details'] ?? null,
        'is_urgent'            => $isUrgent,
        'food_provided'        => $validated['food_provided'],
    ];

    // ---------------------------------------------------------------------
    // APPROVAL CHAIN
    // ---------------------------------------------------------------------
    if ($status === 'Pending') {
        $approvalChain = [];
        $admins = $this->database->getReference('admins')->getValue();

        foreach ($admins ?? [] as $admin) {
            if (strcasecmp(trim($admin['role'] ?? ''), 'SDA Assistant') === 0) {
                $approvalChain[] = [
                    'role' => 'SDA Assistant',
                    'email' => $admin['email'],
                    'status' => 'Pending',
                ];
                break;
            }
        }

        $opportunityData['approval_chain'] = $approvalChain;
    }

    // ---------------------------------------------------------------------
    // SAVE TO FIREBASE
    // ---------------------------------------------------------------------
    $this->database
        ->getReference('opportunities/' . $opportunityId)
        ->set($opportunityData);

    // ---------------------------------------------------------------------
    // REDIRECT RESPONSE
    // ---------------------------------------------------------------------
    if ($status === 'Approved') {
        return redirect()->route('volunteers-admin.opportunities')
            ->with('success', 'Opportunity submitted and auto-approved!');
    }

    return redirect()->route('volunteers-offices.home')
        ->with('success', 'Opportunity submitted! Pending approval by SDA Assistant.');
}

}
