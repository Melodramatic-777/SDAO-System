<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequestVolunteersController extends Controller
{
    public function myRequests()
    {
        // Retrieve requests from DB
        $requests = []; // TODO: Replace with actual query
        return view('volunteers-offices.my-requests', compact('requests'));
    }

    public function create()
    {
        return view('volunteers-offices.request-form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'time_start' => 'required',
            'time_end' => 'required',
            'volunteers_needed' => 'required|integer|min:1',
            'food_provided' => 'required|boolean',
            'task_details' => 'required|string',
            'skills_needed' => 'nullable|string',
        ]);

        // Save to DB here...

        return redirect()->route('volunteers-offices.my-requests')
                         ->with('success', 'Volunteer request submitted successfully.');
    }

    public function show($id)
    {
        $request = null; // TODO: Replace with actual query
        return view('volunteers-offices.show', compact('request'));
    }
}
