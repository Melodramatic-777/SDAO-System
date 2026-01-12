<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class OpportunitiesController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function createOpportunity()
    {
        return view('volunteers-offices.create-opportunity');
    }


public function inviteScholar(Request $request)
{
    /* ========== VALIDATION ========== */
    $validated = $request->validate([
        'scholar_id'          => 'required|string',
        'opportunity_id'      => 'required|string',
        'schedule_indices'    => 'array',
        'schedule_indices.*'  => 'date',
        'tasks'               => 'required|array',
    ]);

    $scholarId       = $validated['scholar_id'];
    $opportunityId   = $validated['opportunity_id'];
    $scheduleDates   = $validated['schedule_indices'] ?? [];
    $selectedTasks   = $validated['tasks'];

    /* ========== FIND SCHOLAR ========== */
    $scholars = $this->database->getReference('scholars')->getValue();
    $selectedScholar = null;
    $scholarPath = null;

    if ($scholars) {
        foreach ($scholars as $yearKey => $terms) {
            foreach ($terms as $termKey => $types) {
                foreach ($types as $typeKey => $students) {
                    foreach ($students as $scholarKey => $scholar) {
                        if (($scholar['student_no'] ?? '') === $scholarId) {
                            $selectedScholar = $scholar;
                            $scholarPath = "scholars/{$yearKey}/{$termKey}/{$typeKey}/{$scholarKey}";
                            break 4;
                        }
                    }
                }
            }
        }
    }

    if (!$selectedScholar) {
        return back()->with('error', 'Scholar not found.');
    }

    /* ========== FIND OPPORTUNITY ========== */
    $opportunity = $this->database
        ->getReference("opportunities/{$opportunityId}")
        ->getValue();

    if (!$opportunity) {
        return back()->with('error', 'Opportunity not found.');
    }

    $participants = $opportunity['participants'] ?? [];

    /* ========== DUPLICATE CHECK ========== */
    foreach ($participants as $p) {
        if (($p['scholar_id'] ?? '') === $scholarId) {
            return back()->with('warning', 'Scholar already invited.');
        }
    }

    /* ========== BUILD PARTICIPANT ========== */
    $newParticipant = [
        'scholar_id' => $scholarId,
        'name'       => trim(($selectedScholar['first_name'] ?? '') . ' ' . ($selectedScholar['last_name'] ?? '')),
        'role'       => 'Volunteer',
        'status'     => 'Pending Invite',
        'tasks'      => $selectedTasks,
        'schedule_dates' => $scheduleDates,
        'invited_at' => Carbon::now()->toDateTimeString(),
    ];

    /* ========== SAVE TO OPPORTUNITY (SAFE PUSH) ========== */
    $this->database
        ->getReference("opportunities/{$opportunityId}/participants")
        ->push($newParticipant);

    /* ========== SAVE TO SCHOLAR RECORD ========== */
    $this->database
        ->getReference("{$scholarPath}/invited/{$opportunityId}")
        ->set([
            'opportunity_id' => $opportunityId,
            'status'         => 'Pending Invite',
            'tasks'          => $selectedTasks,
            'schedule_dates' => $scheduleDates,
            'invited_at'     => Carbon::now()->toDateTimeString(),
        ]);

    return back()->with('success', 'Scholar invited successfully!');
}

