<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\OpportunitiesDisplayOfficeController;

Route::prefix('volunteers-offices')->group(function () {

    Route::get('/volunteers-office/home', [OpportunitiesDisplayOfficeController::class, 'home'])
    ->name('volunteers-offices.home');



    Route::get('/edit-opportunity/{id}', [OpportunitiesController::class, 'edit'])
    ->name('volunteers-offices.edit-opportunity');

// ✅ Update Opportunity (submit edited form)
Route::post('/update-opportunity/{id}', [OpportunitiesController::class, 'update'])
    ->name('volunteers-offices.update-opportunity');

    Route::post('/cancel-opportunity/{id}', [OpportunitiesDisplayOfficeController::class, 'cancelOpportunity'])
        ->name('volunteers-offices.cancel-opportunity');





    // Invite a volunteer (static form)
    Route::get('/invite-volunteer-office', [OpportunitiesController::class, 'showScholarsOffice'])
        ->name('volunteers-offices.invite-volunteer-office');


    Route::get('/volunteers-offices/invite-volunteer', [OpportunitiesController::class, 'showScholarsOffice'])
    ->name('volunteers-offices.invite-volunteer-office');

    Route::post('/invite-scholar', [OpportunitiesController::class, 'inviteScholar'])
        ->name('volunteers-offices.inviteScholar');





    // Show submitted requests list
Route::get('/volunteers-offices/history', [OpportunitiesController::class, 'historyOffice'])
    ->name('volunteers-offices.history-office');






    // Volunteer opportunity form
    Route::get('/create-opportunity', [OpportunitiesController::class, 'createOpportunity'])
        ->name('volunteers-offices.create-opportunity');

    // Save opportunity to Firebase
    Route::post('/store-opportunity', [OpportunitiesController::class, 'store'])
        ->name('volunteers-offices.store-opportunity');
});
