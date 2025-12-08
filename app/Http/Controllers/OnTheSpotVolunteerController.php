<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

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


    /*---------------------------------------------------------
     | Scholar Submits On-The-Spot Volunteer Availability
     *---------------------------------------------------------*/
    public function onTheSpotVolunteer(Request $request)
    {
        $validated = $request->validate([
            'office' => 'required|string',
            'available_date' => 'required|date',
            'available_start_time' => 'required|date_format:H:i',
            'available_end_time' => 'required|date_format:H:i|after:available_start_time',
            'reasons' => 'array',
            'reasons.*' => 'string',
            'other_reason' => 'nullable|string|max:255',
            'willing_to_transfer' => 'required|in:yes,no',
        ]);

        $studentId = session('scholar_accounts.student_no');
        $studentName = session('scholar_accounts.last_name') . ', ' . session('scholar_accounts.first_name');

        $this->database->getReference('on_the_spot_volunteers')
            ->push([
                'student_id' => $studentId,
                'student_name' => $studentName,
                'office' => $validated['office'],   
                'available_date' => $validated['available_date'],
                'available_start_time' => $validated['available_start_time'],
                'available_end_time' => $validated['available_end_time'],
                'reasons' => $validated['reasons'] ?? [],
                'other_reason' => $validated['other_reason'] ?? null,
                'willing_to_transfer' => $validated['willing_to_transfer'],
                'status' => 'available',
                'created_at' => now()->toDateTimeString(),
            ]);

        return redirect()->back()->with('success', 'You are now marked as available for on-the-spot volunteering.');
    }


    /*---------------------------------------------------------
     | Fetch ALL OTSV (ADMIN VIEW)
     *---------------------------------------------------------*/
    public function fetchOTSV()
    {
        $data = $this->database->getReference('on_the_spot_volunteers')->getValue();

        $list = [];
        if ($data) {
            foreach ($data as $id => $item) {
                $list[] = array_merge($item, ['id' => $id]);
            }
        }

        // sort by creation date (latest first)
        usort($list, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return $list;
    }


    /*---------------------------------------------------------
     | Fetch ONLY my own entries (SCHOLAR VIEW)
     *---------------------------------------------------------*/
    public function fetchMyOTSV()
    {
        $studentId = session('scholar_accounts.student_no');

        $data = $this->database->getReference('on_the_spot_volunteers')->getValue();

        $list = [];
        if ($data) {
            foreach ($data as $id => $item) {
                if (($item['student_id'] ?? null) === $studentId) {
                    $list[] = array_merge($item, ['id' => $id]);
                }
            }
        }

        // sort my entries too
        usort($list, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return $list;
    }


    /*---------------------------------------------------------
     | Accept OTSV (Admin)
     *---------------------------------------------------------*/
    public function acceptOTSV($id)
    {
        $this->database->getReference("on_the_spot_volunteers/$id")
            ->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Volunteer accepted.');
    }


    /*---------------------------------------------------------
     | Decline OTSV (Admin)
     *---------------------------------------------------------*/
    public function declineOTSV($id)
    {
        $this->database->getReference("on_the_spot_volunteers/$id")
            ->update(['status' => 'declined']);

        return redirect()->back()->with('success', 'Volunteer declined.');
    }


    /*---------------------------------------------------------
 | Cancel OTSV (Scholar)
 *---------------------------------------------------------*/
/*---------------------------------------------------------
 | Cancel OTSV (Scholar) – Move to Archive
 *---------------------------------------------------------*/
public function cancelOTSV($id)
{
    $studentId = session('scholar_accounts.student_no');

    $ref = $this->database->getReference("on_the_spot_volunteers/$id")->getValue();

    if ($ref && ($ref['student_id'] ?? null) === $studentId) {
        // Add a cancelled_at timestamp
        $ref['status'] = 'cancelled';
        $ref['cancelled_at'] = now()->toDateTimeString();

        // Push to archive
        $this->database->getReference("on_the_spot_volunteers_archive/$id")->set($ref);

        // Remove from the active list
        $this->database->getReference("on_the_spot_volunteers/$id")->remove();

        return redirect()->back()->with('success', 'Your on-the-spot volunteer entry has been cancelled and archived.');
    }

    return redirect()->back()->with('error', 'Unable to cancel this entry.');
}



}
