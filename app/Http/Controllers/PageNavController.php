<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Note;
use App\Models\Activity;
use App\Models\Module;
use App\Models\Faq;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
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

    //EXPLORE
    public function exploreHome()
    {
        //handle navigation
        Session::put('current_nav', ['route' => route('explore.home'), 'back' => 'Home']);
        Session::put('previous_explore', route('explore.home'));

        //get progress
        $module_progress = Auth::user()->progress_module;

        //get modules
        $modules = Module::orderBy('order', 'asc')->get();
        return view("explore.home", compact('modules', 'module_progress'));
    }

    public function exploreModule($module_id)
    {
        
        //find the module
        $module = Module::with('days.activities')->findOrFail($module_id);
        //check progress
        if (Auth::user()->progress_module < $module->order) {
            return redirect()->back();
        }
        
        //get progress
        $day_progress = Auth::user()->progress_day;
        $activity_progress = Auth::user()->progress_activity;
        
        //sorting the activities
        foreach ($module->days as $day) {
            $day->activities = $day->activities->sortBy(function ($activity) {
                return [$activity->optional, $activity->order];
            })->values();
            if ($day->order == $day_progress) {
                $day->show = true;
            }
        }

        //set back route
        $back_label = " Back to Home";
        $back_route = route('explore.home');

        //handle navigation
        Session::put('current_nav', ['route' => route('explore.module', ['module_id' => $module_id]), 'back' => 'Module '.$module_id]);
        Session::put('previous_explore', route('explore.module', ['module_id' => $module_id]));
        
        return view("explore.module", compact('module', 'day_progress', 'activity_progress', 'back_label', 'back_route'));
    }

    public function exploreActivity($activity_id, Request $request)
    {
        //find activity
        $activity = Activity::findOrFail($activity_id);
        //check progress
        $progress = Auth::user()->progress_activity;
        if ($progress < $activity->order) {
            return redirect()->back();
        }

        //get content
        $content = $activity->content;
        
        //favoriting
        $user = Auth::user();
        $is_favorited = $user->favorites()->where('activity_id', $activity_id)->exists();
        
        //setting exit button
        $exit = Session::get('current_nav');
        $exit_route = $exit ? $exit['route'] : route('explore.home');
        
        //end behavior
        if ($activity->end_behavior == 'quiz' && $activity->quiz) {
            $redirect_label = "QUIZ";
            $redirect_route = route('explore.quiz', ['quiz_id' => $activity->quiz->id]);
        }
        else if ($activity->next) {
            $redirect_label = "NEXT";
            $redirect_route = route('explore.activity', ['activity_id' => $activity->next]);
        }
        else {
            $redirect_label = "FINISH";
            $redirect_route = $exit_route;
        }

        //setting back route
        $back_label = $exit ? ' Back to '.$exit['back'] : ' Back';
        $back_route = $exit_route;

        $hide_bottom_nav = true;

        //journal case
        if ($activity->end_behavior == "journal") {
            $journal_label = "JOURNAL";
            $journal_route = route('journal', ['activity_id' => $activity->id]);
            return view("explore.activity", compact('activity', 'progress', 'content', 'is_favorited', 'redirect_label', 'redirect_route', 'back_label', 'back_route', 'exit_route', 'hide_bottom_nav', 'journal_label', 'journal_route'));
        }
        return view("explore.activity", compact('activity', 'progress', 'content', 'is_favorited', 'redirect_label', 'redirect_route', 'back_label', 'back_route', 'exit_route', 'hide_bottom_nav'));
    }
    
    //QUIZ
    public function exploreQuiz($quiz_id) {
        //find quiz
        $quiz = Quiz::findOrFail($quiz_id);

        //check progress
        if (Auth::user()->progress_activity <= $quiz->activity->order) {
            return redirect()->back();
        }

        //see if an answer is saved
        $saved_answer = Session::get('saved_answer');
        if ($saved_answer && $saved_answer['quiz_id'] != $quiz_id) {
            Session::forget('saved_answer');
            $saved_answer = null;
        }

        //setting exit route
        $exit = Session::get('current_nav');
        $exit_route = $exit ? $exit['route'] : route('explore.home');

        //set the end behavior
        if ($quiz->activity->next) {
            $redirect_label = "NEXT";
            $redirect_route = route('explore.activity', ['activity_id' => $quiz->activity->next]);
        }
        else {
            $redirect_label = "FINISH";
            $redirect_route = $exit;
        }

        //setting back route/label
        $back_label = ' Back to '.$quiz->activity->title;
        $back_route = route('explore.activity', ['activity_id' => $quiz->activity_id]);

        $hide_bottom_nav = true;
        return view('explore.quiz', compact('quiz', 'saved_answer', 'redirect_label', 'redirect_route', 'back_label', 'back_route', 'exit_route', 'hide_bottom_nav'));
    }

    public function submitQuiz(Request $request)
    {
        //get quiz and check answer
        $quiz = Quiz::find($request->quiz_id);
        $selected_option = intval($request->answer);
        $is_correct = $quiz->correct_answer == $selected_option+1;
        $feedback = null;
        
        //convert options and get feedback
        $options = $quiz->options_feedback ?? [];
        $feedback = $options[$selected_option]['feedback'];

        //save answer in case returned to this page soon
        Session::put('saved_answer', ['quiz_id' => $request->quiz_id, 'answer' => $selected_option]);

        return redirect()->back()->with([
            'feedback' => $feedback,
            'is_correct' => $is_correct
            ])->withInput();
    }

    public function exploreBrowseButton(Request $request) {
        //browse nav button - check for previous explore page
        //active is for double click functionality
        $previous = Session::get('previous_explore');
        if ($previous && !$request->active) {
            return redirect()->to($previous);
        }
        else {
            return redirect()->route('explore.home');
        }
    }

    //LIBRARIES
    public function library(Request $request) {
        //library nav button - check for previous library
        $previous = Session::get('previous_library');
        if ($previous) {
            return redirect()->to($previous);
        }
        else {
            return redirect()->route('library.meditation');
        }
    }

    public function favoritesLibrary()
    {
        //get users favorites and sort by activity order
        $favorites = Auth::user()->favorites()->with('activity')->get();
        $activities = $favorites->pluck('activity')->sortBy('order');
        $page_info = [
            'title' => 'Favorites',
            'empty' => '<span>Click the "<i class="bi bi-star"></i>" on lessons add them to your favorites and view them here!</span>',
        ];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.favorites'));
        Session::put('current_nav', ['route' => route('library.favorites'), 'back' => 'Favorites']);
        return view('other.library', compact('page_info', 'activities'));
    }
    public function meditationLibrary()
    {
        $progress = Auth::user()->progress_activity;
        $activities = Activity::where('order', '<', $progress)->where('type', 'practice')->orderBy('order', 'asc')->get();
        $page_info = [
            'title' => 'Meditation Library',
            'empty' => 'Keep progressing to unlock meditation sessions...',
        ];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.meditation'));
        Session::put('current_nav', ['route' => route('library.meditation'), 'back' => 'Meditation Library']);
        return view("other.library", compact('page_info', 'activities'));
    }
    
    public function journalPage(Request $request)
    {
        //check if coming from an activity
        $activity_id = $request->activity_id;
        if ($activity_id) {
            $back_label = ' Back to '.Activity::findOrFail($activity_id)->title;
            $back_route = route('explore.activity', ['activity_id' => $activity_id]);
            $hide_bottom_nav = true;
            return view("other.journal", compact('activity_id', 'back_label', 'back_route', 'hide_bottom_nav'));
        }

        //otherwise normal notes page
        //get user
        $id = Auth::id();
        $notes = Note::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        //formatting the date
        foreach ($notes as $note) {
            $date = Carbon::parse($note->created_at);
            $date->setTimezone(new \DateTimeZone('EST'));
            $note->formatted_date = $date->diffForHumans().', '.$date->toFormattedDayDateString();
        }
        return view("other.journal", compact('notes'));
    }
    
    public function accountPage()
    {
        $hide_account_link = true;

        //calculating progress
        $modules = Module::orderBy('module_number', 'asc')->withCount('days')->get();
        $module_progress = Auth::user()->progress_module;
        $day_progress = Auth::user()->progress_day - 1;
        foreach ($modules as $module) {
            if ($module_progress > $module->order) {
                $module->progress = $module->days()->count();
                $day_progress -= $module->progress;
            }
            else if ($module_progress == $module->order) {
                $module->progress = $day_progress;
                $day_progress = 0;
            }
            else {
                $module->progress = 0;
            }
        }
        return view("other.account", compact('hide_account_link', 'modules'));
    }

    public function helpPage()
    {
        $faqs = Faq::all();
        return view("other.help", compact('faqs'));
    }
}
