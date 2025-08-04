<?php

use Illuminate\Support\Facades\Route;

Route::get('/volunteers-user/dashboard', function () {
    return view('volunteers-user.dashboard'); })->name('volunteers-user.dashboard');