public function showScholars(Request $request)
{
    
    // Raw nested data from Firebase: scholars => [ '2021-2022' => [ '1st' => [ 'BLUE' => [ ... ] ] ] ]
    $scholarsRaw = $this->database->getReference('scholars')->getValue();

    // Determine latest academic year key (e.g. "2024-2025", "2021-2022")
    $latestYear = null;
    $latestTerm = null;

    if (!empty($scholarsRaw) && is_array($scholarsRaw)) {
        $yearKeys = array_keys($scholarsRaw);

        // Convert keys like "2021-2022" to comparable integers (use start year if present)
        usort($yearKeys, function ($a, $b) {
            $getStart = function ($k) {
                if (preg_match('/(\d{4})/', $k, $m)) return (int)$m[1];
                return (int)$k;
            };
            return $getStart($b) <=> $getStart($a); // desc
        });

        $latestYear = $yearKeys[0] ?? null;

        // Determine latest term inside that year (e.g. '1st', '2nd', or other)
        if ($latestYear && isset($scholarsRaw[$latestYear]) && is_array($scholarsRaw[$latestYear])) {
            $termKeys = array_keys($scholarsRaw[$latestYear]);

            // Prefer numeric ordering if terms are like '1st','2nd'
            usort($termKeys, function ($a, $b) {
                $extractNum = function ($t) {
                    if (preg_match('/(\d+)/', $t, $m)) return (int)$m[1];
                    return null;
                };
                $na = $extractNum($a);
                $nb = $extractNum($b);
                if ($na !== null && $nb !== null) return $nb <=> $na; // numeric desc
                // fallback: string desc
                return strcmp($b, $a);
            });

            $latestTerm = $termKeys[0] ?? null;
        }
    }

    // Flatten scholars for listing (same as before)
// Flatten scholars ONLY under latest academic year and term
$flattened = [];

if ($latestYear && $latestTerm &&
    isset($scholarsRaw[$latestYear][$latestTerm]) &&
    is_array($scholarsRaw[$latestYear][$latestTerm])) {

    foreach ($scholarsRaw[$latestYear][$latestTerm] as $scholarshipGroup) {
        if (is_array($scholarshipGroup)) {
            foreach ($scholarshipGroup as $scholar) {
                $flattened[] = $scholar;
            }
        }
    }
}                

    
    $collection = collect($flattened);

    // Filters (keep same behavior)
    if ($request->filled('scholarship_type')) {
        $collection = $collection->where('scholarship_type', $request->scholarship_type);
    }

    if ($request->filled('search')) {
        $search = strtolower($request->search);
        $collection = $collection->filter(function ($scholar) use ($search) {
            $fullName = strtolower(
                ($scholar['first_name'] ?? '') . ' ' .
                ($scholar['middle_name'] ?? '') . ' ' .
                ($scholar['last_name'] ?? '') . ' ' .
                ($scholar['suffix'] ?? '')
            );
            return str_contains($fullName, $search);
        });
    }

    $perPage = 10;
    $page = $request->get('page', 1);
    $scholarsPaginated = new LengthAwarePaginator(
        $collection->forPage($page, $perPage)->values(),
        $collection->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    $scholarshipTypes = collect($flattened)->pluck('scholarship_type')->unique()->filter()->values();

    // Opportunities for dropdown (only Approved)
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];
    $opportunities = array_filter($opportunities, function ($opportunity) {
        return isset($opportunity['status']) && $opportunity['status'] === 'Approved';
    });
    $opportunities = array_values($opportunities);

    return view('volunteers-admin.invite-volunteer', [
        'scholars'         => $scholarsPaginated,
        'scholarshipTypes' => $scholarshipTypes,
        'opportunities'    => $opportunities,
        'latestYear'       => $latestYear,
        'latestTerm'       => $latestTerm,
    ]);
}

