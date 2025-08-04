<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/activities-admin.php';
require __DIR__.'/volunteers-admin.php';
require __DIR__.'/sdao-coordinator.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/activities-user.php';
require __DIR__.'/activities-approver.php';
require __DIR__.'/volunteers-user.php';
require __DIR__.'/volunteers-offices.php';

