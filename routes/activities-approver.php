<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProposalsController; 

Route::prefix('activities-approver')->group(function () {
    Route::get('/home', [ProposalsController::class, 'approverHome'])->name('activities-approver.home');

                        Route::get('/proposals/{id}/review', [ProposalsController::class, 'review'])->name('activities-approver.proposals.review');
                        Route::post('/proposals/{id}/approve', [ProposalsController::class, 'approve'])->name('activities-approver.proposals.approve');
                        Route::post('/proposals/{id}/reply', [ProposalsController::class, 'reply'])->name('activities-user.proposals.reply');

                    });