<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OpportunitySubmittedMailable;
use App\Mail\AdminOpportunityRequestMailable;
use App\Mail\NewOpportunityForScholarsMailable;




class SubmitOpportunityController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(
                storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json')
            )
            ->withDatabaseUri(
                'https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/'
            );

        $this->database = $factory->createDatabase();
    }

    /**
     * Sanitize a string to be used as a Firebase key.
     */
    private function sanitizeFirebaseKey(string $key): string
    {
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
            'intermittent_details' => 'nullable|string',
            'food_provided' => 'required|in:yes,no',
        ]);

        // -------------------------------
        // Session user info
        // -------------------------------
        $user = session('volunteers_office') ?? session('volunteers_admin') ?? [];
        $userEmail = $user['email'] ?? abort(403, 'No logged in user email found.');

        $isOffice = session()->has('volunteers_office');

        $userRole = $isOffice
            ? ($user['office_name'] ?? 'Office')
            : ($user['role'] ?? 'Admin');

        $fullName = trim(
            ($user['first_name'] ?? '') . ' ' .
            ($user['middle_name'] ?? '') . ' ' .
            ($user['last_name'] ?? '') . ' ' .
            ($user['suffix'] ?? '')
        );

        $opportunityId = Str::uuid();

        // Auto-approve only SDA Assistant
        $status = (strcasecmp(trim($userRole), 'SDA Assistant') === 0)
            ? 'Approved'
            : 'Pending';

        // -------------------------------
        // Task definitions
        // -------------------------------
        $taskDefinitions = [
            "Marshalling" => [
                "skills" => "Crowd management, clear communication, alert, patient",
                "description" => "Manages crowd flow and guides participants."
            ],
            "Registration" => [
                "skills" => "Organized, detail-oriented, friendly",
                "description" => "Handles participant check-in."
            ],
            "Ushering" => [
                "skills" => "Approachable, good communication",
                "description" => "Guides attendees and assists inquiries."
            ],
            "Photography" => [
                "skills" => "Basic photography skills",
                "description" => "Captures event photos."
            ],
            "Videography" => [
                "skills" => "Basic video recording",
                "description" => "Records event highlights."
            ],
            "Technical Support" => [
                "skills" => "Basic IT skills",
                "description" => "Manages microphones and projectors."
            ],
            "Creatives/Design" => [
                "skills" => "Creativity, basic design",
                "description" => "Handles visuals and creatives."
            ],
            "Hosting" => [
                "skills" => "Confident speaker",
                "description" => "Hosts the program."
            ],
            "Logistics" => [
                "skills" => "Physically capable",
                "description" => "Handles materials and setup."
            ],
            "Admin Support" => [
                "skills" => "Detail-oriented",
                "description" => "Handles documentation."
            ],
        ];

        // -------------------------------
        // Build tasks
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

            if (!is_array($tasks)) continue;

            $intermittentTasksByDate[$firebaseDateKey] = [];

            $scheduleEntry = collect($request->input('schedule', []))
                ->firstWhere('date', $date);

            $startTime = $scheduleEntry['start_time'] ?? null;
            $endTime   = $scheduleEntry['end_time'] ?? null;

            foreach ($tasks as $taskName => $neededRaw) {
                $needed = (int) $neededRaw;
                if ($needed <= 0) continue;

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
        // Schedule
        // -------------------------------
        $scheduleData = $isIntermittent
            ? collect($request->input('schedule', []))->values()->toArray()
            : [[
                'date' => $validated['start_date'],
                'start_time' => $request->input('schedule.0.start_time'),
                'end_time' => $request->input('schedule.0.end_time'),
                'food_provided' => $validated['food_provided'],
            ]];

        // -------------------------------
        // Urgency
        // -------------------------------
        $today = Carbon::now('Asia/Manila')->startOfDay();
        $firstDate = $scheduleData[0]['date'] ?? null;
        $isUrgent = $firstDate && $today->diffInDays(Carbon::parse($firstDate), false) <= 3
            ? 'yes'
            : 'no';

        // -------------------------------
        // Save to Firebase
        // -------------------------------
        $this->database
            ->getReference('opportunities/' . $opportunityId)
            ->set([
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
            ]);

        // -------------------------------
        // EMAILS
        // -------------------------------
// -------------------------------
// EMAILS
// -------------------------------
try {
    if ($isOffice) {

        // 1️⃣ Office confirmation email
        Mail::to($userEmail)->send(
            new OpportunitySubmittedMailable($fullName ?: 'Office User')
        );

        // 2️⃣ Fetch SDA Assistant(s) from Firebase
        $adminsSnapshot = $this->database
            ->getReference('admins')
            ->getValue();

        if (is_array($adminsSnapshot)) {
            foreach ($adminsSnapshot as $admin) {

                if (
                    isset($admin['role'], $admin['email']) &&
                    trim($admin['role']) === 'SDA Assistant'
                ) {
                    Mail::to($admin['email'])->send(
                        new AdminOpportunityRequestMailable([
                            'name' => $fullName,
                            'office' => $userRole,
                            'date_time' => now()->format('F d, Y h:i A'),
                            'location' => $validated['location'],
                            'title' => $validated['title'],
                        ])
                    );
                }
            }
        }

        // 3️⃣ Notify ALL scholars about the new opportunity
        $scholarsSnapshot = $this->database
            ->getReference('scholars')
            ->getValue();

        /**
         * Scholars structure:
         * scholars
         *  └ academic_year
         *    └ term
         *      └ scholarship_type
         *        └ scholar_id => scholar data
         */
        if (is_array($scholarsSnapshot)) {
            foreach ($scholarsSnapshot as $academicYear) {
                if (!is_array($academicYear)) continue;

                foreach ($academicYear as $term) {
                    if (!is_array($term)) continue;

                    foreach ($term as $scholarshipType) {
                        if (!is_array($scholarshipType)) continue;

                        foreach ($scholarshipType as $scholar) {
                            if (!is_array($scholar)) continue;

                            if (
                                isset($scholar['email']) &&
                                filter_var($scholar['email'], FILTER_VALIDATE_EMAIL)
                            ) {
                                Mail::to($scholar['email'])
                                    ->send(new NewOpportunityForScholarsMailable());
                            }
                        }
                    }
                }
            }
        }
    }
} catch (\Throwable $e) {
    Log::error('Opportunity email failed', [
        'error' => $e->getMessage()
    ]);
}

        // -------------------------------
        // Redirect
        // -------------------------------
        return ($status === 'Approved')
            ? redirect()->route('volunteers-admin.opportunities')
                ->with('success', 'Opportunity submitted and auto-approved!')
            : redirect()->route('volunteers-offices.request-volunteer')
                ->with('success', 'Opportunity submitted! Pending approval.');
    }
}
