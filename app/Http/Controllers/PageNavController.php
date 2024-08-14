<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Note;
use App\Models\Activity;
use App\Models\Module;
use App\Models\QuizAnswers;
use App\Models\UserActivity;
use App\Models\Faq;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
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

    public function checkActivityLocked($activity_id, $from_controller = false) {
        //checking cache for progress
        $cacheKey = 'user_' . Auth::id() . '_progress_activities';
        $progress = Cache::get($cacheKey);
        if (!$progress) {
            $progress = Cache::remember($cacheKey, 30, function () {
                return Auth::user()->load('progress_activities')->progress_activities;
            });
        }
        $activity = Activity::findOrFail($activity_id);
        $status = $progress->where('activity_id', $activity_id)->first()->status ?? 'locked';
        //check if deleted
        if ($activity->deleted == true) {
            abort(404, "Page not found.");
        }
        //check status
        $locked = $status === 'locked';
        if ($from_controller) {
            return [$locked, $status];
        }
        return response()->json(['locked' => $locked, 'status' => $status]);
    }

    public function exploreActivity($activity_id, Request $request)
    {
        $user = Auth::user();
        //find activity and check progress again
        $activity = Activity::findOrFail($activity_id);
        $check_activity = $this->checkActivityLocked($activity_id, true);
        $activity->status = $check_activity[1];
        if ($check_activity[0]) {
            abort(404, "Page not found.");
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
    
        //get content
        $content = $activity->content;
        $quiz = $activity->quiz;
        $journal = $activity->journal;
        if ($quiz) {
            $quiz->question_options = json_decode($quiz->question_options, true);
            $temp_answers = $user->quiz_answers($quiz->id)->first();
            $quiz->answers = $temp_answers ? json_decode($temp_answers->answers) : [];
        }
        //decode the audio options
        else if ($content && $content->type == 'audio' && $content->audio_options) {
            $content->audio_options = json_decode($content->audio_options, true);
        }
        else if ($journal) {
            $temp_answer = $user->notes->where('activity_id', $activity->id)->first();
            $journal->answer = $temp_answer ? $temp_answer->note : '';
        }
        
        return view("explore.activity", compact('activity', 'is_favorited', 'page_info', 'content', 'quiz', 'journal'));
    }

    //QUIZ
    public function submitQuiz(Request $request)
    {
        try {
            //get quiz
            $quiz = Quiz::findOrFail($request->quiz_id);
    
            //get answers from request
            $answers = [];
            foreach ($request->all() as $key => $value) {
                if (Str::startsWith($key, 'answer_') || Str::startsWith($key, 'other_answer_')) {
                    $answers[$key] = $value;
                }
            }
    
            QuizAnswers::updateOrCreate([
                'user_id' => Auth::id(),
                'quiz_id' => $quiz->id
            ], [
                'answers' => json_encode($answers)
            ]);
            return response()->json(['success_message' => 'Quiz answers updated successfully.'], 200);
        }
        catch (\Exception $e) {
            return response()->json(['error_message' => 'Failed to submit quiz answers.', 'error' => $e], 500);
        }
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

    public function librarySearch(Request $request) {

        //get query of unlocked activities
        $user_id = Auth::id();
        //query for activities - keep as query
        $activity_ids = UserActivity::where('user_id', $user_id)
        ->where('status', '!=', 'locked')
        ->pluck('activity_id');
        $query = Activity::where('deleted', false)
        ->whereIn('id', $activity_ids);
        
        //base param
        $empty_text = null;
        if ($request->base_param) {
            if ($request->base_param == 'meditation') {
                $query->where('type', 'practice');
                $empty_text = 'Keep progressing to unlock more meditation sessions...';
            }
            else if ($request->base_param = 'favorited') {
                $fav_ids = Auth::user()->favorites()->with('activity')->pluck('activity_id');
                $query->whereIn('id', $fav_ids);
                $empty_text = '<span>Click the "<i class="bi bi-star"></i>" found in activities to add them to your favorites and view them here!</span>';
            }
        }

        //check if empty
        $empty = !$query->exists();
        if ($empty) {
            $view = view('components.search-results', ['empty_text' => $empty_text])->render();
            return response()->json(['html' => $view]);
        }

        //pulling random item
        $query_clone = clone $query;
        $random_act = $query_clone->inRandomOrder()->first();
        
        //handle search
        if ($request->has('search') && $request->search != '') {
            $query->where(function($in_query) use ($request) {
                $in_query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('type', 'like', '%' . $request->search . '%')
                    ->orWhere('time', 'like', '%' . $request->search . '%');
            });
        }
        
        //handle categories
        $categories = $request->input('category', []);
        if (!empty($categories)) {
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
                else if ($lower == 'journal') {
                    $query->where('type', 'journal');
                }
                else if ($lower == 'optional') {
                    $query->where('optional', true);
                }
                else if ($lower == 'quiz') {
                    $query->where('type', 'reflection');
                }
            }
        }
        
        //handle modules
        $module_ids = $request->input('module', []);
        if (!empty($module_ids)) {
            //filter based on the module ids
            $query->whereHas('day.module', function ($in_query) use ($module_ids) {
                $in_query->whereIn('id', $module_ids);
            });
        }
        
        //handle time
        if ($request->has(['start_time', 'end_time']) && ($request->start_time != 0 || $request->end_time != 30)) {
            $start = $request->start_time;
            $end = $request->end_time;
            // $query->where('time', '!=', null)->whereRaw("
            // EXISTS (
            //     SELECT 1
            //     FROM json_each(activities.time)
            //     WHERE CAST(json_each.value AS INTEGER) BETWEEN ? AND ?
            //     )
            //     ", [$start, $end]);
            $query->where('time', '<=', $end)->where('time', '>=', $start);
        }
            
        $activities = $query->with('day.module')->orderBy('order')->paginate(5);
        $view = view('components.search-results', ['activities' => $activities, 'random' => $random_act])->render();

        return response()->json(['html' => $view]);
    }

    public function favoritesLibrary(Request $request)
    {
        $base_param = 'favorited';

        $page_info = [
            'title' => 'Favorites',
            'search_route' => route('library.favorites'),
            'search_text' => 'Search for your favorite activity...'
        ];

        $categories = ['Meditation', 'Audio', 'Video', 'Quiz', 'Journal', 'Optional'];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.favorites'));
        Session::put('current_nav', ['route' => route('library.favorites'), 'back' => 'Favorites']);
        return view('other.library', compact('base_param', 'page_info', 'categories'));
    }
    public function meditationLibrary(Request $request)
    {
        $base_param = 'meditation';

        $page_info = [
            'title' => 'Meditation Library',
            'search_route' => route('library.meditation'),
            'search_text' => 'Search for a meditation exercise...'
        ];

        $categories = ['Favorited', 'Audio', 'Video', 'Quiz', 'Journal', 'Optional'];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.meditation'));
        Session::put('current_nav', ['route' => route('library.meditation'), 'back' => 'Meditation Library']);
        return view("other.library", compact('base_param', 'page_info', 'categories'));
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
        $notes = Note::where('user_id', $id)->orderBy('created_at', 'desc')->paginate(5);
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
