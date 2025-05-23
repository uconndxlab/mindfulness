<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\UserController;
use App\Models\Email_Body;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageNavController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ContentManagementController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
        Route::post('/email/verification-notification', [AuthController::class, 'sendVerifyEmail'])->name('verification.send');
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
            }
            
            // fire GA event - email verified
            session()->flash('ga_event', [
                'name' => 'email_verified',
                'params' => [
                    'event_category' => 'Authentication',
                    'event_label' => 'Email Verified',
                    'user_id' => $user->id,
                    'email' => $user->email
                ]
            ]);

            // if user is already logged in
            if (Auth::user()->id == $user->id) {
                return redirect('/welcome');
            }

            // otherwise redirect to login
            return redirect('/login')->with('success', 'Email verified successfully. Please login.');
    
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Verification failed. Please try again.');
        }
    })->middleware('signed')->name('verification.verify');
    
    //FORGOT PASSWORD
    // Auth::routes(['verify' => true]);
    // Auth::routes();
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    // throttled in controller
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
    
    
    //AUTH protected routes
    Route::middleware(['auth', 'verified', 'update.last.active', 'check.account.lock'])->group(function () {
        //logout
        Route::post('/logout', [AuthController::class,'logout'])->name('logout');

        //NEW EXPLORE
        Route::get('/home', [PageNavController::class, 'exploreHome'])->name('explore.home');
        Route::get('/explore/module/{module_id}', [PageNavController::class, 'exploreModule'])->name('explore.module');
        //using post instead for case with desired accordion open
        Route::get('/explore/bonus/{day_id}', [PageNavController::class, 'exploreModuleBonus'])->name('explore.module.bonus');
        Route::get('/checkActivity/{activity_id}', [PageNavController::class, 'checkActivityLocked'])->name('check.activity');
        Route::get('/explore/activity/{activity_id}', [PageNavController::class, 'exploreActivity'])->name('explore.activity');
        // use for when skipping warning modal
        Route::get('/explore/activity/{activity_id}/fast', [PageNavController::class, 'exploreActivityBypass'])->name('explore.activity.bypass');
        Route::post('/quiz/{quiz_id}', [PageNavController::class,'submitQuiz'])->name('quiz.submit');
        Route::get('/exploreBtn', [PageNavController::class, 'exploreBrowseButton'])->name('explore.browse');

        // activity completion
        Route::post('/activities/complete', [ActivityController::class, 'complete'])->name('activities.complete');
        Route::post('/activities/skip', [ActivityController::class, 'skip'])->name('activities.skip');
        
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
        Route::put('/user/update/namePass', [UserController::class, 'updateNamePass'])->name('user.update.namePass');
        
        //favorites
        Route::post('/togggleFavorite', [UserController::class, 'toggleFavorite'])->middleware('throttle:70,1')->name('favorite.toggle');
        
        //contact form - throttled in controller
        Route::post('/contact', [ContactFormController::class, 'submitForm'])->name('contact.submit');
        
        //NOTES - throttled in controller
        Route::resource('note', NoteController::class);
        
        //ADMIN ONLY
        Route::middleware('admin')->group(function () {
            //Content upload
            Route::get('/adminlanding',[ContentManagementController::class,'adminLanding'])->name('admin.landing');
            Route::get('/usersList', [ContentManagementController::class,'usersList'])->name('users.list');
            Route::post('/changeAccess/{user_id}', [ContentManagementController::class,'changeAccess'])->name('users.access');
            Route::post('/registrationLock', [ContentManagementController::class,'registrationAccess'])->name('registration.lock');
            Route::post('/emailRemindUser/{user_id}', [ContentManagementController::class,'emailRemindUser'])->name('users.remind');
            //email testing
            Route::get('/sendTestMail/{type}', [ContentManagementController::class,'emailTesting'])->name('email.test');

            Route::get('/showReminderEmail/{user_id}', function (Request $request) {
                $user = User::findOrFail($request->user_id)->first();
                $body = Email_Body::where('type', 'reminder')->inRandomOrder()->first()->body;
                return view('emails.inactivity_reminder', compact('user', 'body'));
            });

            Route::delete('/deleteUser/{user_id}', [UserController::class,'deleteUser'])->name('users.delete');
        });
    }); 
});