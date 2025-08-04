<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsActivitiesController;
use App\Http\Controllers\ProposalsController;

Route::prefix('activities-admin')->group(function () {

    Route::get('/home', [ProposalsController::class, 'activitiesAdminHome'])->name('activities-admin.home');
    Route::get('/activities-calendar', fn() => view('activities-admin.activities-calendar'))->name('activities-admin.activities-calendar');
    Route::get('/documents', fn() => view('activities-admin.documents'))->name('activities-admin.documents');
    Route::get('/profile', fn() => view('activities-admin.profile'))->name('activities-admin.profile');
    Route::get('/inquiries', fn() => view('activities-admin.inquiries'))->name('activities-admin.inquiries');


        Route::get('/proposals/{id}/review', [ProposalsController::class, 'review'])->name('activities-admin.proposals.review');
        Route::post('/proposals/{id}/approve', [ProposalsController::class, 'approve'])->name('activities-admin.proposals.approve');



                    // Account creation & management
                    Route::get('/accounts', [AccountsActivitiesController::class, 'showAccountsPage'])->name('activities-admin.accounts');

                    // Organizations
                    Route::post('/organizations/store', [AccountsActivitiesController::class, 'storeOrganization'])->name('activities-admin.store-organization');
                    Route::post('/organizations/create-account', [AccountsActivitiesController::class, 'storeOrganizationAccount'])->name('activities-admin.organizations.create-account');

                    // Schools
                    Route::post('/schools/store', [AccountsActivitiesController::class, 'storeSchool'])->name('activities-admin.schools.store');
                    Route::post('/schools/create-dean-account', [AccountsActivitiesController::class, 'storeDeanAccount'])->name('activities-admin.schools.create-dean-account');
                    Route::post('/schools/create-program-chair-account', [AccountsActivitiesController::class, 'storeProgramChairAccount'])->name('activities-admin.schools.create-program-chair-account');

                    // Other roles
                    Route::post('/create-comex-account', [AccountsActivitiesController::class, 'storeComexAccount'])->name('activities-admin.create-comex-account');
                    Route::post('/create-marketing-account', [AccountsActivitiesController::class, 'storeMarketingAccount'])->name('activities-admin.create-marketing-account');
                    Route::post('/create-department-account', [AccountsActivitiesController::class, 'storeDepartmentAccount'])->name('activities-admin.create-department-account');
                    Route::post('/create-facilities-account', [AccountsActivitiesController::class, 'storeFacilitiesAccount'])->name('activities-admin.create-facilities-account');
                    Route::post('/create-it-systems-account', [AccountsActivitiesController::class, 'storeItSystemsAccount'])->name('activities-admin.create-it-systems-account');
                    Route::post('/create-executive-account', [AccountsActivitiesController::class, 'storeExecutiveAccount'])->name('activities-admin.create-executive-account');
                    Route::post('/create-director', [AccountsActivitiesController::class, 'storeDirector'])->name('activities-admin.create-director');
                    Route::post('/create-director-user', [AccountsActivitiesController::class, 'storeDirectorUser'])->name('activities-admin.create-director-user');
});
