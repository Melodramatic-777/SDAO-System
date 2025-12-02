<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Pagination\LengthAwarePaginator;

class OnTheSpotVolunteerController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

public function onTheSpotVolunteer(Request $request) {
    $validated = $request->validate([
        'office' => 'required|string',
        'available_date' => 'required|date',
        'available_start_time' => 'required|date_format:H:i',
        'available_end_time' => 'required|date_format:H:i|after:available_start_time',
        'reasons' => 'array',
        'reasons.*' => 'string',
        'other_reason' => 'nullable|string|max:255',
    ]);

    $studentId = session('scholar_accounts.student_no');
    $studentName = session('scholar_accounts.first_name') . ' ' . session('scholar_accounts.last_name');

    $this->database->getReference('on_the_spot_volunteers')
        ->push([
            'student_id' => $studentId,
            'student_name' => $studentName,
            'office' => $validated['office'],  // << ADDED HERE
            'available_date' => $validated['available_date'],
            'available_start_time' => $validated['available_start_time'],
            'available_end_time' => $validated['available_end_time'],
            'reasons' => $validated['reasons'] ?? [],
            'other_reason' => $validated['other_reason'] ?? null,
            'status' => 'available',
            'created_at' => now()->toDateTimeString()
        ]);

    return redirect()->back()->with('success', 'You are now marked as available for on-the-spot volunteering.');
}




}