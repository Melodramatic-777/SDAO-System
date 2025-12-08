<?php

use App\Http\Controllers\AccountsScholarsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsVolunteersController;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\OpportunitiesDisplayController;
use App\Http\Controllers\SubmitOpportunityController;
use App\Http\Controllers\OnTheSpotVolunteerController;

Route::prefix('volunteers-admin')->group(function () {


    
Route::post('/admin/logout', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'destroy'])
    ->name('volunteers-admin.logout');

Route::get('/admin/login', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'create']);

    
Route::post('/otsv/{id}/accept', [OnTheSpotVolunteerController::class, 'acceptOTSV'])
    ->name('volunteers-admin.otsv.accept');

Route::post('/otsv/{id}/decline', [OnTheSpotVolunteerController::class, 'declineOTSV'])
    ->name('volunteers-admin.otsv.decline');

    

Route::get('/dashboard', [\App\Http\Controllers\VolunteersAdminDashboardController::class, 'dashboard'])
    ->name('volunteers-admin.dashboard');
    Route::view('/leader-board', 'volunteers-admin.leader-board')->name('volunteers-admin.leader-board');
    Route::view('/inquiries', 'volunteers-admin.inquiries')->name('volunteers-admin.inquiries');
    Route::view('/home', 'volunteers-admin.home')->name('volunteers-admin.home');

    Route::controller(OpportunitiesDisplayController::class)->group(function () {
        Route::get('/opportunities', 'opportunities')->name('volunteers-admin.opportunities');
    });

    Route::controller(AccountsScholarsController::class)->group(function () {
        Route::get('/student-volunteers', 'showStudentVolunteers')->name('volunteers-admin.student-volunteers');
        Route::get('/student/{student_no}', 'showVolunteerDetail')->name('volunteers-admin.student-volunteer-detail');
    });

    Route::controller(AccountsVolunteersController::class)->group(function () {
        Route::get('/accounts', 'showAspiringScholarsPage')->name('volunteers-admin.accounts');
        Route::post('/create-aspiring-scholar-account', 'storeAspiringScholarAccount')->name('volunteers-admin.create-aspiring-scholar-account');
        Route::post('/volunteers-admin/aspiring-scholar/delete', 'archiveAspiringScholar')->name('volunteers-admin.archive-aspiring-scholar');
    });

    Route::controller(OpportunitiesController::class)->group(function () {
        Route::get('/history', 'history')->name('volunteers-admin.history');
        Route::get('/history/{office}', 'officeOpportunities')->name('volunteers-admin.office-opportunities');
        Route::get('/create-opportunity', 'createOpportunity')->name('volunteers-admin.create-opportunity');
        Route::get('/opportunities/{id}', 'show')->name('volunteers-admin.opportunities.show');
        Route::patch('/opportunities/{id}/status', 'updateStatus')->name('volunteers-admin.opportunity.update-status');
        Route::post('/opportunities/{id}/cancel', 'cancelOpportunity')->name('volunteers-admin.opportunities.cancel');
        Route::get('/invite-volunteer', 'showScholars')->name('volunteers-admin.invite-volunteer');
        Route::get('/volunteers/invite', 'showScholars')->name('volunteers.invite');
        Route::post('/invite-scholar', 'inviteScholar')->name('volunteers-admin.inviteScholar');
    });

    Route::post('/store-opportunity', [SubmitOpportunityController::class, 'store'])
        ->name('volunteers-admin.store-opportunity');
});
