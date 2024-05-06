<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

    public function journalPage()
    {
        return view("profile.journal");
    }

    public function profilePage()
    {
        //get the url that redirected user to this page
        $backRoute = url()->previous();
        $showProfileLink = false;
        return view("profile.accountInformation", compact("backRoute", "showProfileLink"));
    }

    public function exploreHomePage()
    {
        Session::put('last_explore_page', 'explore');
        return view("explore.home");
    }

    public function exploreWeekly() {
        $backRoute = route('explore.home');
        Session::put('last_explore_page', 'explore/weekly');
        return view('explore.weekly', compact('backRoute'));
    }

    public function explorePages() {
        //check session for last used explore page - resume on this page
        $lastExplorePage = Session::get('last_explore_page');
        if ($lastExplorePage && Str::startsWith($lastExplorePage, 'explore/')) {
            return redirect()->to($lastExplorePage);
        } else {
            return redirect()->route('explore.home');
    }
    }

}
