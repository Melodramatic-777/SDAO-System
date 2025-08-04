<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;

class ProposalsController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function createProposal()
    {
        return view('activities-user.create-proposal');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'objectives' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'required|string',
            'pdf_file' => 'required|file|mimes:pdf|max:102400',
            'schedule' => 'nullable|array',
            'schedule.*.date' => 'nullable|date',
            'schedule.*.event_name' => 'nullable|string',
            'schedule.*.start_time' => 'nullable|string',
            'schedule.*.end_time' => 'nullable|string',
            'schedule.*.target_participants' => 'nullable|string',
            'schedule.*.target_number' => 'nullable|integer',
        ]);

        $path = $request->file('pdf_file')->store('proposals', 'public');

        $user = session('activities_user') ?? [];
        $userEmail = $user['email'] ?? null;
        if (!$userEmail) {
            abort(403, 'No logged in user email found.');
        }

        $accounts = $this->database->getReference('organization_accounts')
            ->orderByChild('email')
            ->equalTo($userEmail)
            ->getValue();

        if (!$accounts) {
            abort(403, 'Your account not found in Firebase.');
        }

        $account = reset($accounts);
        $organizationId = trim($account['organization_id'] ?? '');
        $organizationName = trim($account['organization_name'] ?? 'N/A');

        $org = $this->database->getReference('organizations/' . $organizationId)->getValue();
        if (!$org) {
            abort(403, 'Organization not found.');
        }

        $schoolId = trim($org['school_id'] ?? '');
        $program = trim($org['program'] ?? '');

        $approvalChain = [];

        // === Adviser
        $adviser = null;
        $orgAccounts = $this->database->getReference('organization_accounts')
            ->orderByChild('organization_id')
            ->equalTo($organizationId)
            ->getValue();

        foreach ($orgAccounts ?? [] as $acc) {
            if (strcasecmp($acc['role'] ?? '', 'Adviser') === 0) {
                $adviser = $acc;
                break;
            }
        }

        if (!$adviser) {
            abort(403, 'No Adviser assigned.');
        }

        $approvalChain[] = [
            'role' => 'Adviser',
            'email' => $adviser['email'],
            'status' => 'Pending',
        ];

        // === Program Chair
        $chairs = $this->database->getReference('program_chairs')->getValue();
        foreach ($chairs ?? [] as $chair) {
            if (
                strcasecmp(trim($chair['school_id'] ?? ''), $schoolId) === 0 &&
                strcasecmp(trim($chair['program'] ?? ''), $program) === 0
            ) {
                $approvalChain[] = [
                    'role' => 'Program Chair',
                    'email' => $chair['email'],
                    'status' => 'Waiting',
                ];
                break;
            }
        }

        // === Dean
        $deans = $this->database->getReference('deans')->getValue();
        foreach ($deans ?? [] as $dean) {
            if (strcasecmp(trim($dean['school_id'] ?? ''), $schoolId) === 0) {
                $approvalChain[] = [
                    'role' => 'Dean',
                    'email' => $dean['email'],
                    'status' => 'Waiting',
                ];
                break;
            }
        }

        // === Activities Admin
        $admins = $this->database->getReference('admins')->getValue();
        foreach ($admins ?? [] as $admin) {
            if (strcasecmp(trim($admin['role'] ?? ''), 'Activities Admin') === 0) {
                $approvalChain[] = [
                    'role' => 'SDAO',
                    'email' => $admin['email'],
                    'status' => 'Waiting',
                ];
                break;
            }
        }

        $proposalId = Str::uuid();

        $proposalData = [
            'proposal_id' => $proposalId,
            'organization_name' => $organizationName,
            'submitted_by' => $userEmail,
            'title' => $validated['title'],
            'objectives' => $validated['objectives'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'],
            'pdf_file_path' => $path,
            'status' => 'Pending',
            'submitted_at' => now()->toDateTimeString(),
            'approval_chain' => $approvalChain,
            'schedule' => $validated['schedule'] ?? [],
        ];

        $this->database->getReference('proposals/' . $proposalId)->set($proposalData);

        return redirect()->route('activities-user.home')->with('success', 'Proposal submitted!');
    }