public function showScholarsOffice(Request $request)
{
    
    // Raw nested data from Firebase: scholars => [ '2021-2022' => [ '1st' => [ 'BLUE' => [ ... ] ] ] ]
    $scholarsRaw = $this->database->getReference('scholars')->getValue();

    // Determine latest academic year key (e.g. "2024-2025", "2021-2022")
    $latestYear = null;
    $latestTerm = null;

    if (!empty($scholarsRaw) && is_array($scholarsRaw)) {
        $yearKeys = array_keys($scholarsRaw);

        // Convert keys like "2021-2022" to comparable integers (use start year if present)
        usort($yearKeys, function ($a, $b) {
            $getStart = function ($k) {
                if (preg_match('/(\d{4})/', $k, $m)) return (int)$m[1];
                return (int)$k;
            };
            return $getStart($b) <=> $getStart($a); // desc
        });

        $latestYear = $yearKeys[0] ?? null;

        // Determine latest term inside that year (e.g. '1st', '2nd', or other)
        if ($latestYear && isset($scholarsRaw[$latestYear]) && is_array($scholarsRaw[$latestYear])) {
            $termKeys = array_keys($scholarsRaw[$latestYear]);

            // Prefer numeric ordering if terms are like '1st','2nd'
            usort($termKeys, function ($a, $b) {
                $extractNum = function ($t) {
                    if (preg_match('/(\d+)/', $t, $m)) return (int)$m[1];
                    return null;
                };
                $na = $extractNum($a);
                $nb = $extractNum($b);
                if ($na !== null && $nb !== null) return $nb <=> $na; // numeric desc
                // fallback: string desc
                return strcmp($b, $a);
            });

            $latestTerm = $termKeys[0] ?? null;
        }
    }

    // Flatten scholars for listing (same as before)
// Flatten scholars ONLY under latest academic year and term
$flattened = [];

if ($latestYear && $latestTerm &&
    isset($scholarsRaw[$latestYear][$latestTerm]) &&
    is_array($scholarsRaw[$latestYear][$latestTerm])) {

    foreach ($scholarsRaw[$latestYear][$latestTerm] as $scholarshipGroup) {
        if (is_array($scholarshipGroup)) {
            foreach ($scholarshipGroup as $scholar) {
                $flattened[] = $scholar;
            }
        }
    }
}                

    
    $collection = collect($flattened);

    // Filters (keep same behavior)
    if ($request->filled('scholarship_type')) {
        $collection = $collection->where('scholarship_type', $request->scholarship_type);
    }

    if ($request->filled('search')) {
        $search = strtolower($request->search);
        $collection = $collection->filter(function ($scholar) use ($search) {
            $fullName = strtolower(
                ($scholar['first_name'] ?? '') . ' ' .
                ($scholar['middle_name'] ?? '') . ' ' .
                ($scholar['last_name'] ?? '') . ' ' .
                ($scholar['suffix'] ?? '')
            );
            return str_contains($fullName, $search);
        });
    }

    $perPage = 10;
    $page = $request->get('page', 1);
    $scholarsPaginated = new LengthAwarePaginator(
        $collection->forPage($page, $perPage)->values(),
        $collection->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    $scholarshipTypes = collect($flattened)->pluck('scholarship_type')->unique()->filter()->values();

    // Opportunities for dropdown (only Approved)
// Get logged-in office email
$office = session('volunteers_office') ?? [];
$officeEmail = $office['email'] ?? null;

// Fetch all opportunities
$allOpportunities = $this->database->getReference('opportunities')->getValue() ?? [];

// Filter: Approved + Submitted by logged-in office only
$opportunities = array_filter($allOpportunities, function ($op) use ($officeEmail) {
    return isset($op['status'], $op['submitted_by']) 
        && $op['status'] === 'Approved'
        && $op['submitted_by'] === $officeEmail;
});

$opportunities = array_values($opportunities);


    return view('volunteers-offices.invite-volunteer-office', [
        'scholars'         => $scholarsPaginated,
        'scholarshipTypes' => $scholarshipTypes,
        'opportunities'    => $opportunities,
        'latestYear'       => $latestYear,
        'latestTerm'       => $latestTerm,
    ]);
}


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
// ---------------------------------------------------------------------
// URGENT AUTO-DETECTION
// ---------------------------------------------------------------------
$today = now('Asia/Manila')->startOfDay();

if ($isIntermittent) {
    $firstDate = collect($request->input('schedule', []))
        ->pluck('date')
        ->filter()
        ->map(fn($d) => \Carbon\Carbon::parse($d, 'Asia/Manila'))
        ->sort()
        ->first();
} else {
    $firstDate = $validated['start_date']
        ? \Carbon\Carbon::parse($validated['start_date'], 'Asia/Manila')
        : null;
}

// Set is_urgent based on date difference
if ($firstDate) {
    $daysUntil = $today->diffInDays($firstDate, false); // negative if in the past
    $isUrgent = ($daysUntil >= 0 && $daysUntil <= 3) ? 'yes' : 'no';
} else {
    $isUrgent = 'no';
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

    return redirect()->route('volunteers-offices.request-volunteer')
        ->with('success', 'Opportunity submitted! Pending approval by SDA Assistant.');
}



    public function home()
    {
        $user = session('volunteers_office') ?? [];
        $userEmail = $user['email'] ?? null;

        if (!$userEmail) {
            abort(403, 'No logged in user email found.');
        }

        $opportunities = $this->database->getReference('opportunities')
            ->orderByChild('submitted_by')
            ->equalTo($userEmail)
            ->getValue() ?? [];

        usort($opportunities, function ($a, $b) {
            return strtotime($b['submitted_at']) <=> strtotime($a['submitted_at']);
        });

        return view('volunteers-offices.home', [
            'opportunities' => $opportunities
        ]);
    }

    
public function opportunities()
{
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];

    // Ensure numeric indexing so sorting works
    $opportunities = array_values($opportunities);

    // Sort newest first
    usort($opportunities, function ($a, $b) {
        return strtotime($b['submitted_at'] ?? 0) <=> strtotime($a['submitted_at'] ?? 0);
    });

    // Group opportunities by submitted_by_role (office name)
    $grouped = [];
    foreach ($opportunities as $opportunity) {
        $office = $opportunity['submitted_by_role'] ?? 'Unknown Office';
        $grouped[$office][] = $opportunity;
    }

    ksort($grouped); // optional alphabetical

    return view('volunteers-admin.opportunities', [
        'groupedOpportunities' => $grouped,
        'opportunities' => $opportunities, // <-- pass the flattened list for the view
    ]);
}


