<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageNavController extends Controller
{
    public function welcome()
    {
        return view("welcome");
    }
}
