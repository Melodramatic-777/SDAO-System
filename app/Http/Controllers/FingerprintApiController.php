<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Kreait\Firebase\Factory;


class FingerprintApiController extends Controller
{
    protected $database;


    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');


        $this->database = $factory->createDatabase();
    }


    public function complete(Request $request)
{
    $data = $request->validate([
        // Scholar ID used as Firebase key
        'firebase_key' => ['required', 'string', 'regex:/^[^.$#\[\]\/]+$/'],
        'academic_year' => ['required', 'string'],
        'term' => ['required', 'string'],
        'scholarship_type' => ['required', 'string'],
        'fingerprint_slot_id' => ['required', 'integer', 'min:1'],
    ]);


    // dev/ prefix only when running locally


    $path = "scholars/{$data['academic_year']}/{$data['term']}/{$data['scholarship_type']}/{$data['firebase_key']}";


    // Always overwrite fingerprint info (re-submits allowed)
    $this->database->getReference($path)->update([
        'fingerprint_slot_id'    => (int) $data['fingerprint_slot_id'],
        'fingerprint_complete'   => true,
        'fingerprint_completed_at' => now()->toIso8601String(),
    ]);


    return response()->json(['ok' => true]);
}


}









