<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
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

        //check auth and remember
        $remember = $request->has('remember');
        if (Auth::attempt($credentials, $remember)) {
            return redirect()->intended('/explore/home');
        }
        
        return back()->withErrors(['credentials' => 'Invalid credentials.'])->withInput();
    }

    public function logout() {
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
        //TODO - finish validation - custom error messages, confirmed?
        //validate inputs
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required',  'email',  'max:255',  'unique:'.User::class],
                'password'=> ['required', Password::defaults()],
            ], [
                'name.required' => 'Please enter a name.',
                'name.max' => 'Name must be no longer than 255 characters.',
                'email.required' => 'Please enter an email address.',
                'email.email' => 'Not a valid email.',
                'email.max' => 'Email must be no longer than 255 characters.',
                'email.unique' => 'The provided email is already in use.',
                'password.required' => 'Please enter a password.'
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        //create user
        $user = User::create([
            'name' => $request->name,
            'email'=> $request->email,
            'password'=> Hash::make($request->password),
        ]);

        //login and redirect
        event(new Registered($user));
        $remember = $request->has('remember');
        Auth::attempt($request->only('email', 'password'), $remember);
        return redirect(route('welcome'));
    }
}
