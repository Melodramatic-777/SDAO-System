<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Pagination\LengthAwarePaginator;

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
    $validated = $request->validate([
        'scholar_id'     => 'required|string',
        'opportunity_id' => 'required|string',
    ]);

    $scholarId = $validated['scholar_id'];
    $opportunityId = $validated['opportunity_id'];

    // Fetch scholar info
    $scholars = $this->database->getReference('scholars')->getValue();
    $selectedScholar = null;
    $selectedKeys = [];

if ($scholars) {
    foreach ($scholars as $yearKey => $terms) {
        foreach ($terms as $termKey => $scholarshipTypes) {
            foreach ($scholarshipTypes as $typeKey => $students) {
                foreach ($students as $scholarKey => $scholar) {
                    if (($scholar['student_no'] ?? '') === $scholarId) {
                        $selectedScholar = $scholar;
                        $selectedKeys = [$yearKey, $termKey, $typeKey, $scholarKey];
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

    // ✅ Error handling if already invited
    if (isset($selectedScholar['invited']) && $selectedScholar['invited'] === $opportunityId) {
        return back()->with('error', 'This scholar is already invited to this opportunity.');
    }

    // Fetch opportunity
    $ref = $this->database->getReference('opportunities/' . $opportunityId);
    $opportunity = $ref->getValue();

    if (!$opportunity) {
        return back()->with('error', 'Opportunity not found.');
    }

    // Participants
    $participants = $opportunity['participants'] ?? [];
    if (!is_array($participants)) {
        $participants = [];
    }

    // Check duplicates in participants
    foreach ($participants as $p) {
        if (($p['scholar_id'] ?? null) === $scholarId) {
            return back()->with('warning', 'Scholar is already listed as a participant.');
        }
    }

    // Capacity check
    $target = $opportunity['schedule'][0]['target_number'] ?? null;
    if ($target !== null && count($participants) >= (int) $target) {
        return back()->with('error', 'Maximum capacity reached for this activity.');
    }

    // Add as volunteer participant
    $newParticipant = [
        'scholar_id' => $scholarId,
        'name'       => trim(($selectedScholar['first_name'] ?? '') . ' ' . ($selectedScholar['last_name'] ?? '')),
        'role'       => 'Volunteer',
        'invited_at' => now()->toDateTimeString(),
    ];

    $participants[] = $newParticipant;

    try {
        // ✅ Save updated participants list to Firebase
        $ref->update(['participants' => $participants]);

        // ✅ Save invited info under scholar record
[$yearKey, $termKey, $typeKey, $scholarKey] = $selectedKeys;

$this->database
    ->getReference("scholars/{$yearKey}/{$termKey}/{$typeKey}/{$scholarKey}/invited")
    ->set($opportunityId);

        return back()->with('success', 'Scholar invited successfully!');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to save data: ' . $e->getMessage());
    }
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
    ]);

    // Get current user session (admin or office)
$user = session('volunteers_office') ?? session('volunteers_admin') ?? [];
$userEmail = $user['email'] ?? null;

if (!$userEmail) {
    abort(403, 'No logged in user email found.');
}

// If office, use office_name as role
if (session()->has('volunteers_office')) {
    $userRole = $user['office_name'] ?? 'Office';
} else {
    $userRole = $user['role'] ?? null;
}

    // Compose full name
    $firstName  = $user['first_name'] ?? '';
    $middleName = $user['middle_name'] ?? '';
    $lastName   = $user['last_name'] ?? '';
    $suffix     = $user['suffix'] ?? '';
    $fullName   = trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix);

    $opportunityId = Str::uuid();
    $status = (strcasecmp(trim($userRole), 'SDA Assistant') === 0) ? 'Approved' : 'Pending';

$isIntermittent = $request->boolean('is_intermittent', false);

// Prepare schedule data depending on type
if ($isIntermittent) {
    // 🔸 Intermittent → multiple schedules
    $scheduleData = $validated['schedule'] ?? [];
} else {
    // 🔹 Regular → single schedule entry only
    $scheduleData = [[
        'date'          => $request->input('start_date'),
        'start_time'    => $request->input('schedule.0.start_time'),
        'end_time'      => $request->input('schedule.0.end_time'),
        'target_number' => $request->input('schedule.0.target_number'),
    ]];
}

// Now build the opportunity data
$opportunityData = [
    'opportunity_id'       => $opportunityId,
    'submitted_by'         => $userEmail,
    'submitted_by_role'    => $userRole,
    'submitted_by_name'    => $fullName, // added full name
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
];

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

    $this->database->getReference('opportunities/' . $opportunityId)->set($opportunityData);

    if ($status === 'Approved') {
        return redirect()->route('volunteers-admin.opportunities')
            ->with('success', 'Opportunity submitted and auto-approved!');
    }

    return redirect()->route('volunteers-offices.home')
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
        'comment' => 'nullable|string|max:1000',
    ]);

    // Corrected reference
    $reference = $this->database->getReference('opportunities/' . $id);

    $data = [
        'status' => $validated['status'],
    ];

    if ($validated['status'] === 'Returned' && !empty($validated['comment'])) {
        $data['decline_comment'] = $validated['comment'];
        $data['declined_at'] = now()->toDateTimeString();
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


public function scholarInvites()
{
    $scholar = session('scholar_accounts');

    if (!$scholar) {
        abort(403, 'Unauthorized');
    }

    $scholarId = $scholar['student_no'] ?? null;

    // Fetch all opportunities
    $opportunities = $this->database->getReference('opportunities')->getValue() ?? [];

    $invites = [];

    foreach ($opportunities as $op) {
        if (!empty($op['participants']) && is_array($op['participants'])) {
            foreach ($op['participants'] as $participant) {
                // ✅ Only show participants with "invited_at" (not "joined_at")
                if (
                    ($participant['scholar_id'] ?? '') === $scholarId &&
                    isset($participant['invited_at']) &&
                    !isset($participant['joined_at'])
                ) {
                    $invites[] = [
                        'opportunity_id' => $op['opportunity_id'] ?? '',
                        'office'         => $op['submitted_by_role'] ?? 'N/A',
                        'title'          => $op['title'] ?? 'Untitled',
                        'invited_at'     => $participant['invited_at'] ?? 'N/A',
                        'status'         => $participant['status'] ?? 'Pending',
                    ];
                }
            }
        }
    }

    return view('scholars.invites', [
        'invites' => $invites,
    ]);
}

public function respondToInvite(Request $request, $id)
{
    $validated = $request->validate([
        'response' => 'required|in:Accepted,Declined',
    ]);

    $scholar = session('scholar_accounts');

    if (!$scholar) {
        abort(403, 'Unauthorized');
    }

    $scholarId = $scholar['student_no'] ?? null;

    // Get the opportunity
    $ref = $this->database->getReference('opportunities/' . $id);
    $opportunity = $ref->getValue();

    if (!$opportunity) {
        return back()->with('error', 'Opportunity not found.');
    }

    $participants = $opportunity['participants'] ?? [];

    // Find participant and update status
    foreach ($participants as &$p) {
        if (($p['scholar_id'] ?? '') === $scholarId) {
            $p['status'] = $validated['response'];
            $p['responded_at'] = now()->toDateTimeString();
            break;
        }
    }

    unset($p);

    // Save updated list
    $ref->update(['participants' => $participants]);

    return back()->with('success', 'You have ' . strtolower($validated['response']) . ' the invite.');
}



public function cancelOpportunity($id)
{
    // Get the opportunity
    $ref = $this->database->getReference('opportunities/' . $id);
    $opportunity = $ref->getValue();

    if (!$opportunity) {
        return back()->with('error', 'Opportunity not found.');
    }

    // ✅ Save canceled opportunity in Firebase
    $canceledRef = $this->database->getReference('canceled_opportunities/' . $id);
    $opportunity['canceled_at'] = now()->toDateTimeString();
    $canceledRef->set($opportunity);

    // ✅ If participants existed, remove "invited" tag in scholars path
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
                        $this->database->getReference("scholars/{$yearKey}/{$termKey}/{$typeKey}/{$scholarKey}/invited")->remove();
                    }
                }
            }
        }
    }
}



    // ✅ Delete from opportunities table
    $ref->remove();

    return back()->with('success', 'Opportunity successfully canceled and moved to canceled records.');
}

}
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


}

