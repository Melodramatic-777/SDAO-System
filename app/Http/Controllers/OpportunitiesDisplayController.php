<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Kreait\Firebase\Factory;

class OpportunitiesDisplayController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
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

        // --- PAGINATION ---
        $currentPage = request()->get('page', 1);
        $perPage = 5;

        $opportunitiesCollection = collect($opportunities);

        $pagedOpportunities = new LengthAwarePaginator(
            $opportunitiesCollection->forPage($currentPage, $perPage),
            $opportunitiesCollection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        // Group opportunities by submitted_by_role (office name)
        $grouped = [];
        foreach ($pagedOpportunities as $opportunity) {
            $office = $opportunity['submitted_by_role'] ?? 'Unknown Office';
            $grouped[$office][] = $opportunity;
        }

        ksort($grouped); // optional alphabetical sorting by office

return view('volunteers-admin.opportunities', [
    'groupedOpportunities' => $grouped,
    'opportunities' => $pagedOpportunities->items(), // paginated items for display
    'pagination' => $pagedOpportunities, // keep paginator for page links
    'allOpportunities' => $opportunities // ✅ full list for counts
]);



    }
    
}

