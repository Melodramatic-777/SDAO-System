<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/superadmin/admins', [AdminController::class, 'listAdmins'])->name('superadmin.admins.list');
Route::get('/superadmin/admins/create', [AdminController::class, 'createAdminForm'])->name('superadmin.admins.create');
Route::post('/superadmin/admins/create', [AdminController::class, 'storeAdmin'])->name('superadmin.admins.store');



    Route::post('/offices/create', [AdminController::class, 'createOffice'])
        ->name('superadmin.create-office');


    Route::post('/office-accounts/create', [AdminController::class, 'createOfficeAccount'])
        ->name('superadmin.create-office-account');

// Admins
Route::delete('/superadmin/admins/revoke/{email}', [AdminController::class, 'revokeAdmin'])
    ->name('superadmin.admins.revoke');

// Office Users
Route::delete('/superadmin/office-users/revoke/{email}', [AdminController::class, 'revokeOfficeUser'])
    ->name('superadmin.office-users.revoke');





