<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\NoteController;

//default
Route::redirect("/","/explore");

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

    //back button
    Route::get('/backBtn', [PageNavController::class, 'backButton'])->name('button.back');

    //explore pages
    Route::get('/exploreBtn', [PageNavController::class, 'exploreBrowseButton'])->name('explore.browse');
    Route::get('/explore', [PageNavController::class, 'exploreHome'])->name('explore.home');
    Route::get('/explore/{lessonId}', [PageNavController::class, 'exploreLesson'])->name('explore.lesson');
    Route::get('/explore/quiz/{quizId}', [PageNavController::class,'exploreQuiz'])->name('explore.quiz');
    Route::post('/explore/quiz/{quizId}', [PageNavController::class,'submitQuiz'])->name('quiz.submit');
    
    //User updates
    Route::put('/user/update/voice', [UserController::class, 'updateVoice'])->name('user.update.voice');
    Route::put('/user/update/namePass', [UserController::class, 'updateNamePass'])->name('user.update.namePass');
    
    //NOTES
    Route::resource('note', NoteController::class);


    //ADMIN ONLY
    Route::middleware('admin')->group(function () {
        //Content upload
        Route::get('/admin', [ContentController::class,'adminPage'])->name('admin.browse');
        Route::get('/admin/lesson/create', [ContentController::class,'newLessonPage'])->name('admin.lesson.create');
        Route::post('/admin/lesson', [ContentController::class, 'storeLesson'])->name('admin.lesson.store');
        Route::get('/admin/lesson/{lessonId}', [ContentController::class,'showLessonPage'])->name('admin.lesson.show');
        Route::put('/admin/lesson/{lessonId}', [ContentController::class,'updateLesson'])->name('admin.lesson.update');
        Route::delete('/admin/lesson/{lessonId}', [ContentController::class, 'deleteLesson'])->name('admin.lesson.delete');
    });
});

// Route::get('/note', [NoteController::class, 'index'])->name('note.index');
// Route::get('/note/create', [NoteController::class, 'create'])->name('note.create');
// Route::post('/note', [NoteController::class,'store'])->name('note.store');
// Route::get('/note/{id}', [NoteController::class, 'show'])->name('note.show');
// Route::post('/note/{id}/edit', [NoteController::class,'edit'])->name('note.edit');
// Route::put('/note/{id}', [NoteController::class,'update'])->name('note.update');
// Route::delete('/note/{id}', [NoteController::class, 'destroy'])->name('note.destroy');