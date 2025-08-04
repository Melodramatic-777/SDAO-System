<?php

use Illuminate\Support\Facades\Route;

Route::get('/volunteers-offices/request-volunteer', function () {
    return view('volunteers-offices.request-volunteer'); 
})->name('volunteers-offices.request-volunteer');
