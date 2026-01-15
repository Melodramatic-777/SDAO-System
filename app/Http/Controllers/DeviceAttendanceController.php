<?php


namespace App\Http\Controllers;


use Kreait\Firebase\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DeviceAttendanceController extends Controller
{


    protected $database;


    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');


        $this->database = $factory->createDatabase();
    }


    public function store(Request $request)
    {
        $student_id = $request->input('student_id');


        try {
            DB::table('scholars_records')->insert([
                'user_id' => $student_id,
                // scholars_records also needs scholarship_type_id (NOT NULL)
                // so this insert will still fail unless you add it or change table
            ]);


            $message = 'Inserted successfully';
        } catch (\Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }


        return response()->json([
            'ok' => true,
            'message' => $message,
            'received' => $request->all(),
        ]);
    }
}