public function approve(Request $request, $id)
{
    $proposalRef = $this->database->getReference('proposals/' . $id);
    $proposal = $proposalRef->getValue();

    if (!$proposal) {
        abort(404, 'Proposal not found.');
    }

    $sessionUser = session('activities_approver') ?? [];
    $approverRole = $sessionUser['role'] ?? null;
    $approverEmail = $sessionUser['email'] ?? null;

    // ✅ Always build the approver's display name with role
    $approverName = trim(($sessionUser['first_name'] ?? '') . ' ' . ($sessionUser['last_name'] ?? ''));
    if ($approverName) {
        $approverName .= ' (' . $approverRole . ')';
    } else {
        $approverName = $approverRole;
    }

    if (!$approverRole || !$approverEmail) {
        abort(403, 'You must be logged in as an approver.');
    }

    $action = $request->input('action'); // 'approve' or 'reject'
    $comments = trim($request->input('comments'));

    $approvalChain = $proposal['approval_chain'] ?? [];
    $found = false;

    foreach ($approvalChain as $index => &$approval) {
        if (
            strcasecmp($approval['role'], $approverRole) === 0 &&
            $approval['email'] === $approverEmail &&
            strtolower($approval['status']) === 'pending'
        ) {
            $approval['by_name'] = $approverName; // ✅ Always store who did it

            if ($action === 'approve') {
                $approval['status'] = 'Approved';
                $approval['comments'] = $comments ?: 'Approved.';
                $approval['actioned_at'] = now()->toDateTimeString();

                // ✅ Activate next approver
                $nextIndex = $index + 1;
                if (isset($approvalChain[$nextIndex]) && strtolower($approvalChain[$nextIndex]['status']) === 'waiting') {
                    $approvalChain[$nextIndex]['status'] = 'Pending';
                } else {
                    $proposal['status'] = 'Fully Approved';
                }
            } else {
                if (empty($comments)) {
                    return redirect()->back()->withErrors([
                        'comments' => 'Please provide a comment explaining why this proposal is being returned.'
                    ]);
                }

                $approval['status'] = 'Returned';
                $approval['comments'] = $comments;
                $approval['actioned_at'] = now()->toDateTimeString();
                $proposal['status'] = 'Returned';
            }

            $found = true;
            break;
        }
    }

    if (!$found) {
        abort(403, 'You are not authorized to approve/return this proposal.');
    }

    $proposal['approval_chain'] = $approvalChain;
    $proposalRef->update($proposal);

    return redirect()->back()->with('success', "You have {$action}d the proposal.");
}



    public function index(Request $request)
    {
        $user = session('activities_user') ?? [];
        $userEmail = $user['email'] ?? null;

        $account = $this->database->getReference('organization_accounts')
            ->orderByChild('email')
            ->equalTo($userEmail)
            ->getValue();

        $organizationName = '';

        if ($account) {
            foreach ($account as $entry) {
                $organizationName = trim($entry['organization_name'] ?? '');
                break;
            }
        }

        $allProposals = $this->database->getReference('proposals')->getValue();
        $proposals = [];
        $selectedProposal = null;

        if ($allProposals) {
            foreach ($allProposals as $proposal) {
                if (strcasecmp(trim($proposal['organization_name'] ?? ''), $organizationName) === 0) {
                    $proposals[] = $proposal;

                    if ($request->has('id') && $proposal['proposal_id'] === $request->input('id')) {
                        $selectedProposal = $proposal;
                    }
                }
            }
        }

        return view('activities-user.home', compact('proposals', 'selectedProposal'));
    }

    public function show($id)
    {
        $proposal = $this->database->getReference('proposals/' . $id)->getValue();
        if (!$proposal) {
            abort(404);
        }

        return view('activities-user.proposal-details', compact('proposal'));
    }

    public function review($id)
    {
        $proposal = $this->database->getReference('proposals/' . $id)->getValue();
        if (!$proposal) {
            abort(404);
        }

        $user = session('activities_user') ?? session('activities_approver') ?? [];
        $role = $user['role'] ?? 'Unknown';

        return view('activities-approver.proposal-approval', compact('proposal', 'role'));
    }

    public function approverHome()
    {
        $user = session('activities_approver') ?? [];
        $approverEmail = $user['email'] ?? null;

        $allProposals = $this->database->getReference('proposals')->getValue();
        $pendingProposals = [];

        if ($allProposals) {
            foreach ($allProposals as $proposal) {
                if (!isset($proposal['approval_chain'])) {
                    continue;
                }

                foreach ($proposal['approval_chain'] as $approval) {
                    if (strtolower($approval['status']) === 'pending' && $approval['email'] === $approverEmail) {
                        $pendingProposals[] = $proposal;
                        break;
                    }
                }
            }
        }

        return view('activities-approver.home', compact('pendingProposals'));
    }

    public function activitiesAdminHome()
{
    $user = session('activities_admin') ?? [];
    $adminEmail = $user['email'] ?? null;

    if (!$adminEmail) {
        abort(403, 'You must be logged in as Activities Admin.');
    }

    $allProposals = $this->database->getReference('proposals')->getValue();
    $pendingProposals = [];

    if ($allProposals) {
        foreach ($allProposals as $proposal) {
            foreach ($proposal['approval_chain'] ?? [] as $approval) {
                if (
                    strtolower($approval['status']) === 'pending' &&
                    strtolower($approval['role']) === 'sdao' &&
                    $approval['email'] === $adminEmail
                ) {
                    $pendingProposals[] = $proposal;
                    break;
                }
            }
        }
    }

    return view('activities-admin.home', compact('pendingProposals'));
}


