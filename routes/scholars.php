<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\AccountsScholarsController;
use App\Http\Controllers\ScholarJoinController;
use App\Http\Controllers\OnTheSpotVolunteerController;


Route::prefix('scholars')->name('scholars.')->group(function () {

    Route::controller(ScholarJoinController::class)->group(function () {
        Route::get('dashboard', 'joinedOpportunities')->name('dashboard');
        Route::get('volunteer-opportunities', 'scholarOpportunities')->name('volunteer-opportunities');
        Route::post('opportunities/join/{id}', 'join')->name('opportunities.join');
    });

    Route::controller(OpportunitiesController::class)->group(function () {
        Route::get('invites', 'scholarInvites')->name('invites');
        Route::post('invites/respond/{id}', 'respondToInvite')->name('invites.respond');
    });

    Route::controller(AccountsScholarsController::class)->group(function () {
        Route::get('profile', 'scholarProfile')->name('profile');
    });

    Route::controller(OnTheSpotVolunteerController::class)->group(function () {
        Route::post('on-the-spot', 'onTheSpotVolunteer')->name('ontheSpotVolunteer');
    });
});
