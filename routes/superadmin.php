<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/superadmin/admins', [AdminController::class, 'listAdmins'])->name('superadmin.admins.list');
Route::get('/superadmin/admins/create', [AdminController::class, 'createAdminForm'])->name('superadmin.admins.create');
Route::post('/superadmin/admins/create', [AdminController::class, 'storeAdmin'])->name('superadmin.admins.store');
