<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

#template
Route::get('/', function () {
    return view('welcome');
});


//login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('show.login');


//registration
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('show.registration');

