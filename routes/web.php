<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountsActivitiesController;

Route::get('/', function () {
    return view('welcome');
});

// 🔒 Default Laravel user auth dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 🔒 Default Laravel user auth profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ✅ Custom Admin login 
// ✅ Custom Admin login
Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])
    ->name('login');
Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])->name('admin.login.store');
Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('admin.logout');



//Activities Admin Routes
Route::get('/activities-admin/home', fn() => view('activities-admin.home'))->name('activities-admin.home');
Route::get('/activities-admin/activities-calendar', fn() => view('activities-admin.activities-calendar'))->name('activities-admin.activities-calendar');
Route::get('/activities-admin/documents', fn() => view('activities-admin.documents'))->name('activities-admin.documents');
Route::get('/activities-admin/profile', fn() => view('activities-admin.profile'))->name('activities-admin.profile');

                    // Account Creation 
                    Route::prefix('activities-admin')->group(function () {

                        Route::get('/accounts', [AccountsActivitiesController::class, 'showAccountsPage'])->name('activities-admin.accounts');

                        // Organizations
                        Route::post('/organizations/store', [AccountsActivitiesController::class, 'storeOrganization'])->name('activities-admin.store-organization');
                        Route::post('/organizations/create-account', [AccountsActivitiesController::class, 'storeOrganizationAccount'])->name('activities-admin.organizations.create-account');

                        // Schools
                        Route::post('/schools/store', [AccountsActivitiesController::class, 'storeSchool'])->name('activities-admin.schools.store');
                        Route::post('/schools/create-dean-account', [AccountsActivitiesController::class, 'storeDeanAccount'])->name('activities-admin.schools.create-dean-account');
                        Route::post('/schools/create-program-chair-account', [AccountsActivitiesController::class, 'storeProgramChairAccount'])->name('activities-admin.schools.create-program-chair-account');

                        // COMEX & Marketing
                        Route::post('/create-comex-account', [AccountsActivitiesController::class, 'storeComexAccount'])->name('activities-admin.create-comex-account');
                        Route::post('/create-marketing-account', [AccountsActivitiesController::class, 'storeMarketingAccount'])->name('activities-admin.create-marketing-account');

                        // ✅ New routes:
                        Route::post('/create-department-account', [AccountsActivitiesController::class, 'storeDepartmentAccount'])->name('activities-admin.schools.create-department-account');
                        Route::post('/create-facilities-account', [AccountsActivitiesController::class, 'storeFacilitiesAccount'])->name('activities-admin.schools.create-facilities-account');
                        Route::post('/create-it-systems-account', [AccountsActivitiesController::class, 'storeItSystemsAccount'])->name('activities-admin.schools.create-it-systems-account');
                        Route::post('/create-executive-account', [AccountsActivitiesController::class, 'storeExecutiveAccount'])->name('activities-admin.schools.create-executive-account');
                        Route::post('/create-director', [AccountsActivitiesController::class, 'storeDirector'])->name('activities-admin.schools.create-director');
                        Route::post('/create-director-user', [AccountsActivitiesController::class, 'storeDirectorUser'])->name('activities-admin.schools.create-director-user');

                    });





Route::get('/activities-admin/inquiries', fn() => view('activities-admin.inquiries'))->name('activities-admin.inquiries');


//Volunteers Admin Routes
Route::get('/volunteers-admin/leader-board', fn() => view('volunteers-admin.leader-board'))->name('volunteers-admin.leader-board');


//SDAO Admin Routes
Route::get('/sdao-coordinator/home', fn() => view('sdao-coordinator.home'))->name('sdao-coordinator.home');



Route::get('/superadmin/admins', [AdminController::class, 'listAdmins'])->name('superadmin.admins.list');
Route::get('/superadmin/admins/create', [AdminController::class, 'createAdminForm'])->name('superadmin.admins.create');
Route::post('/superadmin/admins/create', [AdminController::class, 'storeAdmin'])->name('superadmin.admins.store');








