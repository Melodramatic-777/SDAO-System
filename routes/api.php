<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FingerprintApiController;
use App\Http\Controllers\FingerprintBridgeController;
use App\Http\Controllers\FingerprintLogController;


Route::post('/fingerprint/enroll', [FingerprintBridgeController::class, 'enroll']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/fingerprint/complete', [FingerprintApiController::class, 'complete']);



Route::post('/fingerprint/scan', [FingerprintLogController::class, 'scan']);





