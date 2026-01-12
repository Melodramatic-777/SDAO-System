<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class FingerprintBridgeController extends Controller
{
    public function enroll()
    {
        // Get base URL from config/services.php -> 'esp32'
        $esp32 = rtrim(config('services.esp32.base_url'), '/');


        // Call ESP32 /enroll endpoint
        $resp = Http::timeout(60)->post($esp32 . '/enroll');


        // Always return ESP32's JSON (success OR already_registered)
        // ESP32 uses status codes for HTTP, but business logic in JSON
        return response()->json($resp->json());
    }
}



