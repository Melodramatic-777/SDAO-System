<?php

use App\Http\Controllers\AccountsScholarsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsVolunteersController;
use App\Http\Controllers\OpportunitiesController;
use App\Http\Controllers\StudentVolunteersController;


Route::prefix('volunteers-admin')->group(function () {

    // 📊 Dashboard
    Route::get('/dashboard', fn() => view('volunteers-admin.dashboard'))
        ->name('volunteers-admin.dashboard');

    // 🗓️ Opportunities page
    Route::get('/opportunities', [OpportunitiesController::class, 'opportunities'])
        ->name('volunteers-admin.opportunities');

    // 🏆 Leaderboard
    Route::get('/leader-board', fn() => view('volunteers-admin.leader-board'))
        ->name('volunteers-admin.leader-board');




    // 👥 Student Volunteers
    Route::get('student-volunteers', [AccountsScholarsController::class, 'showStudentVolunteers'])
    ->name('volunteers-admin.student-volunteers');
    // Student Volunteer Detail Page
    Route::get('student/{student_no}',  [AccountsScholarsController::class, 'viewStudentProfile']
    )->name('volunteers-admin.student-volunteer-detail');





    // 🧾 Account creation & management
    Route::get('/accounts', [AccountsVolunteersController::class, 'showAspiringScholarsPage'])
        ->name('volunteers-admin.accounts');

    Route::post('/create-aspiring-scholar-account', [AccountsVolunteersController::class, 'storeAspiringScholarAccount'])
        ->name('volunteers-admin.create-aspiring-scholar-account');
    
        Route::post('/volunteers-admin/aspiring-scholar/delete', [AccountsVolunteersController::class, 'archiveAspiringScholar'])
    ->name('volunteers-admin.archive-aspiring-scholar');



    // 🕓 History page
    Route::get('/history', [OpportunitiesController::class, 'history'])
        ->name('volunteers-admin.history');
    Route::get('/history/{office}', [OpportunitiesController::class, 'officeOpportunities'])
        ->name('volunteers-admin.office-opportunities');







    // 📝 Volunteer opportunity form
    Route::get('/create-opportunity', [OpportunitiesController::class, 'createOpportunity'])
        ->name('volunteers-admin.create-opportunity');




    // Save opportunity to Firebase
    Route::post('/store-opportunity', [OpportunitiesController::class, 'store'])
        ->name('volunteers-admin.store-opportunity');




    // 📄 Opportunity details + actions
    Route::get('/opportunities/{id}', [OpportunitiesController::class, 'show'])
        ->name('volunteers-admin.opportunities.show');

    Route::patch('/opportunities/{id}/status', [OpportunitiesController::class, 'updateStatus'])
        ->name('volunteers-admin.opportunity.update-status');

Route::post('/opportunities/{id}/cancel', [OpportunitiesController::class, 'cancelOpportunity'])
    ->name('volunteers-admin.opportunities.cancel');





    // ✉️ Invite Volunteer
    Route::get('/invite-volunteer', [OpportunitiesController::class, 'showScholars'])
        ->name('volunteers-admin.invite-volunteer');

    Route::get('/volunteers/invite', [OpportunitiesController::class, 'showScholars'])
        ->name('volunteers.invite');

    Route::post('/invite-scholar', [OpportunitiesController::class, 'inviteScholar'])
        ->name('volunteers-admin.inviteScholar');

    // 📬 Inquiries
    Route::get('/inquiries', fn() => view('volunteers-admin.inquiries'))
        ->name('volunteers-admin.inquiries');
});
