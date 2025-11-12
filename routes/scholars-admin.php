<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsScholarsController;
use App\Http\Controllers\ScholarshipTypesController;
use App\Http\Controllers\ScholarRecordsController;
use App\Http\Controllers\ScholarsDashboardController;

Route::prefix('scholars-admin')->group(function () {

    // ✅ Dashboard
    Route::get('/dashboard',  [ScholarsDashboardController::class, 'dashboard'])
        ->name('scholars-admin.dashboard');

    // ✅ Scholarship Types (CRUD)
    Route::get('/scholarships', [ScholarshipTypesController::class, 'index'])
        ->name('scholars-admin.scholarships');
    Route::post('/scholarships', [ScholarshipTypesController::class, 'store'])
        ->name('scholars-admin.scholarships.store');
    Route::put('/scholarships/{id}', [ScholarshipTypesController::class, 'update'])
        ->name('scholars-admin.scholarships.update');
    Route::delete('/scholarships/{id}', [ScholarshipTypesController::class, 'destroy'])
        ->name('scholars-admin.scholarships.destroy');

    // ✅ Scholars Accounts
    Route::get('/scholars_accounts', [AccountsScholarsController::class, 'showScholarsPage'])
        ->name('scholars-admin.scholars_accounts');
    Route::post('/import-scholars', [AccountsScholarsController::class, 'importScholars'])
        ->name('scholars-admin.import-scholars');

    // ✅ Scholars GWA Records
    Route::get('/scholars', [ScholarshipTypesController::class, 'indexRecord'])
        ->name('scholars-admin.scholars');
    Route::post('/scholars/gwa', [ScholarshipTypesController::class, 'store'])
        ->name('scholars-admin.gwa.store');
    Route::put('/scholars/gwa/{id}', [ScholarshipTypesController::class, 'update'])
        ->name('scholars-admin.gwa.update');
    Route::delete('/scholars/gwa/{id}', [ScholarshipTypesController::class, 'destroy'])
        ->name('scholars-admin.gwa.destroy');
Route::post('/save-all-gwa', [ScholarshipTypesController::class, 'saveAllGwa'])
    ->name('scholars-admin.saveAllGwa');



Route::post('/scholars/evaluate-status', [ScholarRecordsController::class, 'evaluateStatus'])
    ->name('scholars-admin.evaluate-status');

Route::post('/scholars/update-gwa', [ScholarRecordsController::class, 'updateGwa'])
    ->name('scholars-admin.update-gwa');




});