public function reply(Request $request, $id)
{
    $validated = $request->validate([
        'reply' => 'required|string|max:2000',
    ]);

    $proposalRef = $this->database->getReference('proposals/' . $id);
    $proposal = $proposalRef->getValue();

    if (!$proposal) {
        abort(404, 'Proposal not found.');
    }

    $sessionUser = session('activities_user') ?? session('activities_approver') ?? [];
    $email = $sessionUser['email'] ?? null;

    if (!$email) {
        abort(403, 'You must be logged in.');
    }

    // ✅ Figure out who they are
    $isOrgUser = session()->has('activities_user');
    $name = $isOrgUser
        ? trim(($sessionUser['first_name'] ?? '') . ' ' . ($sessionUser['last_name'] ?? ''))
        : trim(($sessionUser['first_name'] ?? '') . ' ' . ($sessionUser['last_name'] ?? '') . ' (' . $sessionUser['role'] . ')');


    if ($isOrgUser) {
        // ✅ RSO reply goes in top-level discussion
        if (!isset($proposal['discussion'])) {
            $proposal['discussion'] = [];
        }

        $proposal['discussion'][] = [
            'reply' => $validated['reply'],
            'by' => $name,
            'email' => $email,
            'replied_at' => now()->toDateTimeString(),
        ];

    } else {
        // ✅ Approver reply goes in the matched approval block
        $approvalChain = $proposal['approval_chain'] ?? [];
        $found = false;

        foreach ($approvalChain as &$approval) {
            if ($approval['email'] === $email) {
                if (!isset($approval['replies'])) {
                    $approval['replies'] = [];
                }

                $approval['replies'][] = [
                    'reply' => $validated['reply'],
                    'by' => $name,
                    'email' => $email,
                    'replied_at' => now()->toDateTimeString(),
                ];

                $found = true;
                break;
            }
        }

        if (!$found) {
            abort(403, 'Could not match your approver account to this proposal.');
        }

        $proposal['approval_chain'] = $approvalChain;
    }

    $proposalRef->update($proposal);

    return redirect()->back()->with('success', 'Reply submitted!');
}


public function resubmit($id)
{
    $proposalRef = $this->database->getReference('proposals/' . $id);
    $proposal = $proposalRef->getValue();

    if (!$proposal) {
        abort(404, 'Proposal not found.');
    }

    // ✅ Must be logged in as activities_user
    $sessionUser = session('activities_user') ?? [];
    $userEmail = $sessionUser['email'] ?? null;

    if (!$userEmail || $userEmail !== $proposal['submitted_by']) {
        abort(403, 'You are not authorized to resubmit this proposal.');
    }

    // ✅ Reset chain: Adviser -> Pending, others -> Waiting
    $approvalChain = $proposal['approval_chain'] ?? [];
    foreach ($approvalChain as $index => &$approval) {
        if ($index === 0) {
            $approval['status'] = 'Pending';
            unset($approval['comments']);
            unset($approval['actioned_at']);
        } else {
            $approval['status'] = 'Waiting';
            unset($approval['comments']);
            unset($approval['actioned_at']);
        }
    }

    $proposal['approval_chain'] = $approvalChain;
    $proposal['status'] = 'Pending'; // Overall status
    $proposalRef->update($proposal);

    return redirect()->route('activities-user.home')->with('success', 'Proposal resubmitted!');
}

public function edit($id)
{
    $proposal = $this->database->getReference('proposals/' . $id)->getValue();

    if (!$proposal) {
        abort(404, 'Proposal not found.');
    }

    $sessionUser = session('activities_user') ?? [];
    $userEmail = $sessionUser['email'] ?? null;

    if (!$userEmail || $userEmail !== $proposal['submitted_by']) {
        abort(403, 'You are not authorized to edit this proposal.');
    }

    if (strtolower($proposal['status']) !== 'returned') {
        abort(403, 'Only returned proposals can be edited.');
    }

    return view('activities-user.edit-proposal', compact('proposal'));
}

public function update(Request $request, $id)
{
    $proposalRef = $this->database->getReference('proposals/' . $id);
    $proposal = $proposalRef->getValue();

    if (!$proposal) {
        abort(404, 'Proposal not found.');
    }

    $sessionUser = session('activities_user') ?? [];
    $userEmail = $sessionUser['email'] ?? null;

    if (!$userEmail || $userEmail !== $proposal['submitted_by']) {
        abort(403, 'You are not authorized to update this proposal.');
    }

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'objectives' => 'required|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'description' => 'required|string',
        'pdf_file' => 'nullable|file|mimes:pdf|max:102400',
    ]);

    // ✅ Replace PDF if uploaded
    if ($request->hasFile('pdf_file')) {
        $path = $request->file('pdf_file')->store('proposals', 'public');
        $proposal['pdf_file_path'] = $path;
    }

    $proposal['title'] = $validated['title'];
    $proposal['objectives'] = $validated['objectives'];
    $proposal['start_date'] = $validated['start_date'];
    $proposal['end_date'] = $validated['end_date'];
    $proposal['description'] = $validated['description'];

    $proposalRef->update($proposal);

    return redirect()->route('activities-user.home')->with('success', 'Proposal updated!');
}

}
