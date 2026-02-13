<?php

namespace App\Http\Controllers;

use App\Enums\MilestoneType;
use App\Events\MilestoneAchieved;
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
use App\Models\Invitation;
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
            'timezone' => ['string', 'nullable'],
        ], [
            'email.required' => "Email address is required.",
            'email.email' => "Not a valid email address.",
            'password.required' => "Password is required."
        ]);

        //check if user exists first
        $credentials = $request->only('email', 'password');
        
        //check auth and remember
        $remember = $request->has('remember');
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $request->session()->regenerateToken();
            //if user is locked
            if (Auth::user()->lock_access) {
                //log user out
                Auth::logout();
                return back()->withErrors([
                    'error' => 'Your account is locked. If you have any questions, feel free to contact us at <a href="mailto:'.config('mail.contact_email').'">'.config('mail.contact_email').'</a>.',
                ]);
            }

            // update active and timezone
            Auth::user()->update([
                'last_active_at' => Carbon::now(),
                'timezone' => $request->input('timezone') ?? config('app.timezone')
            ]);
            
            // log login
            activity('auth')
                ->event('login')
                ->causedBy(Auth::user())
                ->log('Authenticated');

            return redirect()->intended('/home');
        }
      
        return back()->withErrors(['login' => 'Invalid credentials.'])->withInput();
    }

    public function logout(Request $request) {
        $request->session()->invalidate();
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        Cache::forget('user_'.Auth::id().'_progress_activities');
        Auth::logout();
        return redirect()->route('login');
    }

    public function registrationPage(Request $request)
    {
        $invitation = null;
        
        // if invitation token in session (from middleware), get inv details
        if ($request->session()->has('invitation_token')) {
            $token = $request->session()->get('invitation_token');
            $invitation = Invitation::where('token', $token)->first();
        }
        
        return view('auth.register', compact('invitation'));
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
                'email' => ['required', 'email:rfc,dns', 'max:255',  'unique:'.User::class],
                'password'=> ['required', Password::min(8)->mixedCase()->numbers()],
                'timezone' => ['string', 'nullable'],
                'terms_accepted' => ['required', 'accepted']
            ], [
                'name.required' => 'Please enter a name.',
                'name.max' => 'Name must be no longer than 255 characters.',
                'email.required' => 'Please enter an email address.',
                'email.email' => 'Not a valid email address.',
                'email.max' => 'Email must be no longer than 255 characters.',
                'email.unique' => 'The provided email is already in use.',
                'password.required' => 'Please enter a password.',
                'terms_accepted.required' => 'You must accept the Terms of Use to register.',
                'terms_accepted.accepted' => 'You must accept the Terms of Use to register.'
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
        
        try {
            // check if invitation-only mode is enabled and validate invitation
            $invitation = null;
            $skipEmailValidation = false;
            if (getConfig('invitation_only_mode', false)) {
                $invitationToken = $request->input('invitation_token');
                
                if (!$invitationToken) {
                    return back()->withErrors(['error' => 'An invitation is required to register.']);
                }
                
                $invitation = Invitation::where('token', $invitationToken)->first();
                
                if (!$invitation || !$invitation->isValid()) {
                    return back()->withErrors(['error' => 'Invalid or expired invitation.']);
                }
                
                // verify email matches invitation
                if ($invitation->email !== $request->input('email')) {
                    return back()->withErrors(['error' => 'Email must match the invitation email.']);
                }
                $skipEmailValidation = true;
            }
            
            //create user
            $user = User::create([
                'name' => $request->input('name'),
                'email'=> $request->input('email'),
                'password'=> Hash::make(value: $request->input('password')),
                'timezone' => $request->timezone ?? config('app.timezone'),
                'last_active_at' => Carbon::now(),
                'terms_accepted_at' => Carbon::now(),
                'terms_version' => config('terms.current_version'),
            ]);
            
            // mark invitation as used if it exists
            if ($invitation) {
                $invitation->markAsUsed($user);

                $user->email_verified_at = Carbon::now();
                $user->save();
            }
            
            //unlocking first module/day/activity
            lockAll($user->id);
            unlockFirst($user->id);
          
            //login, redirect, event hits MustVerifyEmail which calls sendEmailVerificationNotification
            if (!$skipEmailValidation) {
                event(new Registered($user));
            }
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $request->session()->regenerateToken();
            
            // clear invitation session data
            $request->session()->forget(['invitation_token', 'invitation_email']);
            
            RateLimiter::hit($key, $limit['decay']);
          
            // log registration
            activity('auth')
                ->event('registration')
                ->causedBy($user)
                ->log('Registered');

            // record milestone
            event(new MilestoneAchieved($user, MilestoneType::Registered));
            
            return $skipEmailValidation ? redirect()->route('explore.home') : redirect()->route('verification.notice');
        }
        catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }

    public function sendVerifyEmail(Request $request)
    {
        // throttle
        $key = sha1('send_verify_email|'.$request->ip());
        $limit = ['attempts' => 4, 'decay' => 60]; // 4 successes per minute
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

    public function checkVerification()
    {
        return response()->json([
            'verified' => Auth::user()->email_verified_at ? true : false,
        ]);
    }
}
