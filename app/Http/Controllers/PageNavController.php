<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageNavController extends Controller
{
    public function welcomePage()
    {
        return view("auth.welcome");
    }

    public function voiceSelectPage()
    {
        return view("auth.voice-select");
    }

    public function exploreMainPage()
    {
        return view("explore.main");
    }

    public function journalPage()
    {
        return view("profile.journal");
    }

    public function profilePage()
    {
        return view("profile.accountInformation");
    }
}
