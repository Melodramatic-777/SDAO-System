<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Kreait\Firebase\Factory;

class OpportunitiesDisplayOfficeController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

public function requestVolunteer()
{
    $user = session('volunteers_office') ?? [];
    $userEmail = $user['email'] ?? null;

    if (!$userEmail) {
        abort(403, 'No logged in user email found.');
    }

    // Get ALL opportunities by this office for counts
    $allOpportunities = $this->database->getReference('opportunities')
        ->orderByChild('submitted_by')
        ->equalTo($userEmail)
        ->getValue() ?? [];

    // Ensure numeric array
    $allOpportunities = collect(array_values($allOpportunities));

    // Sort newest first
    $allOpportunities = $allOpportunities->sortByDesc(function ($op) {
        return strtotime($op['submitted_at'] ?? 0);
    })->values(); // reset keys

    // ---- PAGINATION ----
    $currentPage = request()->get('page', 1);
    $perPage = 5;

    $pagedOpportunities = new LengthAwarePaginator(
        $allOpportunities->forPage($currentPage, $perPage),
        $allOpportunities->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url()]
    );

    return view('volunteers-offices.request-volunteer', [
        'opportunities' => $pagedOpportunities,   // for displaying paginated list
        'allOpportunities' => $allOpportunities   // ✅ for correct status counts
    ]);
}

public function dashboardOffice()
{
    $user = session('volunteers_office') ?? [];
    $userEmail = $user['email'] ?? null;

    if (!$userEmail) {
        abort(403, 'No logged in user email found.');
    }

    $allOpportunities = $this->database->getReference('opportunities')
        ->orderByChild('submitted_by')
        ->equalTo($userEmail)
        ->getValue() ?? [];

    $allOpportunities = collect(array_values($allOpportunities));

    // Sort newest first
    $allOpportunities = $allOpportunities->sortByDesc(function ($op) {
        return strtotime($op['submitted_at'] ?? 0);
    })->values();

    $currentPage = request()->get('page', 1);
    $perPage = 5;

    $pagedOpportunities = new LengthAwarePaginator(
        $allOpportunities->forPage($currentPage, $perPage),
        $allOpportunities->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url()]
    );

    return view('volunteers-offices.dashboard-office', [ // ✅ load dashboard view
        'opportunities' => $pagedOpportunities,
        'allOpportunities' => $allOpportunities
    ]);
}

}
