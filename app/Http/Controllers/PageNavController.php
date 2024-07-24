<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Note;
use App\Models\Activity;
use App\Models\Module;
use App\Models\UserActivity;
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

        //get modules and progress
        $modules = Module::orderBy('order', 'asc')->get();
        $module_ids = $modules->pluck('id')->toArray();
        $progress = getModuleProgress(Auth::id(), $module_ids);
        
        foreach ($modules as $module) {
            $module->progress = $progress[$module->id];
        }
        return view("explore.home", compact('modules'));
    }

    public function exploreModule($module_id)
    {
        //find the module
        $module = Module::with('days.activities')->findOrFail($module_id);

        //check progress
        if (getModuleProgress(Auth::id(), [$module_id])[$module_id]['status'] == 'locked') {
            return redirect()->back();
        }
        
        //get progress
        $day_ids = $module->days()->pluck('id')->toArray();
        $progress = getDayProgress(Auth::id(), $day_ids);

        //sorting the activities within each day
        foreach ($module->days as $day) {
            $day->activities = $day->activities->sortBy(function ($activity) {
                return [$activity->optional, $activity->order];
            })->values();

            //assign the progress
            $day->progress = $progress[$day->id];
        }

        //set back route
        $page_info['back_label'] = " Back to Home";
        $page_info['back_route'] = route('explore.home');

        //handle navigation
        Session::put('current_nav', ['route' => route('explore.module', ['module_id' => $module_id]), 'back' => 'Module '.$module_id]);
        Session::put('previous_explore', route('explore.module', ['module_id' => $module_id]));
        
        return view("explore.module", compact('module', 'page_info'));
    }

    public function exploreActivity($activity_id, Request $request)
    {
        $user = Auth::user();
        //find activity
        $activity = Activity::findOrFail($activity_id);
        //check progress and set status
        $user->load('progress_activities');
        $activity->status = $user->progress_activities->where('activity_id', $activity->id)->first()->status ?? 'locked';
        if ($activity->status == 'locked' || $activity->deleted == true) {
            return redirect()->back();
        }
        
        //get content
        $content = $activity->content;
        //decode the audio options
        if ($content && $content->type == 'audio' && $content->audio_options) {
            $content->audio_options = json_decode($content->audio_options, true);
        }
        
        //favoriting
        $is_favorited = $user->favorites()->where('activity_id', $activity_id)->exists();

        $page_info = [];
        
        //setting exit button
        $exit = Session::get('current_nav');
        $page_info['exit_route'] = $exit ? $exit['route'] : route('explore.home');
        
        //NEXT/FINISH redirect
        //make sure that if doing next, the day is not changing
        if (!$request->library) {
            if ($activity->next && Activity::find($activity->next)->day->id == $activity->day->id) {
                $page_info['redirect_label'] = "NEXT";
                $page_info['redirect_route'] = route('explore.activity', ['activity_id' => $activity->next]);
            }
            else {
                $page_info['redirect_label'] = "FINISH";
                $page_info['redirect_route'] = $page_info['exit_route'];
            }
        }

        //setting back route
        $page_info['back_label'] = $exit ? ' Back to '.$exit['back'] : ' Back';
        $page_info['back_route'] = $page_info['exit_route'];

        $page_info['hide_bottom_nav'] = true;

        //end behavior - for activities
        if ($activity->end_behavior != 'none') {
            if ($activity->end_behavior == "journal") {
                $page_info['end_label'] = "JOURNAL";
                $page_info['end_route'] = route('journal', ['activity_id' => $activity->id, 'library' => $request->library]);
            }
            else if ($activity->end_behavior == "quiz" && $activity->quiz) {
                $page_info['end_label'] = "QUIZ";
                $page_info['end_route'] = route('explore.quiz', ['quiz_id' => $activity->quiz->id, 'library' => $request->library]);
            }
        }
        return view("explore.activity", compact('activity', 'content', 'is_favorited', 'page_info'));
    }
    
    //QUIZ
    public function exploreQuiz($quiz_id, Request $request) {
        //find quiz
        $quiz = Quiz::findOrFail($quiz_id);
        
        //check progress
        $user = Auth::user();
        $user->load('progress_activities');
        $activity = Activity::findOrFail($quiz->activity->id);
        $status = $user->progress_activities->where('activity_id', $activity->id)->first()->status ?? 'locked';
        if ($status != 'completed'  || $activity->deleted == true) {
            return redirect()->back();
        }

        //see if an answer is saved
        $saved_answer = Session::get('saved_answer');
        if ($saved_answer && $saved_answer['quiz_id'] == $quiz_id) {
            //convert options and get feedback
            $options = $quiz->options_feedback ?? [];
            //passing information through quiz
            $quiz->saved_answer = $saved_answer['answer'];
            $quiz->feedback = $options[$saved_answer['answer']]['feedback'];
            $quiz->is_correct = $saved_answer['correct'];
        }
        else {
            Session::forget('saved_answer');
            $quiz->saved_answer = null;
        }

        $page_info = [];

        //setting exit route
        $exit = Session::get('current_nav');
        $page_info['exit_route'] = $exit ? $exit['route'] : route('explore.home');

        //set the end behavior - next only on same day
        if (!$request->library) {
            if ($quiz->activity->next && Activity::find($quiz->activity->next)->day->id == $quiz->activity->day->id) {
                $page_info['redirect_label'] = "NEXT";
                $page_info['redirect_route'] = route('explore.activity', ['activity_id' => $quiz->activity->next]);
            }
            else {
                $page_info['redirect_label'] = "FINISH";
                $page_info['redirect_route'] = $exit;
            }
        }

        //setting back route/label
        $page_info['back_label'] = ' Back to '.$quiz->activity->title;
        $page_info['back_route'] = route('explore.activity', ['activity_id' => $quiz->activity_id, 'library' => $request->library]);
        
        $page_info['hide_bottom_nav'] = true;
        return view('explore.quiz', compact('quiz', 'page_info'));
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
        Session::put('saved_answer', ['quiz_id' => $request->quiz_id, 'answer' => $selected_option, 'correct' => $is_correct]);
        
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

    public function libraryFilter($query, Request $request) {
        //check if first query has rows
        $first_empty = !$query->exists();

        //search/filter flag
        $is_search = false;

        //handle search
        if ($request->has('search') && $request->search != '' && !$first_empty) {
            $is_search = true;
            $query->where(function ($in_query) use ($request) {
                $in_query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('sub_header', 'like', '%' . $request->search . '%');
            });
        }
        
        //handle categories
        $categories = $request->input('category', []);
        if (!empty($categories)) {
            $is_search = true;
            //filter based on the categories
            foreach($categories as $category) {
                $lower = strtolower($category);
                if ($lower == 'audio' || $lower == 'video') {
                    $query->whereHas('content', function ($in_query) use ($lower) {
                        $in_query->where('type', $lower);
                    });
                }
                else if ($lower == 'favorited') {
                    $fav_ids = Auth::user()->favorites()->with('activity')->pluck('activity_id');
                    $query->whereIn('id', $fav_ids);
                }
                else if ($lower == 'meditation') {
                    $query->where('type', 'practice');
                }
                else if ($lower == 'optional') {
                    $query->where('optional', true);
                }
                else if ($lower == 'quiz') {
                    $query->where('end_behavior', 'quiz');
                }
            }
        }

        //handle modules
        $module_ids = $request->input('module', []);
        if (!empty($module_ids)) {
            $is_search = true;
            //filter based on the module ids
            $query->whereHas('day.module', function ($in_query) use ($module_ids) {
                $in_query->whereIn('id', $module_ids);
            });
        }
        
        //handle time
        if ($request->has(['start_time', 'end_time']) && ($request->start_time != 0 || $request->end_time != 30)) {
            $is_search = true;
            $start = $request->start_time;
            $end = $request->end_time;
            $query->where('time', '!=', null)->whereRaw("
                EXISTS (
                    SELECT 1
                    FROM json_each(activities.time)
                    WHERE CAST(json_each.value AS INTEGER) BETWEEN ? AND ?
                )
            ", [$start, $end]);
        }

        $activities = $query->orderBy('order')->paginate(6);

        return [$first_empty, $is_search, $activities];
    }

    public function favoritesLibrary(Request $request)
    {
        //get users favorites and sort by activity order
        $favorite_ids = Auth::user()->favorites()->with('activity')->pluck('activity_id');
        $query = Activity::whereIn('id', $favorite_ids)
            ->where('deleted', false);

        //call search/filter function
        $results = $this->libraryFilter($query, $request);
        $first_empty = $results[0];
        $is_search = $results[1];
        $activities = $results[2];

        $page_info = [
            'title' => 'Favorites',
            'first_empty' => $first_empty,
            'empty' => !$is_search ? '<span>Click the "<i class="bi bi-star"></i>" on lessons add them to your favorites and view them here!</span>' : 'No activities found.',
            'search_route' => route('library.favorites'),
            'search_text' => 'Search for your favorite activity...'
        ];

        $categories = ['Meditation', 'Audio', 'Video', 'Quiz', 'Optional'];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.favorites'));
        Session::put('current_nav', ['route' => route('library.favorites'), 'back' => 'Favorites']);
        return view('other.library', compact('page_info', 'activities', 'categories'));
    }
    public function meditationLibrary(Request $request)
    {
        $user_id = Auth::id();
        //query for activities - keep as query
        $activity_ids = UserActivity::where('user_id', $user_id)
            ->where('status', '!=', 'locked')
            ->pluck('activity_id');
        $query = Activity::where('deleted', false)
            ->where('type', 'practice')
            ->whereIn('id', $activity_ids);

        //call search/filter function
        $results = $this->libraryFilter($query, $request);
        $first_empty = $results[0];
        $is_search = $results[1];
        $activities = $results[2];
        
        $page_info = [
            'title' => 'Meditation Library',
            'first_empty' => $first_empty,
            'empty' => !$is_search ? 'Keep progressing to unlock more meditation sessions...' : 'No activities found.',
            'search_route' => route('library.meditation'),
            'search_text' => 'Search for a meditation exercise...'
        ];

        $categories = ['Favorited', 'Audio', 'Video', 'Quiz', 'Optional'];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.meditation'));
        Session::put('current_nav', ['route' => route('library.meditation'), 'back' => 'Meditation Library']);
        return view("other.library", compact('page_info', 'activities', 'categories'));
    }
    
    public function journalPage(Request $request)
    {
        $page_info = [];

        //check if coming from an activity
        $activity_id = $request->activity_id;
        if ($activity_id) {
            $page_info['back_label'] = ' Back to '.Activity::findOrFail($activity_id)->title;
            $page_info['back_route'] = route('explore.activity', ['activity_id' => $activity_id, 'library' => $request->library]);
            $page_info['hide_bottom_nav'] = true;
            return view("other.journal", compact('activity_id', 'page_info'));
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
        return view("other.journal", compact('notes', 'page_info'));
    }
    
    public function accountPage()
    {
        $page_info = [];
        $page_info['hide_account_link'] = true;

        //calculating progress
        $modules = Module::orderBy('order', 'asc')->get();
        $progress = getModuleProgress(Auth::id(), $modules->pluck('id')->toArray());
        foreach ($modules as $module) {
            $module->progress = $progress[$module->id];
        }
        return view("other.account", compact('page_info', 'modules'));
    }

    public function helpPage()
    {
        $faqs = Faq::all();
        return view("other.help", compact('faqs'));
    }
}
