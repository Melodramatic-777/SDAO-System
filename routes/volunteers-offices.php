<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\OpportunitiesDisplayOfficeController;

Route::prefix('volunteers-offices')->group(function () {


    Route::controller(OpportunitiesDisplayOfficeController::class)->group(function () {
        Route::get('/volunteers-office/dashboard-office', 'dashboardOffice')
            ->name('volunteers-offices.dashboard-office');

        Route::get('/volunteers-office/request-volunteer', 'requestVolunteer')
            ->name('volunteers-offices.request-volunteer');

        Route::post('/cancel-opportunity/{id}', 'cancelOpportunity')
            ->name('volunteers-offices.cancel-opportunity');
    });


    Route::controller(OpportunitiesController::class)->group(function () {
        Route::get('/edit-opportunity/{id}', 'edit')
            ->name('volunteers-offices.edit-opportunity');

        Route::post('/update-opportunity/{id}', 'update')
            ->name('volunteers-offices.update-opportunity');

        Route::get('/invite-volunteer-office', 'showScholarsOffice')
            ->name('volunteers-offices.invite-volunteer-office');
        Route::post('/invite-scholar', 'inviteScholar')
            ->name('volunteers-offices.invite-scholar');

        Route::get('/history', 'historyOffice')
            ->name('volunteers-offices.history-office');

        Route::get('/create-opportunity', 'createOpportunity')
            ->name('volunteers-offices.create-opportunity');
        Route::post('/store-opportunity', 'store')
            ->name('volunteers-offices.store-opportunity');
    });

});
