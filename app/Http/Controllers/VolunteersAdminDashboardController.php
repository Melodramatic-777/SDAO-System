<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;

class VolunteersAdminDashboardController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    /*---------------------------------------------------------
     | Fetch All On-The-Spot Volunteers for Admin Dashboard
     *---------------------------------------------------------*/
    public function fetchOTSV()
    {
        $data = $this->database->getReference('on_the_spot_volunteers')->getValue();
        $list = [];

        if ($data) {
            foreach ($data as $id => $item) {
                // Only include volunteers who are still available
                if (($item['status'] ?? '') !== 'accepted' && ($item['status'] ?? '') !== 'cancelled') {
                    $list[] = array_merge($item, ['id' => $id]);
                }
            }
        }

        // Sort newest first
        usort($list, fn($a, $b) => strtotime($b['created_at'] ?? 0) <=> strtotime($a['created_at'] ?? 0));

        return $list;
    }

    /*---------------------------------------------------------
     | Admin Dashboard View
     *---------------------------------------------------------*/
    public function dashboard()
    {
        // Fetch other data for admin (opportunities, stats, etc.)
        // ...

        // Fetch OTSV
        $otsvList = $this->fetchOTSV();

        return view('volunteers-admin.dashboard', compact('otsvList'));
    }
}
