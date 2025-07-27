<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsVolunteersController;

Route::prefix('volunteers-admin')->group(function () {

    Route::get('/leader-board', fn() => view('volunteers-admin.leader-board'))->name('volunteers-admin.leader-board');
    Route::get('/office', fn() => view('volunteers-admin.office'))->name('volunteers-admin.office');
    Route::get('/student-volunteers', fn() => view('volunteers-admin.student-volunteers'))->name('volunteers-admin.student-volunteers');


                // Account creation & management
                Route::get('/accounts', [AccountsVolunteersController::class, 'showAccountsPage'])->name('volunteers-admin.accounts');
                Route::post('/create-aspiring-scholar-account', [AccountsVolunteersController::class, 'storeAspiringScholarAccount'])->name('volunteers-admin.create-aspiring-scholar-account');
                Route::post('/create-office', [AccountsVolunteersController::class, 'storeOffice'])->name('volunteers-admin.create-office');
                Route::post('/create-office-account', [AccountsVolunteersController::class, 'storeOfficeAccount'])->name('volunteers-admin.create-office-account');
                Route::post('/volunteers-admin/import-scholars', [AccountsVolunteersController::class, 'importScholars'])->name('volunteers-admin.import-scholars');


    Route::get('/inquiries', fn() => view('volunteers-admin.inquiries'))->name('volunteers-admin.inquiries');
});