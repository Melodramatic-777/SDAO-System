<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\AccountsScholarsController;
use App\Http\Controllers\ScholarJoinController;
use App\Http\Controllers\OnTheSpotVolunteerController;
use App\Http\Controllers\AdminController;


Route::prefix('scholars')->name('scholars.')->group(function () {

    Route::post('/logout', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'destroy'])
        ->name('logout'); // <- now matches scholars.logout

    // Scholar login
    Route::get('/login', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'create'])
        ->name('login');

            Route::post('/profile/change-password', [AdminController::class, 'changeScholarPassword'])
    ->name('profile.change-password');


Route::post('volunteer-opportunities/join-task', [ScholarJoinController::class, 'joinTask'])
    ->name('joinTask'); 


    Route::controller(ScholarJoinController::class)->group(function () {
        Route::get('dashboard', 'joinedOpportunities')->name('dashboard');
        Route::get('volunteer-opportunities', 'scholarOpportunities')->name('volunteer-opportunities');
        Route::post('opportunities/join/{id}', 'join')->name('opportunities.join');
        
        Route::get('opportunities/intermittent-dates/{id}', 'getIntermittentDates')
            ->name('opportunities.intermittent-dates');

        // 🔥 NEW – Join multiple intermittent dates
        Route::post('opportunities/join-intermittent/{id}', 'joinIntermittent')
            ->name('opportunities.join-intermittent');
    });

Route::controller(OpportunitiesController::class)->group(function () {

    // View invites
    Route::get('invites', 'scholarInvites')->name('invites');

    // Old respond route
    Route::post('invites/respond/{id}', 'respondToInvite')->name('invites.respond');

    // NEW: Accept invite
    Route::post('invites/accept', 'acceptInvite')->name('invites.accept');

    // NEW: Decline invite
    Route::post('invites/decline', 'declineInvite')->name('invites.decline');
});

    Route::controller(AccountsScholarsController::class)->group(function () {
        Route::get('profile', 'scholarProfile')->name('profile');
    });

    Route::controller(OnTheSpotVolunteerController::class)->group(function () {
        Route::post('on-the-spot', 'onTheSpotVolunteer')->name('ontheSpotVolunteer');
            Route::post('on-the-spot/cancel/{id}', 'cancelOTSV')->name('ontheSpotVolunteer.cancel'); // <--- new

    });
});
