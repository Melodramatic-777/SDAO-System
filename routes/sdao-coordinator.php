<?php

use Illuminate\Support\Facades\Route;

Route::get('/sdao-coordinator/home', fn() => view('sdao-coordinator.home'))->name('sdao-coordinator.home');
