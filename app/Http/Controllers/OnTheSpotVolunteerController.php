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
                    'transfer_unlocked' => false,   // 🔥 REQUIRED

                'created_at' => now()->toDateTimeString(),
            ]);

        return redirect()->back()->with('success', 'You are now marked as available for on-the-spot volunteering.');
    }

    /*---------------------------------------------------------
     | Fetch OTSV for Office Dashboard
     *---------------------------------------------------------*/
/*---------------------------------------------------------
 | Fetch OTSV for Office/Admin Dashboard
 *---------------------------------------------------------*/
public function fetchOTSVForOffice()
{
    $data = $this->database->getReference('on_the_spot_volunteers')->getValue();
    $list = [];

    $currentOffice = session('volunteers_office.office_name') ?? null;
    $isAdmin = session()->has('volunteers_admin');

    if (!$data || (!$currentOffice && !$isAdmin)) {
        return [];
    }

    foreach ($data as $id => $item) {

        $status = $item['status'] ?? 'available';
        $willing = $item['willing_to_transfer'] ?? 'no';
        $transferUnlocked = isset($item['transfer_unlocked']) && $item['transfer_unlocked'] === true;
        $originalOffice = $item['office'] ?? null;

        // Skip accepted or cancelled
        if (in_array($status, ['accepted', 'cancelled'])) {
            continue;
        }

        /**
         * ===============================
         * WILLING TO TRANSFER = YES
         * ===============================
         */
        if ($willing === 'yes') {

            // BEFORE DECLINE → only original office sees it
            if (!$transferUnlocked && $originalOffice === $currentOffice) {
                $list[] = array_merge($item, ['id' => $id]);
                continue;
            }

            // AFTER DECLINE → other offices + admin
            if ($transferUnlocked) {
                if ($isAdmin || $originalOffice !== $currentOffice) {
                    $list[] = array_merge($item, ['id' => $id]);
                }
                continue;
            }

            continue;
        }

        /**
         * ===============================
         * WILLING TO TRANSFER = NO
         * ===============================
         */
        if ($willing === 'no') {

            // Show ONLY to the originally chosen office (including SDA Office)
            if ($originalOffice === $currentOffice) {
                $list[] = array_merge($item, ['id' => $id]);
            }

            // ❌ Do NOT show to admin or other offices
            continue;
        }
    }

    // Sort newest first
    usort($list, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

    return $list;
}

    /*---------------------------------------------------------
     | Accept OTSV (Office)
     *---------------------------------------------------------*/
    public function acceptOTSV($id)
    {
        $approver = $this->getApprover();

        if (!$approver) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $this->database->getReference("on_the_spot_volunteers/$id")->update([
            'status' => 'accepted',
            'action_by' => $approver,
            'action_at' => now()->toDateTimeString(),
        ]);

        return redirect()->back()->with('success', 'Volunteer accepted.');
    }

    /*---------------------------------------------------------
     | Decline OTSV (Office)
     *---------------------------------------------------------*/
 public function declineOTSV($id)
{
    $approver = $this->getApprover();
    if (!$approver) {
        return redirect()->back()->with('error', 'Unauthorized action.');
    }

    $ref = $this->database->getReference("on_the_spot_volunteers/$id")->getValue();
    if (!$ref) {
        return redirect()->back()->with('error', 'Record not found.');
    }

    $update = [
        'declined_by' => $approver,
        'declined_at' => now()->toDateTimeString(),
    ];

    /**
     * WILLING TO TRANSFER = YES
     * → unlock transfer, make available again
     */
    if (($ref['willing_to_transfer'] ?? 'no') === 'yes') {
        $update['transfer_unlocked'] = true;   // ✅ unlock
        $update['status'] = 'available';
    }
    /**
     * WILLING TO TRANSFER = NO
     * → lock transfer forever, permanent decline
     */
    else {
        $update['transfer_unlocked'] = false;  // ✅ explicitly locked
        $update['status'] = 'declined';
    }

    $this->database
        ->getReference("on_the_spot_volunteers/$id")
        ->update($update);

    return redirect()->back()->with('success', 'Volunteer declined.');
}


    /*---------------------------------------------------------
     | Cancel OTSV (Scholar)
     *---------------------------------------------------------*/
    public function cancelOTSV($id)
    {
        $studentId = session('scholar_accounts.student_no');
        $ref = $this->database->getReference("on_the_spot_volunteers/$id")->getValue();

        if ($ref && ($ref['student_id'] ?? null) === $studentId) {
            $ref['status'] = 'cancelled';
            $ref['cancelled_at'] = now()->toDateTimeString();

            $this->database->getReference("on_the_spot_volunteers_archive/$id")->set($ref);
            $this->database->getReference("on_the_spot_volunteers/$id")->remove();

            return redirect()->back()->with('success', 'Your on-the-spot volunteer entry has been cancelled and archived.');
        }

        return redirect()->back()->with('error', 'Unable to cancel this entry.');
    }

    /*---------------------------------------------------------
     | Get Approver (Office or Admin)
     *---------------------------------------------------------*/
    private function getApprover()
    {
        if (session()->has('volunteers_admin')) {
            $admin = session('volunteers_admin');
            return [
                'id' => $admin['id'] ?? null,
                'name' => ($admin['last_name'] ?? '') . ', ' . ($admin['first_name'] ?? ''),
                'role' => 'SDA Assistant',
                'office' => null,
            ];
        }

        if (session()->has('volunteers_office')) {
            $office = session('volunteers_office');
            return [
                'id' => $office['id'] ?? null,
                'name' => $office['office_name'] ?? $office['email'],
                'role' => 'Office',
                'office' => $office['office_name'] ?? null,
            ];
        }

        return null;
    }



}