public function updateStatus(Request $request, $id)
{
    $validated = $request->validate([
        'status'  => 'required|string|in:Approved,Returned',
        'reason'  => 'nullable|string|max:255', // <-- Added reason
        'comment' => 'nullable|string|max:1000',
    ]);

    // Reference to Firebase
    $reference = $this->database->getReference('opportunities/' . $id);

    $data = [
        'status' => $validated['status'],
    ];

    if ($validated['status'] === 'Returned') {
        if (!empty($validated['reason'])) {
            $data['return_reason'] = $validated['reason'];
        }
        if (!empty($validated['comment'])) {
            $data['return_comment'] = $validated['comment'];
        }
        $data['returned_at'] = now()->toDateTimeString();
    }

    $reference->update($data);

    return redirect()->back()->with('success', 'Status updated successfully.');
}

    // FOR SCHOLARS

    public function scholarOpportunities()
    {
        $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];

        // filter approved
        $approved = array_filter($opportunities, function ($op) {
            return isset($op['status']) && $op['status'] === 'Approved';
        });

        // normalize participants and schedule
foreach ($approved as $key => &$op) {
    if (!empty($op['participants']) && is_array($op['participants'])) {
        $op['participants'] = array_values($op['participants']); // flatten
    } else {
        $op['participants'] = [];
    }

    if (empty($op['schedule']) || !is_array($op['schedule'])) {
        $op['schedule'] = [[]];
    }
}

        unset($op);

        $approved = array_values($approved);
        usort($approved, function ($a, $b) {
            return strtotime($b['submitted_at']) <=> strtotime($a['submitted_at']);
        });

        $officesData = $this->database->getReference('offices')->getValue() ?? [];
$offices = [];

foreach ($officesData as $office) {
    if (isset($office['name'])) {
        $offices[] = ['name' => $office['name']];
    }
}

return view('scholars.volunteer-opportunities', [
    'opportunities' => $approved,
    'offices' => $offices
]);

    }

