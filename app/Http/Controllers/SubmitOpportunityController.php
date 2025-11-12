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
            'task_details' => 'required|string',
            'skills_needed' => 'required|string',
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

        // Get user info from session
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

        $firstName  = $user['first_name'] ?? '';
        $middleName = $user['middle_name'] ?? '';
        $lastName   = $user['last_name'] ?? '';
        $suffix     = $user['suffix'] ?? '';
        $fullName   = trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix);

        $opportunityId = Str::uuid();
        $status = (strcasecmp(trim($userRole), 'SDA Assistant') === 0) ? 'Approved' : 'Pending';
        $isIntermittent = $request->boolean('is_intermittent', false);

        // Prepare schedule
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

        // Compose full opportunity data
        $opportunityData = [
            'opportunity_id'       => $opportunityId,
            'submitted_by'         => $userEmail,
            'submitted_by_role'    => $userRole,
            'submitted_by_name'    => $fullName,
            'title'                => $validated['title'],
            'location'             => $validated['location'],
            'start_date'           => $validated['start_date'],
            'task_details'         => $validated['task_details'],
            'skills_needed'        => $validated['skills_needed'],
            'status'               => $status,
            'submitted_at'         => now()->toDateTimeString(),
            'schedule'             => $scheduleData,
            'is_intermittent'      => $isIntermittent,
            'intermittent_details' => $validated['intermittent_details'] ?? null,
            'is_urgent'            => $request->input('is_urgent', 'no'),
            'food_provided'        => $validated['food_provided'],
        ];

        // Approval chain
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

        // Store to Firebase
        $this->database->getReference('opportunities/' . $opportunityId)->set($opportunityData);

        // Redirect
        if ($status === 'Approved') {
            return redirect()->route('volunteers-admin.opportunities')
                ->with('success', 'Opportunity submitted and auto-approved!');
        }

        return redirect()->route('volunteers-offices.home')
            ->with('success', 'Opportunity submitted! Pending approval by SDA Assistant.');
    }
}
