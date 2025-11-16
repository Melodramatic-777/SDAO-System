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
Route::get('/scholars', [ScholarRecordsController::class, 'indexRecord'])
    ->name('scholars-admin.scholars');

Route::post('/scholars/save-gwa', [ScholarRecordsController::class, 'saveGwa'])
    ->name('scholars-admin.scholars.save');




});