public function join($id)
{
    $scholar = session('scholar_accounts');

    if (!$scholar) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $scholarId   = is_array($scholar) ? ($scholar['student_no'] ?? null) : ($scholar->student_no ?? null);
    $firstName   = is_array($scholar) ? ($scholar['first_name'] ?? '') : ($scholar->first_name ?? '');
    $lastName    = is_array($scholar) ? ($scholar['last_name'] ?? '') : ($scholar->last_name ?? '');
    $scholarName = trim($firstName . ' ' . $lastName);

    if (!$scholarId) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Get opportunity
    $ref = $this->database->getReference('opportunities/' . $id);
    $opportunity = $ref->getValue();

    if (!$opportunity) {
        return response()->json(['error' => 'Opportunity not found'], 404);
    }

    // Normalize participants
    $participants = $opportunity['participants'] ?? [];
    if (!is_array($participants)) {
        $participants = [];
    }

    // Check duplicate
    foreach ($participants as $p) {
        if (($p['scholar_id'] ?? null) == $scholarId) {
            return response()->json(['message' => 'Already joined'], 200);
        }
    }

    // Check capacity
    $participantCount = count($participants);
    $target = $opportunity['schedule'][0]['target_number'] ?? null;

    if ($target !== null && $participantCount >= (int)$target) {
        return response()->json(['error' => 'Opportunity is full'], 400);
    }

    // Add new participant
    $newParticipant = [
        'scholar_id' => $scholarId,
        'name'       => $scholarName,
        'joined_at'  => now()->toDateTimeString(),
        'status'     => 'Accepted',
    ];

    $participants[] = $newParticipant;

    try {
        // Update in realtime database
        $ref->update([
            'participants' => $participants
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error'   => 'Failed to save participant',
            'details' => $e->getMessage()
        ], 500);
    }

    return response()->json(['message' => 'Joined successfully'], 200);
}
public function scholarInvites(Request $request)
{
    $scholarId = session('scholar_accounts.student_no');

    if (!$scholarId) {
        return redirect()->route('scholars.login')->with('error', 'You must log in first.');
    }

    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];
    $finalInvites = [];

    foreach ($opportunities as $oppId => $opp) {

        if (!isset($opp['participants']) || !is_array($opp['participants'])) {
            continue;
        }

        foreach ($opp['participants'] as $pKey => $p) {

            // Accept all possible ID formats
            $participantScholarId =
                $p['scholar_id']
                ?? $p['id']
                ?? $p['scholarID']
                ?? null;

            if ($participantScholarId !== $scholarId) {
                continue;
            }

            // Only show pending invites
            if (!isset($p['status']) || $p['status'] !== 'Pending Invite') {
                continue;
            }

            // Extract tasks
            $tasks = $p['tasks'] ?? [];

            $finalInvites[] = [
                'participant_key' => $pKey,  // 🔥 needed for update
                'opportunity_id'  => $oppId,
                'title'           => $opp['title'] ?? 'Unknown Title',
                'office'          => $opp['office'] ?? 'Unknown Office',
                'invited_at'      => $p['invited_at'] ?? '',
                'status'          => $p['status'],
                'tasks'           => $tasks,
                'is_intermittent' => $opp['is_intermittent'] ?? false,
            ];
        }
    }

    return view('scholars.invites', ['invites' => $finalInvites]);
}
public function acceptInvite(Request $request)
{
    $request->validate([
        'opportunity_id' => 'required',
        'participant_key' => 'required',
    ]);

    $oppId = $request->opportunity_id;
    $pKey = $request->participant_key;

    $ref = "opportunities/{$oppId}/participants/{$pKey}";

    // If intermittent, check selected dates
    if ($request->has('selected_dates')) {

        // Store only the dates the scholar accepted
        $this->database->getReference("$ref/selected_dates")
            ->set($request->selected_dates);
    }

    // Update status
    $this->database->getReference("$ref/status")
        ->set('Joined');

    return back()->with('success', 'Invite accepted successfully!');
}


