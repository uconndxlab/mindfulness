<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\NoteController;

//template
Route::get('/zzoldpage', function () {
    return view('welcomeold');
});



//login
Route::get('/', [AuthController::class, 'showLoginForm'])->name('show.login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('show.login');

//registration
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('show.registration');


//Page Navigation
Route::get('/welcome', [PageNavController::class, 'welcome'])->name('welcome');


//notes CRUD routes
//TODO should not be web accessible
Route::resource('note', NoteController::class);
// Route::get('/note', [NoteController::class, 'index'])->name('note.index');
// Route::get('/note/create', [NoteController::class, 'create'])->name('note.create');
// Route::post('/note', [NoteController::class,'store'])->name('note.store');
// Route::get('/note/{id}', [NoteController::class, 'show'])->name('note.show');
// Route::post('/note/{id}/edit', [NoteController::class,'edit'])->name('note.edit');
// Route::put('/note/{id}', [NoteController::class,'update'])->name('note.update');
// Route::delete('/note/{id}', [NoteController::class, 'destroy'])->name('note.destroy');