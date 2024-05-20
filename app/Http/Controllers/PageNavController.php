<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use Carbon\Carbon;
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
        //get user
        $id = Auth::id();
        $notes = Note::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        //formatting the date
        foreach ($notes as $note) {
            $date = Carbon::parse($note->created_at);
            $date->setTimezone(new \DateTimeZone('EST'));
            $note->formatted_date = $date->diffForHumans().', '.$date->toFormattedDayDateString();
        }
        return view("profile.journal", compact("notes"));
    }

    public function backButton() {
        //back button functionality - get route, forget key, redirect

        //if from admin page, get separate session variable
        $prev_path = parse_url(url()->previous(), PHP_URL_PATH);
        if (Str::startsWith($prev_path, '/admin')) {
            $backRoute = Session::get("admin_back_route");
            Session::forget("admin_back_route");
        }
        else {
            $backRoute = Session::get("back_route");
            Session::forget("back_route");
        }
        return redirect()->to($backRoute);
    }

    public function profilePage()
    {
        //set nav bar buttons
        $showBackBtn = true;
        $hideProfileLink = true;
        //if returning from profile submission or admin page, do not reset back_route
        $prev_path = parse_url(url()->previous(), PHP_URL_PATH);
        if ($prev_path != "/profile" && !Str::startsWith($prev_path, '/admin')) {
            Session::put("back_route", url()->previous());
        }
        return view("profile.accountInformation", compact("showBackBtn", "hideProfileLink"));
    }

    public function getModulesList() {
        //get list of modules
        $modules = Module::orderBy('module_number', 'asc')->get();

        //get associated lessons for each module
        foreach ($modules as $module) {
            $lessons = Lesson::where('module_id', $module->id)
                                ->orderBy('lesson_number', 'asc')
                                ->select('id', 'title')
                                ->get();
            $module->lessons = $lessons;
        }
        return $modules;
    }

    public function exploreHomePage()
    {
        //get list of modules
        $modules = $this->getModulesList();
        //track explore page
        Session::put('last_explore_page', 'explore');
        return view("explore.home", compact('modules'));
    }

    public function exploreLesson($lessonId) {
        //set back_route
        $showBackBtn = true;
        Session::put("back_route", '/explore');
        //track explore page for browse button
        Session::put('last_explore_page', 'explore/'.$lessonId);
        //get the lesson info
        $lesson = Lesson::select('id', 'title')->find($lessonId);
        return view('explore.lesson', compact('showBackBtn', 'lessonId', 'lesson'));
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
