<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Note;
use App\Models\Content;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
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
                                ->select('id', 'title', 'order')
                                ->get();
            $module->lessons = $lessons;
        }
        return $modules;
    }

    public function exploreHome()
    {
        //get list of modules
        $modules = $this->getModulesList();
        //track explore page
        Session::put('last_explore_page', 'explore');
        return view("explore.home", compact('modules'));
    }

    public function exploreLesson($lessonId) {
        //get the lesson info
        $lesson = Lesson::findOrFail($lessonId);
        //adding prevention of url access to stop skipping - send to explore home
        if (Auth::user()->progress < $lesson->order) {
            return redirect()->to(route('explore.home'));
        }

        //set back_route
        $showBackBtn = true;
        Session::put("back_route", '/explore');
        //track explore page for browse button
        Session::put('last_explore_page', 'explore/'.$lesson->id);
        //get quizid
        $quizId = null;
        if ($lesson->end_behavior == 'quiz') {
            $quizId = Quiz::where('lesson_id', $lesson->id)->value('id');
        }
        //get content
        $extra = Content::where('lesson_id', $lesson->id)->get();
        $main = null;
        //filtering main
        foreach ($extra as $key => $item) {
            if ($item->main) {
                $main = $item;
                //remove main
                $extra->forget($key);
                break;
            }
        }

        if ($extra->count() == 0) {
            $extra = null;
        }

        return view('explore.lesson', compact('showBackBtn', 'lessonId', 'lesson', 'quizId', 'main', 'extra'));
    }

    public function exploreQuiz($quizId) {
        //quiz info
        $quiz = Quiz::findOrFail($quizId);
        $showBackBtn = true;
        //set routes for browse and back buttons
        Session::put("back_route", '/explore/'.$quiz->lesson_id);
        Session::put('last_explore_page', 'explore/quiz/'.$quizId);
        //get activity title
        $activityTitle = Lesson::find($quiz->lesson_id)->value('title');
        return view('explore.quiz', compact('showBackBtn', 'quiz', 'activityTitle'));
    }

    public function submitQuiz(Request $request, $quizId)
    {
        //get quiz and chceck answer
        $quiz = Quiz::find($quizId);
        $selectedOption = intval($request->answer);
        $isCorrect = $quiz->correct_answer == $selectedOption+1;
        $feedback = null;
        
        //convert options and get feedback
        $options = $quiz->options_feedback ?? [];
        $feedback = $options[$selectedOption]['feedback'];

        return redirect()->back()->with([
            'feedback' => $feedback,
            'is_correct' => $isCorrect
        ])->withInput();
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
