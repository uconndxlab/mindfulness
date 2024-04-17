<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\NoteController;

//login
Route::get('/login', [AuthController::class, 'loginPage'])->name('login.show');

//registration
Route::get('/account-creation', [AuthController::class, 'registrationPage'])->name('registration.show');


//Page Navigation - the controller is not totally necessary
// Route::middleware(['auth'])->group(function () {
// });
Route::redirect("/","/explore");
Route::get('/welcome', [PageNavController::class, 'welcomePage'])->name('welcome');
Route::get('/voice-select', [PageNavController::class, 'voiceSelectPage'])->name('voiceSelect');
Route::get('/explore', [PageNavController::class, 'exploreMainPage'])->name('explore');
Route::get('/journal', [PageNavController::class, 'journalPage'])->name('journal');
Route::get('/profile', [PageNavController::class, 'profilePage'])->name('profile');


//notes CRUD routes
Route::resource('note', NoteController::class);

// Route::get('/note', [NoteController::class, 'index'])->name('note.index');
// Route::get('/note/create', [NoteController::class, 'create'])->name('note.create');
// Route::post('/note', [NoteController::class,'store'])->name('note.store');
// Route::get('/note/{id}', [NoteController::class, 'show'])->name('note.show');
// Route::post('/note/{id}/edit', [NoteController::class,'edit'])->name('note.edit');
// Route::put('/note/{id}', [NoteController::class,'update'])->name('note.update');
// Route::delete('/note/{id}', [NoteController::class, 'destroy'])->name('note.destroy');