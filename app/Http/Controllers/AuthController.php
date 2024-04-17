<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        //TODO login - use user controller?
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
