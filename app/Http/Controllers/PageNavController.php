<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;

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
        // //get user
        $id = Auth::id();
        $notes = Note::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        return view("profile.journal", compact("notes"));
    }

    public function backButton() {
        //back button functionality - get route, forget key, redirect
        $backRoute = Session::get("back_route");
        Session::forget("back_route");
        return redirect()->to($backRoute);
    }

    public function profilePage()
    {
        //set nav bar buttons
        $showBackBtn = true;
        $hideProfileLink = true;
        //if returning from profile submission, do not reset back_route
        if (parse_url(url()->previous(), PHP_URL_PATH) != "/profile") {
            Session::put("back_route", url()->previous());
        }
        return view("profile.accountInformation", compact("showBackBtn", "hideProfileLink"));
    }

    public function exploreHomePage()
    {
        //example lists for content
        $week1List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5", "Compass6", "Compass7","Compass8");
        $week2List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5");
        $week3List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5", "Compass6");
        $week4List = array("Compass1", "Compass2", "Compass3", "Compass4", "Compass5", "Compass6");

        //track explore page
        Session::put('last_explore_page', 'explore');
        return view("explore.home", compact("week1List", "week2List", "week3List", "week4List"));
    }

    public function exploreWeekly($contentKey) {
        //set back_route
        $showBackBtn = true;
        Session::put("back_route", '/explore');
        //track explore page for browse button
        Session::put('last_explore_page', 'explore/'.$contentKey);
        return view('explore.weekly', compact('showBackBtn', 'contentKey'));
    }

    public function exploreBrowseButton() {
        //double click functionality - if clicking browse while on an explore page
        if (Str::startsWith(parse_url(url()->previous(), PHP_URL_PATH), '/explore')) {
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
