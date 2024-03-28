<?php

use Illuminate\Support\Facades\Route;

#template
Route::get('/', function () {
    return view('welcome');
});



Route::get('/login', function () {
    return view('login');
});

Route::get('/createAccount', function () {
    return view('createAccount');
});
