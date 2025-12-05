<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsScholarsController;
use App\Http\Controllers\ScholarshipTypesController;
use App\Http\Controllers\ScholarRecordsController;
use App\Http\Controllers\ScholarsDashboardController;

Route::prefix('scholars-admin')->group(function () {

Route::post('/admin/logout', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'destroy'])
    ->name('scholars-admin.logout');

Route::get('/admin/login', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'create']);

    
    Route::controller(ScholarsDashboardController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('scholars-admin.dashboard');
    });

    Route::controller(ScholarshipTypesController::class)->group(function () {
        Route::get('/scholarships', 'index')->name('scholars-admin.scholarships');
        Route::post('/scholarships', 'store')->name('scholars-admin.scholarships.store');
        Route::put('/scholarships/{id}', 'update')->name('scholars-admin.scholarships.update');
        Route::delete('/scholarships/{id}', 'destroy')->name('scholars-admin.scholarships.destroy');
    });

    Route::controller(AccountsScholarsController::class)->group(function () {
        Route::get('/scholars_accounts', 'showScholarsPage')->name('scholars-admin.scholars_accounts');
        Route::post('/import-scholars', 'importScholars')->name('scholars-admin.import-scholars');
    });

    Route::controller(ScholarRecordsController::class)->group(function () {
        Route::get('/scholars', 'indexRecord')->name('scholars-admin.scholars');
        Route::post('/scholars/save-gwa', 'saveGwa')->name('scholars-admin.scholars.save');
    });


    Route::get('/download-template', function () {
return response()->download(
    public_path('templates/Import-Scholars_Template.xlsx'),
    'Import-Scholars_Template.xlsx'
);
});



});
