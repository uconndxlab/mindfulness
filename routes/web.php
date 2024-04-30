<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\NoteController;

Route::redirect("/","/login");

//AUTHENTICATION
//login page
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
//login request
Route::post('/login', [AuthController::class,'authenticate'])->name('login.submit');

//registration page
Route::get('/account-creation', [AuthController::class, 'registrationPage'])->name('register');
//registration request
Route::post('/account-creation', [AuthController::class,'register'])->name('register.submit');



//NAVIGATION
//Page Navigation - the controller is not totally necessary
Route::get('/welcome', [PageNavController::class, 'welcomePage'])->name('welcome');
Route::get('/voice-select', [PageNavController::class, 'voiceSelectPage'])->name('voiceSelect');
Route::get('/explore', [PageNavController::class, 'exploreMainPage'])->name('explore');
Route::get('/journal', [PageNavController::class, 'journalPage'])->name('journal');
Route::get('/profile', [PageNavController::class, 'profilePage'])->name('profile');


//NOTES
Route::resource('note', NoteController::class);

// Route::get('/note', [NoteController::class, 'index'])->name('note.index');
// Route::get('/note/create', [NoteController::class, 'create'])->name('note.create');
// Route::post('/note', [NoteController::class,'store'])->name('note.store');
// Route::get('/note/{id}', [NoteController::class, 'show'])->name('note.show');
// Route::post('/note/{id}/edit', [NoteController::class,'edit'])->name('note.edit');
// Route::put('/note/{id}', [NoteController::class,'update'])->name('note.update');
// Route::delete('/note/{id}', [NoteController::class, 'destroy'])->name('note.destroy');