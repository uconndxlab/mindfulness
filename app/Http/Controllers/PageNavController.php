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
        //example lists for content
        $week1List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5", "Compass6", "Compass7","Compass8");
        $week2List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5");
        $week3List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5", "Compass6");
        $week4List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5", "Compass6");
        Session::put('last_explore_page', 'explore');
        return view("explore.home", compact("week1List", "week2List", "week3List", "week4List"));
    }

    public function exploreWeekly($contentKey, $fromBrowse=false) {
        $backRoute = route('explore.home');
        Session::put('last_explore_page', 'explore/'.$contentKey);
        return view('explore.weekly', compact('backRoute', 'contentKey'));
    }

    public function exploreResume() {
        //double click functionality - if clicking browse while on an explore page
        $prevUrl = url()->previous();
        $path = parse_url($prevUrl, PHP_URL_PATH);
        if (Str::startsWith($path, '/explore')) {
            return redirect()->route('explore.home');
        }

        //check session for last used explore page - resume on this page
        $lastExplorePage = Session::get('last_explore_page');

        if ($lastExplorePage && Str::startsWith($lastExplorePage, 'explore/')) {
            return redirect()->to($lastExplorePage);
        } else {
            return redirect()->route('explore.home');
        }
    }

}
