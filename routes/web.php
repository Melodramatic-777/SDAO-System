<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FingerprintLogController;




Route::get('/', fn() => view('welcome'));








require __DIR__.'/auth.php';
require __DIR__.'/admin.php';          
require __DIR__.'/superadmin.php';    
require __DIR__.'/scholars-admin.php';  
require __DIR__.'/volunteers-admin.php';
require __DIR__.'/scholars.php';      
require __DIR__.'/volunteers-offices.php';  



Route::get('/fingerprint/logs', [FingerprintLogController::class, 'showLogs'])->name('fingerprint.logs');

use Illuminate\Http\Request;
use App\Http\Controllers\DeviceAttendanceController;


Route::post('/attendance-from-device', [\App\Http\Controllers\DeviceAttendanceController::class, 'store']);







