<?php

use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// Custom Admin login
Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])->name('admin.login.store');
Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
