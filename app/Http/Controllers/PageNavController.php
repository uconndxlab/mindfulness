<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Note;
use App\Models\Content;
use App\Models\Activity;
use App\Models\Module;
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

    //function to update the routes saved in session for navbar items
    public function updateNavButtons($route) {
        //check which nav item we are coming from
        $current_nav = Session::get('current_nav');
        if ($current_nav) {
            //update the route that nav bar goes to
            if ($current_nav == 'explore.home') {
                Session::put('explore_nav', $route);
            }
            else {
                Session::put('library_nav', $route);
            }
        }
    }

    //EXPLORE
    public function exploreHome()
    {
        //MUST be on browse to reach this page
        Session::put('current_nav', 'explore.home');
        Session::put('explore_nav', route('explore.home'));

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

        //MUST be on browse to reach this page
        Session::put('current_nav', 'explore.home');
        Session::put('explore_nav', route('explore.module', ['module_id' => $module_id]));

        //sorting the activities
        foreach ($module->days as $day) {
            $day->activities = $day->activities->sortBy(function ($activity) {
                return [$activity->optional, $activity->order];
            })->values();
        }

        //set back route
        $back_route = route('explore.home');
        return view("explore.module", compact('module', 'day_progress', 'activity_progress', 'back_route'));
    }

    public function exploreActivity($activity_id)
    {
        //find activity
        $activity = Activity::findOrFail($activity_id);
        //check progress
        $progress = Auth::user()->progress_activity;
        if ($progress < $activity->order) {
            return redirect()->back();
        }

        //update the current nav item with the activity
        $this->updateNavButtons(route('explore.activity', ['activity_id' => $activity_id]));

        //get content
        $content = $activity->content;
        
        //favoriting
        $user = Auth::user();
        $is_favorited = $user->favorites()->where('activity_id', $activity_id)->exists();

        //end behavior
        if ($activity->end_behavior == 'quiz') {
            $redirect_label = "QUIZ";
            $redirect_route = route('explore.quiz', ['quiz_id' => $activity->quiz->id]);
        }
        else if ($activity->end_behavior == "journal") {
            $redirect_label = "JOURNAL";
            $redirect_route = route('journal', ['activity' => $activity->id]);
        }
        else if ($activity->next) {
            $redirect_label = "NEXT";
            $redirect_route = route('explore.activity', ['activity_id' => $activity->next]);
        }
        else {
            $redirect_label = "FINISH";
            $redirect_route = route('explore.module', ['module_id' => $activity->day->module_id]);
        }

        //setting the back route/label
        if (Session::get('current_nav') == 'explore.home') {
            $back_label = ' to '.$activity->day->module->name;
            $back_route = route('explore.module', ['module_id' => $activity->day->module_id]);
        }
        else {
            $back_label = ' to Library';
            $prev_lib = Session::get('previous_library');
            $back_route = $prev_lib ? route($prev_lib) : route('library.meditation');
        }

        //setting exit button
        $exit = Session::get('current_nav');
        $exit_route = $exit ? route($exit) : route('explore.home');
        return view("explore.activity", compact('activity', 'progress', 'content', 'is_favorited', 'redirect_label', 'redirect_route', 'back_label', 'back_route', 'exit_route'));
    }
    
    //QUIZ
    public function exploreQuiz($quiz_id) {
        //find quiz
        $quiz = Quiz::findOrFail($quiz_id);
        //block if user has not finished the associated activity
        if (Auth::user()->progress_activity <= $quiz->activity->order) {
            return redirect()->back();
        }

        //update current nav item with the route
        $this->updateNavButtons(route('explore.quiz', ['quiz_id' => $quiz_id]));

        //set the end behavior
        if ($quiz->activity->next) {
            $redirect_label = "NEXT";
            $redirect_route = route('explore.activity', ['activity_id' => $quiz->activity->next]);
        }
        else {
            $redirect_label = "FINISH";
            $redirect_route = route('explore.home');
        }

        //setting back route/label
        $back_label = ' to '.$quiz->activity->title;
        $back_route = route('explore.activity', ['activity_id' => $quiz->activity_id]);

        //setting exit route
        $exit = Session::get('current_nav');
        $exit_route = $exit ? route($exit) : route('explore.home');
        return view('explore.quiz', compact('quiz', 'redirect_label', 'redirect_route', 'back_label', 'back_route', 'exit_route'));
    }

    public function submitQuiz(Request $request, $quiz_id)
    {
        //get quiz and chceck answer
        $quiz = Quiz::find($quiz_id);
        $selected_option = intval($request->answer);
        $is_correct = $quiz->correct_answer == $selected_option+1;
        $feedback = null;
        
        //convert options and get feedback
        $options = $quiz->options_feedback ?? [];
        $feedback = $options[$selected_option]['feedback'];

        return redirect()->back()->with([
            'feedback' => $feedback,
            'is_correct' => $is_correct
            ])->withInput();
    }
        
    public function exploreBrowseButton() {
        //set the current nav item
        Session::put('current_nav', 'explore.home');
        //check if there is a route saved, otherwise return home
        if (Session::get('explore_nav')) {
            return redirect()->to(Session::get('explore_nav'));
        }
        else {
            return redirect()->route('explore.home');
        }
    }

    //LIBRARIES
    public function library()
    {
        //find what the previous library was
        $library = Session::get('previous_library');
        //set the current nav item
        Session::put('current_nav', $library);
        //if there is a route saved, go to it
        if (Session::get('library_nav')) {
            return redirect()->to(Session::get('library_nav'));
        }
        //otherwise go to the previous library
        else if ($library) {
            return redirect()->route($library);
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

        //set as the previous library and save route
        Session::put('previous_library', 'library.favorites');
        Session::put('library_nav', route('library.favorites'));
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

        //set as the previous library and save route
        Session::put('previous_library', 'library.meditation');
        Session::put('library_nav', route('library.meditation'));
        return view("other.library", compact('page_info', 'activities'));
    }
    
    public function journalPage(Request $request)
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
        return view("other.journal", compact('notes'));
    }
    
    public function profilePage()
    {
        $hide_profile_link = true;

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
        return view("other.profile", compact('hide_profile_link', 'modules'));
    }
}
