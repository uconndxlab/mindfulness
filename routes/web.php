<?php

use App\Http\Controllers\UserController;
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


//AUTH protected routes
Route::middleware('auth')->group(function () {
    //logout
    Route::get('/logout', [AuthController::class,'logout'])->name('logout');

    //NAVIGATION
    //Page Navigation - the controller is not totally necessary
    Route::get('/welcome', [PageNavController::class, 'welcomePage'])->name('welcome');
    Route::get('/voice-select', [PageNavController::class, 'voiceSelectPage'])->name('voiceSelect');
    Route::get('/journal', [PageNavController::class, 'journalPage'])->name('journal');
    Route::get('/profile', [PageNavController::class, 'profilePage'])->name('profile');
    //explore pages
    Route::get('', [PageNavController::class, 'exploreResume'])->name('explore.resume');
    Route::get('/explore', [PageNavController::class, 'exploreHomePage'])->name('explore.home');
    Route::get('/explore/{contentKey}', [PageNavController::class, 'exploreWeekly'])->name('explore.weekly');
    
    //TODO - USER, all
    Route::put('', [UserController::class, 'updateVoice'])->name('');
    Route::put('', [UserController::class, 'updateName'])->name('');
    Route::put('', [UserController::class, 'updatePassword'])->name('');
    
    //NOTES
    Route::resource('note', NoteController::class);
});

// Route::get('/note', [NoteController::class, 'index'])->name('note.index');
// Route::get('/note/create', [NoteController::class, 'create'])->name('note.create');
// Route::post('/note', [NoteController::class,'store'])->name('note.store');
// Route::get('/note/{id}', [NoteController::class, 'show'])->name('note.show');
// Route::post('/note/{id}/edit', [NoteController::class,'edit'])->name('note.edit');
// Route::put('/note/{id}', [NoteController::class,'update'])->name('note.update');
// Route::delete('/note/{id}', [NoteController::class, 'destroy'])->name('note.destroy');