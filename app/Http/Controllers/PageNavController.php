<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
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

    //EXPLORE
    public function exploreHome()
    {
        $modules = Module::orderBy('order', 'asc')->get();
        return view("explore.home", compact('modules'));
    }

    public function exploreModule($module_id)
    {
        $module = Module::findOrFail($module_id);
        return view("explore.module", compact('module'));
    }

    public function exploreActivity($activity_id)
    {
        $activity = Activity::findOrFail($activity_id);
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
            $redirect_route = route('explore.home');
        }
        return view("explore.activity", compact('activity', 'content', 'is_favorited', 'redirect_label', 'redirect_route'));
    }
    
    //QUIZ
    public function exploreQuiz($quiz_id) {
        $quiz = Quiz::findOrFail($quiz_id);
        if ($quiz->activity->next) {
            $redirect_label = "NEXT";
            $redirect_route = route('explore.activity', ['activity_id' => $quiz->activity->next]);
        }
        else {
            $redirect_label = "FINISH";
            $redirect_route = route('explore.home');
        }
        return view('explore.quiz', compact('quiz', 'redirect_label', 'redirect_route'));
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

    //LIBRARIES
    public function library()
    {
        if (Session::get('previous_library')) {
            return redirect()->route(Session::get('previous_library'));
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
        Session::put('previous_library', 'library.favorites');
        return view('other.library', compact('page_info', 'activities'));
    }
    public function meditationLibrary()
    {
        //TODO
        // Activity::where('order', '<', $progress)->orderBy('order', 'asc')->select('id', 'title', 'sub_header')->get();
        $activities = Activity::where('type', 'practice')->orderBy('order', 'asc')->get();
        $page_info = [
            'title' => 'Meditation Library',
            'empty' => 'Keep progressing to unlock meditation sessions...',
        ];
        Session::put('previous_library', 'library.meditation');
        return view("other.library", compact('page_info', 'activities'));
    }



    

    //TODO
    // public function journalPage(Request $request)
    // {
    //     $showBackBtn = false;
    //     $activity = null;
        
    //     //if we are redirected here from an activity
    //     if ($request->activity) {
    //         $showBackBtn = true;
    //         Session::put("back_route", url()->previous());
    //         $activity = Lesson::find($request->activity)->title;
    //     }
    //     //get user
    //     $id = Auth::id();
    //     $notes = Note::where('user_id', $id)->orderBy('created_at', 'desc')->get();
    //     //formatting the date
    //     foreach ($notes as $note) {
    //         $date = Carbon::parse($note->created_at);
    //         $date->setTimezone(new \DateTimeZone('EST'));
    //         $note->formatted_date = $date->diffForHumans().', '.$date->toFormattedDayDateString();
    //     }
    //     return view("profile.journal", compact('notes', 'showBackBtn', 'activity'));
    // }

    // public function backButton(Request $request) {
    //     //back button functionality - get route, forget key, redirect

    //     //if from admin page, get separate session variable
    //     $prev_path = parse_url(url()->previous(), PHP_URL_PATH);
    //     if (Str::startsWith($prev_path, '/admin')) {
    //         $backRoute = Session::get("admin_back_route");
    //         Session::forget("admin_back_route");
    //     }
    //     else {
    //         $backRoute = Session::get("back_route");
    //         //case where back is selected on explore page that was reached by favorites
    //         if ($backRoute == "/favorites" && $request->from_back = 'explore.lesson') {
    //             Session::put('last_explore_page', 'explore');
    //         }
    //         Session::forget("back_route");
    //     }
    //     return redirect()->to($backRoute);
    // }

    // public function profilePage()
    // {
    //     //set nav bar buttons
    //     $showBackBtn = true;
    //     $hideProfileLink = true;
    //     //if returning from profile submission or admin page, do not reset back_route
    //     $prev_path = parse_url(url()->previous(), PHP_URL_PATH);
    //     if ($prev_path != "/profile" && !Str::startsWith($prev_path, '/admin')) {
    //         Session::put("back_route", url()->previous());
    //     }

    //     //calculating progress
    //     $modules = Module::orderBy('module_number', 'asc')->get();
    //     $progress = Auth::user()->progress;
    //     foreach ($modules as $module) {
    //         if ($progress >= $module->lesson_count) {
    //             $module->progress = $module->lesson_count;
    //             $progress -= $module->lesson_count;
    //         }
    //         else if ($progress > 0) {
    //             $module->progress = $progress;
    //             $progress = 0;
    //         }
    //         else {
    //             $module->progress = 0;
    //         }
    //     }
    //     return view("profile.accountInformation", compact("showBackBtn", "hideProfileLink", 'modules'));
    // }

    // public function exploreHomeOld()
    // {
    //     //get list of modules
    //     $modules = $this->getModulesList();
    //     //track explore page
    //     Session::put('last_explore_page', 'explore');
    //     return view("explore.homeOriginal", compact('modules'));
    // }

    // public function exploreLesson(Request $request, $lessonId) {
    //     //get the lesson info
    //     $lesson = Lesson::findOrFail($lessonId);
    //     //adding prevention of url access to stop skipping - send to explore home
    //     if (Auth::user()->progress < $lesson->order) {
    //         return redirect()->to(route('explore.home'));
    //     }

    //     //set back_route
    //     $showBackBtn = true;
    //     $from_fav = false;
    //     //from refereces where the button was clicked
    //     if (isset($request->from) && $request->from = 'fav') {
    //         //if button was accessed on the favorites page, that is where back should lead
    //         Session::put("back_route", '/favorites');
    //         $from_fav = true;
    //     }
    //     else {
    //         Session::put("back_route", '/explore');
    //     }
    //     //track explore page for browse button
    //     Session::put('last_explore_page', 'explore/'.$lesson->id);
    //     //get quizid
    //     $quizId = $lesson->end_behavior == 'quiz' && $lesson->quiz ? $lesson->quiz : null;
    //     //get content
    //     $content = $lesson->content;
    //     $extra = $content->where('main', false);
    //     $main = $content->where('main', true);
    //     $main = $main->sortBy('id');

    //     //favorited?
    //     $user = Auth::user();
    //     $isFavorited = $user->favorites()->where('lesson_id', $lessonId)->exists();

    //     //get next lesson
    //     $next = Lesson::where('order', $lesson->order + 1)->value('id');

    //     return view('explore.lesson', compact('showBackBtn', 'lessonId', 'lesson', 'quizId', 'main', 'extra', 'isFavorited', 'next', 'from_fav'));
    // }

    // public function exploreBrowseButton() {
    //     //double click functionality - if clicking browse while on an explore page
    //     if (Str::startsWith(parse_url(url()->previous(), PHP_URL_PATH), '/explore')) {
    //         return redirect()->route('explore.home');
    //     }

    //     //check session for last used explore page - resume on this page
    //     $lastExplorePage = Session::get('last_explore_page');

    //     if ($lastExplorePage && Str::startsWith($lastExplorePage, 'explore/')) {
    //         return redirect()->to($lastExplorePage);
    //     } else {
    //         return redirect()->route('explore.home');
    //     }
    // }
}
