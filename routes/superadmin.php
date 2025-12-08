<?php

use App\Http\Controllers\AccountsOfficesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;


Route::get('/superadmin/admins', [AdminController::class, 'listAdmins'])->name('superadmin.admins.list');
Route::get('/superadmin/admins/create', [AdminController::class, 'createAdminForm'])->name('superadmin.admins.create');
Route::post('/superadmin/admins/create', [AdminController::class, 'storeAdmin'])->name('superadmin.admins.store');


    
Route::post('/admin/logout', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'destroy'])
    ->name('superadmin.logout');

Route::get('/admin/login', [App\Http\Controllers\Auth\AdminAuthenticatedSessionController::class, 'create']);


    Route::post('/offices/create', [AccountsOfficesController::class, 'storeOffice'])
        ->name('superadmin.create-office');


    Route::post('/office-accounts/create', [AccountsOfficesController::class, 'storeOfficeAccount'])
        ->name('superadmin.create-office-account');

// Admins
Route::delete('/superadmin/admins/revoke/{email}', [AdminController::class, 'revokeAdmin'])
    ->name('superadmin.admins.revoke');

// Office Users
Route::delete('/superadmin/office-users/revoke/{email}', [AdminController::class, 'revokeOfficeUser'])
    ->name('superadmin.office-users.revoke');





