<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\NoteController;
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
            return redirect()->intended('/explore');
        }

        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});


// Route::get('/test', function () {
//     Mail::to('test@example.com')->send(new TestMail());
//     return 'Test email sent!';
// });

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
    Route::get('/explore/week/{week_id}', [PageNavController::class, 'exploreWeek'])->name('explore.week');
    Route::get('/explore/activity/{activity_id}', [PageNavController::class, 'exploreActivity'])->name('explore.activity');
    Route::get('/explore/quiz/{quiz_id}', [PageNavController::class,'exploreQuiz'])->name('explore.quiz');
    Route::post('/explore/quiz/{quiz_id}', [PageNavController::class,'submitQuiz'])->name('quiz.submit');


    //TODO

    //back button
    Route::get('/backBtn', [PageNavController::class, 'backButton'])->name('button.back');

    //explore pages
    Route::get('/exploreBtn', [PageNavController::class, 'exploreBrowseButton'])->name('explore.browse');



    //NAVIGATION
    //Page Navigation - the controller is not totally necessary
    Route::get('/welcome', [PageNavController::class, 'welcomePage'])->name('welcome');
    Route::get('/voice-select', [PageNavController::class, 'voiceSelectPage'])->name('voiceSelect');
    Route::get('/journal', [PageNavController::class, 'journalPage'])->name('journal');
    Route::get('/profile', [PageNavController::class, 'profilePage'])->name('profile');
    Route::get('/library', [PageNavController::class, 'library'])->name('library');
    Route::get('/meditation-library', [PageNavController::class, 'meditationLibrary'])->name('library.meditation');
    Route::get('/favorites', [PageNavController::class, 'favoritesLibrary'])->name('library.favorites');
    
    //User updates
    Route::put('/user/update/voice', [UserController::class, 'updateVoice'])->name('user.update.voice');
    Route::put('/user/update/namePass', [UserController::class, 'updateNamePass'])->name('user.update.namePass');
    Route::put('/user/update/progress', [UserController::class,'updateProgress'])->name('user.update.progress');

    //favorites
    Route::post('/favorites', [UserController::class, 'addFavorite'])->name('favorites.create');
    Route::delete('/favorites/{activity_id}', [UserController::class,'deleteFavorite'])->name('favorites.delete');
    
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
