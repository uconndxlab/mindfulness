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
    public function exploreHomePage()
    {
        return view("explore.home");
    }

    public function exploreWeekly() {
        $backRoute = route('explore.home');
        return view('explore.weekly', compact('backRoute'));
    }

    public function journalPage()
    {
        return view("profile.journal");
    }

    public function profilePage()
    {
        //TODO adjust backRoute
        $backRoute = route("explore.home");
        $showProfileLink = false;
        return view("profile.accountInformation", compact("backRoute", "showProfileLink"));
    }
}
