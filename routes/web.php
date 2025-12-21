<?php

use Illuminate\Support\Facades\Route;


Route::get('/', fn() => view('welcome'));




require __DIR__.'/auth.php';
require __DIR__.'/admin.php';          
require __DIR__.'/superadmin.php';     
require __DIR__.'/scholars-admin.php';   
require __DIR__.'/volunteers-admin.php';
require __DIR__.'/scholars.php';      
require __DIR__.'/volunteers-offices.php';   


