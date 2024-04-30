<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request) : void
    {
        //TODO login - use user controller?
        //$this->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return redirect()->intended('dashboard'); // Redirect to a dashboard or any other page after successful login
    }


        
    }





    public function registrationPage()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        //TODO make a user, - user model? using user controller?
    }
}
