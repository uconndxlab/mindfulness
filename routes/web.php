<?php

use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ContentManagementController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;

//default
Route::redirect("/","/explore/home");

//AUTHENTICATION
//login page
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
//login request
Route::post('/login', [AuthController::class,'authenticate'])->name('login.submit');

//registration page
Route::get('/account-creation', [AuthController::class, 'registrationPage'])->name('register');
//registration request
Route::post('/account-creation', [AuthController::class,'register'])->name('register.submit');

//EMAIL VERIFICATION
Route::middleware('auth')->group(function () {
    //THERE ARE BUILT IN VERIFICATION FUNCTIONS
    Route::get('/email/verify', function () {
        if (Auth::user()->email_verified_at) {
            return redirect()->back();
        }
        return view('auth.verify');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->intended('/welcome');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $user = Auth::user();
        $user->sendEmailVerificationNotification();
        // TODO DELETE THIS WHEN EMAIL IS SET UP
        $user->email_verified_at = now();
        $user->save();
        if ($user->email_verified_at) {
            return redirect()->intended('/');
        }

        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

//FORGOT PASSWORD
// Auth::routes(['verify' => true]);
// Auth::routes();
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');


//AUTH protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    //logout
    Route::get('/logout', [AuthController::class,'logout'])->name('logout');

    //NEW EXPLORE
    Route::get('/explore/home', [PageNavController::class, 'exploreHome'])->name('explore.home');
    Route::get('/explore/module/{module_id}', [PageNavController::class, 'exploreModule'])->name('explore.module');
    Route::get('/checkActivity/{activity_id}', [PageNavController::class, 'checkActivityLocked'])->name('check.activity');
    Route::get('/explore/activity/{activity_id}', [PageNavController::class, 'exploreActivity'])->name('explore.activity');
    Route::post('/quiz/{quiz_id}', [PageNavController::class,'submitQuiz'])->name('quiz.submit');
    Route::get('/exploreBtn', [PageNavController::class, 'exploreBrowseButton'])->name('explore.browse');
    
    //NAVIGATION
    //Page Navigation - the controller is not totally necessary
    Route::get('/welcome', [PageNavController::class, 'welcomePage'])->name('welcome');
    Route::get('/voice-select', [PageNavController::class, 'voiceSelectPage'])->name('voiceSelect');
    Route::get('/journal', [PageNavController::class, 'journalPage'])->name('journal');
    Route::get('/account', [PageNavController::class, 'accountPage'])->name('account');
    Route::get('/help', [PageNavController::class, 'helpPage'])->name('help');
    Route::get('/library', [PageNavController::class, 'library'])->name('library');
    Route::get('/meditation-library', [PageNavController::class, 'meditationLibrary'])->name('library.meditation');
    Route::get('/favorites', [PageNavController::class, 'favoritesLibrary'])->name('library.favorites');
    Route::get('/search', [PageNavController::class, 'librarySearch'])->name('library.search');
    
    //User updates
    Route::put('/user/update/voice', [UserController::class, 'updateVoice'])->name('user.update.voice');
    Route::put('/user/update/namePass', [UserController::class, 'updateNamePass'])->name('user.update.namePass');
    Route::put('/user/update/progress', [UserController::class,'updateProgress'])->name('user.update.progress');
    
    //favorites
    Route::post('/favorites', [UserController::class, 'addFavorite'])->name('favorites.create');
    Route::delete('/favorites/{activity_id}', [UserController::class,'deleteFavorite'])->name('favorites.delete');

    //contact form
    Route::post('/contact', [ContactFormController::class, 'submitForm'])->name('contact.submit');
    
    //NOTES
    Route::resource('note', NoteController::class);

    //ADMIN ONLY
    Route::middleware('admin')->group(function () {
        //Content upload
        //modules
        Route::get('/module', [ContentManagementController::class,'indexModule'])->name('module.index');
        Route::get('/module/{module_id}', [ContentManagementController::class,'showModule'])->name('module.show');
        Route::post('/module/{module_id}/edit', [ContentManagementController::class,'editModule'])->name('module.edit');
        Route::get('/module/create', [ContentManagementController::class,'createModule'])->name('module.create');
        Route::post('/module', [ContentManagementController::class,'storeModule'])->name('module.store');
        Route::delete('/module/{module_id}', [ContentManagementController::class,'deleteModule'])->name('module.delete');
        //days
        Route::get('/day', [ContentManagementController::class,'indexDay'])->name('day.index');
        Route::get('/day/{day_id}', [ContentManagementController::class,'showDay'])->name('day.show');
        Route::post('/day/{day_id}/edit', [ContentManagementController::class,'editDay'])->name('day.edit');
        Route::get('/day/create', [ContentManagementController::class,'createDay'])->name('day.create');
        Route::post('/day', [ContentManagementController::class,'storeDay'])->name('day.store');
        Route::delete('/day/{day_id}', [ContentManagementController::class,'deleteDay'])->name('day.delete');
        //activities
        Route::get('/activity', [ContentManagementController::class,'indexActivity'])->name('activity.index');
        Route::get('/activity/{activity_id}', [ContentManagementController::class,'showActivity'])->name('activity.show');
        Route::post('/activity/{activity_id}/edit', [ContentManagementController::class,'editActivity'])->name('activity.edit');
        Route::get('/activity/create', [ContentManagementController::class,'createActivity'])->name('activity.create');
        Route::post('/activity', [ContentManagementController::class,'storeActivity'])->name('activity.store');
        Route::delete('/activity/{activity_id}', [ContentManagementController::class,'deleteActivity'])->name('activity.delete');
    });
});
