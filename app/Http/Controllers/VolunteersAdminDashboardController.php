<?php

namespace App\Http\Controllers;

use App\Http\Controllers\OnTheSpotVolunteerController;

class VolunteersAdminDashboardController extends Controller
{
    public function dashboard(OnTheSpotVolunteerController $otsv)
    {
        $otsvList = $otsv->fetchOTSV();

        return view('volunteers-admin.dashboard', compact('otsvList'));
    }
}
