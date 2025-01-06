<?php

namespace App\Http\Controllers;

use App\Rules\ValidEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends Controller
{
    use AuthenticatesUsers;


    public function loginPage()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required',
        ], [
            'email.required' => "Email address is required.",
            'email.email' => "Not a valid email address.",
            'password.required' => "Password is required."
        ]);

        //check if user exists first
        $credentials = $request->only('email', 'password');
        // $user = User::where('email', $credentials['email'])->first();
        // if (!$user) { 
        //     return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        // }

        if (Auth::attempt($credentials)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        //check auth and remember
        $remember = $request->has('remember');
        if (Auth::attempt($credentials, $remember)) {
            //if user is locked
            if (Auth::user()->lock_access) {
                //log user out
                Auth::logout();
                return back()->withErrors([
                    'credentials' => 'Your account is locked. If you have any questions, feel free to contact us at <a href="mailto:'.config('mail.contact_email').'">'.config('mail.contact_email').'</a>.',
                ]);
            }
            Auth::user()->update(['last_active_at' => Carbon::now()]);
            return redirect()->intended('/home');
        }
        
        return back()->withErrors(['credentials' => 'Invalid credentials.'])->withInput();
    }

    public function logout(Request $request) {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cache::forget('user_'.Auth::id().'_progress_activities');
        Auth::logout();
        return redirect()->route('login');
    }

    public function registrationPage()
    {
        return view('auth.register');
    }

    //account registration
    public function register(Request $request) : RedirectResponse
    {
        // throttling registration attempts
        // make key, and check if too many attempts
        $key = sha1('register|'.$request->ip());
        $limit = ['attempts' => 4, 'decay' => 3600]; // 3 successes per hour
        if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
            $seconds = RateLimiter::availableIn($key);
            $timeLeft = Carbon::now()->addSeconds($seconds)->diffForHumans(null, true);

            return back()->withErrors(['error' => "Too many registration attempts. Please try again in {$timeLeft}."]);
        }

        //validate inputs
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required',  'email', new ValidEmail(), 'max:255',  'unique:'.User::class],
                'password'=> ['required', Password::min(8)->mixedCase()->numbers()],
            ], [
                'name.required' => 'Please enter a name.',
                'name.max' => 'Name must be no longer than 255 characters.',
                'email.required' => 'Please enter an email address.',
                'email.email' => 'Not a valid email address.',
                'email.max' => 'Email must be no longer than 255 characters.',
                'email.unique' => 'The provided email is already in use.',
                'password.required' => 'Please enter a password.'
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            //create user
            $user = User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'password'=> Hash::make($request->password),
                'last_active_at' => Carbon::now()
            ]);
    
            //unlocking first module/day/activity
            lockAll($user->id);
            unlockFirst($user->id);
    
            //login, hit limiter, redirect
            event(new Registered($user));
            $remember = $request->has('remember');
            Auth::attempt($request->only('email', 'password'), $remember);
            RateLimiter::hit($key, $limit['decay']);
            return redirect(route('welcome'));
        }
        catch (\Exception $e) {
            dd($e);
        }
    }

    public function sendVerifyEmail(Request $request)
    {
        // throttle
        $key = sha1('send_verify_email|'.$request->ip());
        $limit = ['attempts' => 4, 'decay' => 60]; // 5 successes per minute
        if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
            $seconds = RateLimiter::availableIn($key);
            $timeLeft = Carbon::now()->addSeconds($seconds)->diffForHumans(null, true);
            return back()->withErrors(['error' => "Too many attempts. Please try again in {$timeLeft}."]);
        }

        $user = Auth::user();
        $user->sendEmailVerificationNotification();
        if ($user->email_verified_at) {
            return redirect()->intended('/');
        }

        RateLimiter::hit($key, $limit['decay']);
        return back()->with('message', 'Verification link sent!');
    }
}
