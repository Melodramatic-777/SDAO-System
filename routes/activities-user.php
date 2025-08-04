<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProposalsController;

Route::prefix('activities-user')->group(function () {
    Route::get('/home', [ProposalsController::class, 'index'])->name('activities-user.home');

                Route::get('/create-proposal', [ProposalsController::class, 'createProposal'])->name('activities-user.create-proposal');
                Route::post('/proposals', [ProposalsController::class, 'store'])->name('activities-user.proposals.store');
                Route::get('/proposal/{id}', [ProposalsController::class, 'show'])->name('activities-user.proposal-details');
                Route::get('/proposals/{id}', [ProposalsController::class, 'show'])->name('activities-user.proposals.show');
                Route::post('/proposals/{id}/reply', [ProposalsController::class, 'reply'])->name('activities-user.proposals.reply');
                Route::post('/proposals/{id}/resubmit', [ProposalsController::class, 'resubmit'])->name('activities-user.proposals.resubmit');

                Route::get('/proposals/{id}/edit', [ProposalsController::class, 'edit'])->name('activities-user.proposals.edit');
                Route::post('/proposals/{id}/update', [ProposalsController::class, 'update'])->name('activities-user.proposals.update');


    Route::get('/activities-calendar', fn() => view('activities-user.activities-calendar'))->name('activities-user.activities-calendar');

    Route::get('/history', fn() => view('activities-user.history'))->name('activities-user.history');

    Route::get('/profile', fn() => view('activities-user.profile'))->name('activities-user.profile');
});