public function cancelOpportunity($id)
{
    // Get the opportunity from Firebase
    $ref = $this->database->getReference('opportunities/' . $id);
    $opportunity = $ref->getValue();

    if (!$opportunity) {
        return back()->with('error', 'Opportunity not found.');
    }

    // ✅ Save canceled opportunity in Firebase
    $canceledRef = $this->database->getReference('canceled_opportunities/' . $id);
    $opportunity['canceled_at'] = now()->toDateTimeString();
    $canceledRef->set($opportunity);

    // ✅ Remove "invited" tag for participants in scholars
    if (!empty($opportunity['participants']) && is_array($opportunity['participants'])) {
        $scholars = $this->database->getReference('scholars')->getValue() ?? [];

        foreach ($opportunity['participants'] as $participant) {
            $scholarId = $participant['scholar_id'] ?? null;
            if (!$scholarId) continue;

            // Loop through scholars to find match
            foreach ($scholars as $yearKey => $terms) {
                foreach ($terms as $termKey => $types) {
                    foreach ($types as $typeKey => $students) {
                        foreach ($students as $scholarKey => $scholar) {
                            if (($scholar['student_no'] ?? '') === $scholarId) {
                                if (($scholar['invited'] ?? null) === $id) {
                                    $this->database
                                        ->getReference("scholars/{$yearKey}/{$termKey}/{$typeKey}/{$scholarKey}/invited")
                                        ->remove();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // ✅ Remove opportunity from main opportunities path
    $ref->remove();

    return back()->with('success', 'Opportunity canceled successfully.');
}


public function history()
{
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];

    // Only show approved or completed ones (you can adjust filter as needed)
    $filtered = array_filter($opportunities, function ($op) {
        return isset($op['status']) && in_array($op['status'], ['Approved', 'Completed']);
    });

    // Group opportunities by office name
    $grouped = [];
    foreach ($filtered as $id => $opportunity) {
        $office = $opportunity['submitted_by_role'] ?? 'Unknown Office';
        $grouped[$office][] = $opportunity;
    }

    // Sort offices alphabetically
    ksort($grouped);

    return view('volunteers-admin.history', [
        'groupedOpportunities' => $grouped
    ]);
}


public function officeOpportunities($office)
{
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];

    // Filter by submitted_by_role (office name)
    $filtered = array_filter($opportunities, function ($op) use ($office) {
        return isset($op['submitted_by_role']) && $op['submitted_by_role'] === urldecode($office);
    });

    // Sort newest first
    usort($filtered, function ($a, $b) {
        return strtotime($b['submitted_at'] ?? 0) <=> strtotime($a['submitted_at'] ?? 0);
    });

    return view('volunteers-admin.office-opportunities', [
        'office' => urldecode($office),
        'opportunities' => $filtered
    ]);
}


public function historyOffice()
{
    $user = session('volunteers_office') ?? [];
    $userEmail = $user['email'] ?? null;

    if (!$userEmail) {
        abort(403, 'No logged in user email found.');
    }

    // Get only office's submitted opportunities
    $opportunities = $this->database->getReference('opportunities')
        ->orderByChild('submitted_by')
        ->equalTo($userEmail)
        ->getValue() ?? [];

    // Sort by newest
    usort($opportunities, function ($a, $b) {
        return strtotime($b['submitted_at']) <=> strtotime($a['submitted_at']);
    });

    return view('volunteers-offices.history-office', [
        'opportunities' => $opportunities
    ]);
}


public function onTheSpotVolunteer(Request $request)
{
    // Validate form input
    $request->validate([
        'office' => 'required|string',
        'reasons' => 'array',
        'other_reason' => 'nullable|string'
    ]);

    // Collect checkbox reasons
    $reasonList = $request->reasons ?? [];

    // If "Others" was specified, append it
    if ($request->other_reason) {
        $reasonList[] = $request->other_reason;
    }

    // Build record data
    $data = [
        'student_no'   => session('scholar_accounts.student_no'),
        'student_name' => session('scholar_accounts.first_name') . ' ' . session('scholar_accounts.last_name'),
        'office'       => $request->office,
        'reasons'      => $reasonList,
        'status'       => 'Pending', // default status for offices to review
        'timestamp'    => now()->toDateTimeString()
    ];

    // Save to Firebase
    $ref = $this->database->getReference('on_the_spot_volunteers')->push($data);

    // If push failed
    if (!$ref) {
        return back()->with('error', 'Failed to submit request. Please try again.');
    }

    return back()->with('success', 'Your On-The-Spot volunteer request has been submitted.');
}



public function edit($id)
{
    $opportunity = $this->database->getReference('opportunities/' . $id)->getValue();

    return view('volunteers-offices.update-opportunity', [
        'mode' => 'edit',
        'opportunity' => $opportunity
    ]);
}

public function update(Request $request, $id)
{
    date_default_timezone_set('Asia/Manila'); // Set PH timezone

    $data = $request->all();
    $data['status'] = 'Returned to SDAO'; // keep your required status update

    // ✅ Add/Edit timestamp fields
    $data['date_edited'] = date('Y-m-d');
    $data['time_edited'] = date('H:i:s');

    $this->database->getReference('opportunities/' . $id)->update($data);

    return redirect()->route('volunteers-offices.home')->with('success', 'Opportunity updated and resubmitted.');
}



}