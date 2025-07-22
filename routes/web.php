<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

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

// ✅ Custom Admin login at `/login` (no `/admin` prefix)
Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])->name('admin.login.store');
Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

// ✅ Firebase Admin protected Filament routes
Route::get('/activities-admin/home', App\Filament\Pages\ActivitiesAdmin\Home::class)->name('activities-admin.home');
Route::get('/volunteers-admin/leader-board', App\Filament\Pages\VolunteersAdmin\LeaderBoard::class)->name('volunteers-admin.leader-board');
Route::get('/sdao-coordinator/home', fn() => 'SDAO Coordinator Dashboard')->name('sdao-coordinator.home');
// ✅ Add Super Admin custom pages (independent of Filament CRUD)

Route::get('/superadmin/admins', [AdminController::class, 'listAdmins'])->name('superadmin.admins.list');
Route::get('/superadmin/admins/create', [AdminController::class, 'createAdminForm'])->name('superadmin.admins.create');
Route::post('/superadmin/admins/create', [AdminController::class, 'storeAdmin'])->name('superadmin.admins.store');
