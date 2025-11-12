<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\AccountsScholarsController;
use App\Http\Controllers\ScholarJoinController;



Route::prefix('scholars')->name('scholars.')->group(function () {
    // Scholar dashboard and pages



Route::get('/dashboard', [ScholarJoinController::class, 'joinedOpportunities'])
    ->name('dashboard');



    Route::get('/volunteer-opportunities', [ScholarJoinController::class, 'scholarOpportunities'])
        ->name('volunteer-opportunities');

    Route::post('/opportunities/join/{id}', [ScholarJoinController::class, 'join'])
        ->name('opportunities.join');



 Route::get('/invites', [OpportunitiesController::class, 'scholarInvites'])->name('invites');
    Route::post('/invites/respond/{id}', [OpportunitiesController::class, 'respondToInvite'])->name('invites.respond');


Route::post('/on-the-spot-volunteer', [OpportunitiesController::class, 'onTheSpotVolunteer'])
    ->name('ontheSpotVolunteer');


Route::get('/profile', [AccountsScholarsController::class, 'scholarProfile'])
    ->name('profile');

});
