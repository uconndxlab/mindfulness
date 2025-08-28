<?php

use App\Models\Email_Body;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\UserController;
use App\Models\User;

use App\Models\QuizAnswers;

Route::middleware('web')->group(function () {
    //default
    Route::redirect("/","/home");

    //AUTHENTICATION
    //login page
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
    //login request
    Route::post('/login', [AuthController::class,'authenticate'])->middleware('throttle:10,1')->name('login.submit');
    
    //REGISTRATION
    Route::middleware('registration.lock')->group(function () { 
        //registration page
        Route::get('/account-creation', [AuthController::class, 'registrationPage'])->name('register');
        //registration request - throttled in controller
        Route::post('/account-creation', [AuthController::class,'register'])->name('register.submit');
    });
    
    //EMAIL VERIFICATION
    Route::middleware('auth')->group(function () {
        //BUILT IN VERIFICATION FUNCTIONS
        Route::get('/email/verify', function () {
            if (Auth::user()->email_verified_at) {
                return redirect()->back();
            }
            return view('auth.verify');
        })->name('verification.notice');
        Route::post('/email/verification-notification', [AuthController::class, 'sendVerifyEmail'])->middleware('throttle:4,1')->name('verification.send'); // throttled in controller too
        Route::get('/check-verification', [AuthController::class, 'checkVerification'])->name('verification.check');
    });
    // verify email button in email
    Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    
        try {
            // find user
            $user = User::findOrFail($request->id);

            // check hash
            if (!hash_equals((string) $request->hash, sha1($user->email))) {
                abort(403, 'Invalid verification link');
            }
    
            // verify the user
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                // log email verification
                activity('auth')
                    ->event('verification')
                    ->causedBy($user)
                    ->log('Verified');
            }
          
            // if user is already logged in
            if (Auth::check() && Auth::id() == $user->id) {
                return redirect('/welcome');
            }

            // otherwise redirect to login
            return redirect('/login')->with('success', 'Email verified successfully. Please login.');
    
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Verification failed. Please try again.');
        }
    })->middleware('signed')->name('verification.verify');
    
    //FORGOT PASSWORD
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    // throttled in controller
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
    
    
    //AUTH protected routes
    Route::middleware(['auth', 'verified', 'update.last.active', 'check.account.lock', 'session.policy'])->group(function () {
        //logout
        Route::post('/logout', [AuthController::class,'logout'])->middleware('throttle:20,1')->name('logout');

        //NEW EXPLORE
        Route::get('/home', [PageNavController::class, 'exploreHome'])->name('explore.home');
        Route::get('/explore/module/{module_id}', [PageNavController::class, 'exploreModule'])->name('explore.module');
        //using post instead for case with desired accordion open
        Route::get('/explore/bonus/{day_id}', [PageNavController::class, 'exploreModuleBonus'])->name('explore.module.bonus');
        Route::get('/checkActivity/{activity_id}', [PageNavController::class, 'checkActivityLocked'])->name('check.activity');
        Route::get('/explore/activity/{activity_id}', [PageNavController::class, 'exploreActivity'])->name('explore.activity');
        // use for when skipping warning modal
        Route::get('/explore/activity/{activity_id}/fast', [PageNavController::class, 'exploreActivityBypass'])->name('explore.activity.bypass');
        Route::post('/quiz/{quiz_id}', [PageNavController::class,'submitQuiz'])->middleware('throttle:30,1')->name('quiz.submit');
        Route::get('/exploreBtn', [PageNavController::class, 'exploreBrowseButton'])->name('explore.browse');

        // activity completion (add light rate-limiting)
        Route::post('/activities/complete', [ActivityController::class, 'complete'])->middleware('throttle:30,1')->name('activities.complete');
        Route::post('/activities/skip', [ActivityController::class, 'skip'])->middleware('throttle:30,1')->name('activities.skip');
        Route::post('/activities/log-interaction', [ActivityController::class, 'logInteraction'])->middleware('throttle:60,1')->name('activities.log_interaction');
        
        //NAVIGATION
        //Page Navigation - the controller is not totally necessary
        Route::get('/welcome', [PageNavController::class, 'welcomePage'])->name('welcome');
        // Route::get('/voice-select', [PageNavController::class, 'voiceSelectPage'])->name('voiceSelect');
        
        Route::get('/journaltab', [PageNavController::class, 'journal'])->name('journal');
        Route::get('/journal', [PageNavController::class, 'journalCompose'])->name('journal.compose');
        Route::get('/journal-library', [PageNavController::class, 'journalLibrary'])->name('journal.library');
        // throttle??
        Route::get('/journal/search', [PageNavController::class, 'journalSearch'])->name('journal.search');
        
        Route::get('/profile', [PageNavController::class, 'accountPage'])->name('account');
        Route::get('/about', [PageNavController::class, 'helpPage'])->name('help');

        //LIBRARY
        Route::get('/librarytab', [PageNavController::class, 'library'])->name('library');
        Route::get('/favorites', [PageNavController::class, 'favoritesLibrary'])->name('library.favorites');
        Route::get('/library', [PageNavController::class, 'mainLibrary'])->name('library.main');
        // throttle??
        Route::get('/search', [PageNavController::class, 'librarySearch'])->name('library.search');
        
        //User updates
        Route::put('/user/update/voice', [UserController::class, 'updateVoice'])->name('user.update.voice');
        
        //favorites
        Route::post('/togggleFavorite', [UserController::class, 'toggleFavorite'])->middleware('throttle:30,1')->name('favorite.toggle');
        
        //NOTES - throttled in controller + guard API spam
        Route::resource('note', NoteController::class)->middleware('throttle:30,1');
        
        //ADMIN ONLY
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [AdminUserController::class, 'dashboard'])->name('dashboard');
            Route::post('/lock-registration-access', [AdminUserController::class, 'lockRegistrationAccess'])->middleware('throttle:10,1')->name('lock-registration-access');
            
            Route::get('/test', function () {
                $quizAnswer = QuizAnswers::first();
                if (!$quizAnswer) {
                    dd('No quiz answers found');
                }
                
                // Show the current format
                echo "<h3>Current Raw Answer:</h3>";
                echo "<pre>" . json_encode($quizAnswer->answers, JSON_PRETTY_PRINT) . "</pre>";
                
                // Show what it should look like in the new format
                echo "<h3>Expected New Format:</h3>";
                $newFormat = [
                    "1" => [["6" => "some other text"]],
                    "2" => [["3" => null]]
                ];
                echo "<pre>" . json_encode($newFormat, JSON_PRETTY_PRINT) . "</pre>";
                
                // Show all quiz answers for user 1
                echo "<h3>All Quiz Answers for User 1:</h3>";
                $allAnswers = QuizAnswers::where('user_id', 1)->get();
                foreach($allAnswers as $answer) {
                    echo "<strong>Quiz #{$answer->quiz_id}:</strong><br>";
                    echo "<pre>" . json_encode($answer->answers, JSON_PRETTY_PRINT) . "</pre>";
                    echo "<hr>";
                }
            });
            
            Route::get('/users', [AdminUserController::class, 'index'])->name('users');
            Route::get('/events', [AdminEventController::class, 'index'])->name('events');
            Route::get('/events/export/csv', [AdminEventController::class, 'exportEvents'])->name('events.export');
        });
    }); 
});