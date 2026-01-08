<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\OpportunitiesDisplayOfficeController;
use App\Http\Controllers\OnTheSpotVolunteerController;
use App\Http\Controllers\AccountsOfficesController;
use App\Http\Controllers\AdminController;



Route::prefix('volunteers-offices')->group(function () {


       
Route::post('/admin/logout', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'destroy'])
    ->name('volunteers-offices.logout');

Route::get('/admin/login', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'create']);



Route::post('/otsv/{id}/accept', [OnTheSpotVolunteerController::class, 'acceptOTSV'])
    ->name('volunteers-offices.otsv.accept');

Route::post('/otsv/{id}/decline', [OnTheSpotVolunteerController::class, 'declineOTSV'])
    ->name('volunteers-offices.otsv.decline');


    Route::post('/profile/change-password', [AdminController::class, 'changeOfficePassword'])
    ->name('volunteers-offices.profile.change-password');

        Route::get('/profile', [AdminController::class, 'showOfficeProfile'])
        ->name('volunteers-offices.profile');
    

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

  Route::get('/admins', [AdminController::class, 'listAdmins'])
        ->name('volunteers-offices.list-admins');

    Route::get('/admins/create', [AdminController::class, 'createAdminForm'])
        ->name('volunteers-offices.create-admin');

    Route::post('/admins/create', [AdminController::class, 'storeAdmin'])
        ->name('volunteers-offices.store-admin');

    Route::post('/offices/create', [AccountsOfficesController::class, 'storeOffice'])
        ->name('volunteers-offices.create-office');

    Route::post('/office-accounts/create', [AccountsOfficesController::class, 'storeOfficeAccount'])
        ->name('volunteers-offices.create-office-account');

    // Admin / Office User Deletion
    Route::delete('/admins/revoke/{email}', [AdminController::class, 'revokeAdmin'])
        ->name('volunteers-offices.revoke-admin');

    Route::delete('/office-users/revoke/{email}', [AdminController::class, 'revokeOfficeUser'])
        ->name('volunteers-offices.revoke-office-user');



});
