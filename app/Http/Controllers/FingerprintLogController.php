<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class FingerprintLogController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    /**
     * 
     * 
     * Scan fingerprint for Time In / Time Out
     */


       public function index()
    {
        return view('scholars-admin.fingerprint-log');
    }
    
    public function scan(Request $request)
    {
        $request->validate([
            'action' => 'required|in:time_in,time_out'
        ]);

        $action = $request->action;

        // Call ESP32 endpoint to get matched fingerprint slot
        $esp32Url = rtrim(config('services.esp32.base_url'), '/') . '/verify';
        $resp = \Illuminate\Support\Facades\Http::post($esp32Url);

        $json = $resp->json();

        if (!($json['found'] ?? false)) {
            return response()->json(['ok' => false, 'error' => 'Fingerprint not recognized']);
        }

        $slotId = (int) $json['id'];
        $now = now();

        // Insert into fingerprint_log
        $logData = [
            'slot_id' => $slotId,
            'time_in' => $action === 'time_in' ? $now->toTimeString() : null,
            'time_out' => $action === 'time_out' ? $now->toTimeString() : null,
            'time_in_date' => $action === 'time_in' ? $now->toDateString() : null,
            'time_out_date' => $action === 'time_out' ? $now->toDateString() : null,
        ];

        $this->database->getReference('fingerprint_log')->push($logData);

        return response()->json([
            'ok' => true,
            'slot_id' => $slotId,
            'action' => $action,
            'time' => $now->toTimeString(),
            'datetime' => $now->format('l, F j, Y h:i:s A')
        ]);
    }
}
