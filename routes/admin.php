<?php

use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\Auth\ForgotPasswordController;


// Custom Admin login
Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])->name('admin.login.store');
Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('admin.logout');



Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::put('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
    ->name('password.update');



Route::post('/admin-login', [ContactUsController::class, 'store']);
