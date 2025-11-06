<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpportunitiesController;

Route::prefix('volunteers-offices')->group(function () {

    Route::get('/volunteers-office/home', [OpportunitiesController::class, 'home'])
    ->name('volunteers-offices.home');



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
