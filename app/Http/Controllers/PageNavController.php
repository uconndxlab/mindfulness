<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Quiz;
use App\Models\Note;
use App\Models\Activity;
use App\Models\Module;
use App\Models\QuizAnswers;
use App\Models\Teacher;
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

    public function exploreModule($module_id, $accordion_day=null)
    {
        //find the module
        $module = Module::with('days.activities')->findOrFail($module_id);
        
        //check progress
        $mod_progress = getModuleProgress(Auth::id(), [$module_id]);
        $module->progress_days = [$mod_progress[$module_id]['completed'], $mod_progress[$module_id]['total']];
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
        // $accordion_day = 1;
        $override_accordion = $accordion_day ? 'day_'.$accordion_day : null;

        //set back route
        $page_info['back_label'] = " Back to Home";
        $page_info['back_route'] = route('explore.home');

        //handle navigation
        Session::put('current_nav', ['route' => route('explore.module', ['module_id' => $module_id]), 'back' => 'Part '.$module_id]);
        Session::put('previous_explore', route('explore.module', ['module_id' => $module_id]));
        
        return view("explore.module", compact('module', 'page_info', 'override_accordion'));
    }

    public function exploreModuleBonus(Request $request, $module_id) {
        $accordion_day = $request->day ?? null;
        return $this->exploreModule($module_id, $accordion_day);
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

        // check if locked
        if ($locked) {
            return response()->json(['locked' => true, 'modalContent' => [
                'label' => 'Activity Locked: '.$activity->title,
                'body' => 'This activity is currently locked. Continue progressing to unlock this activity.'
            ]]);
        }

        // check if activity is blocked by day completion
        $user = Auth::user();
        $lastCompleteTime = $user->last_day_completed_at;
        $last_day_name = $user->last_day_name;
        $blockNextDayAct = $user->block_next_day_act;

        // check if this activity is blocked
        if ($blockNextDayAct && $blockNextDayAct == $activity->id && $lastCompleteTime) {
            // get local times
            $lastCompletionLocal = Carbon::parse($lastCompleteTime)->setTimezone($user->timezone ?? config('app.timezone'));
            $now = now()->setTimezone($user->timezone ?? config('app.timezone'));

            // if it is not yet the next day, return modal content (or less than two hours)
            if ($lastCompletionLocal->isSameDay($now) || $now->diffInHours($lastCompletionLocal) < 2) {
                return response()->json(['locked' => true, 'modalContent' => [
                    'label' => 'You are progressing fast!',
                    'body' => 'It appears you have already completed <strong>'.$last_day_name.'</strong> today. While your efforts are admirable, we recommend you take your time through this program and take it one day at a time.',
                    'route' => route('explore.activity.bypass', ['activity_id' => $activity_id]),
                    'method' => 'GET',
                    'buttonLabel' => 'Continue to Activity',
                    'buttonClass' => 'btn-danger'
                ]]);
            }
        }
        return response()->json(['locked' => false]);
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
                $page_info['redirect_label'] = "Next Activity";
                $page_info['redirect_route'] = route('explore.activity', ['activity_id' => $activity->next]);
            }
            else {
                $page_info['redirect_label'] = "Back to Part ".$activity->day->module->id;
                $page_info['redirect_route'] = $page_info['exit_route'];
            }

            //check if this is the last activity of the day
            $last_act = getDayProgress($user->id, [$activity->day->id])[$activity->day->id]['one_more'];
            if ($last_act && $activity->status == 'unlocked') {
                $page_info['redirect_label'] = "Complete ".$activity->day->name;
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

    public function exploreActivityBypass($activity_id) {
        // user bypassed warning modal - remove warning information
        $user = Auth::user();
        $user->last_day_completed_at = null;
        $user->last_day_name = null;
        $user->block_next_day_act = null;
        $user->save();
        return redirect()->route('explore.activity', ['activity_id' => $activity_id]);
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
            return redirect()->route('library.main');
        }
    }

    public function librarySearch(Request $request) {

        //get query of unlocked activities
        $user_id = Auth::id();
        //query for activities - keep as query
        $activity_ids = UserActivity::where('user_id', $user_id)
            ->where('status', '!=', 'locked')
            ->pluck('activity_id');

        // make sure rand query matches query
        $query = Activity::where('deleted', false)
            ->whereIn('id', $activity_ids);
        $rand_query = Activity::where('deleted', false)
            ->whereIn('id', $activity_ids);
        
        
        //base param
        $empty_text = null;
        if ($request->base_param) {
            if ($request->base_param == 'main') {
                $empty_text = 'Keep progressing to unlock more exercises...';
            }
            else if ($request->base_param == 'favorited') {
                $fav_ids = Auth::user()->favorites()->with('activity')->pluck('activity_id');
                $query->whereIn('id', $fav_ids);
                $rand_query->whereIn('id', $fav_ids);
                $empty_text = '<span>Click the "<i class="bi bi-star"></i>" found in activities to add them to your favorites and view them here!</span>';
            }
        }

        //check if empty
        $empty = !$query->exists();
        if ($empty) {
            $view = view('components.search-results', ['empty_text' => $empty_text])->render();
            return response()->json(['html' => $view, 'empty' => true]);
        }

        // using query copy get random activity
        $random_act = $rand_query->inRandomOrder()->first();
        
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
            $query->where(function($in_query) use ($categories) {
                //filter based on the categories
                foreach($categories as $category) {
                    $lower = strtolower($category);
                    if ($lower == 'favorited') {
                        $fav_ids = Auth::user()->favorites()->with('activity')->pluck('activity_id');
                        $in_query->orWhereIn('id', $fav_ids);
                    }
                    else if ($lower == 'practice') {
                        $in_query->orWhere('type', 'practice');
                    }
                    else if ($lower == 'lesson') {
                        $in_query->orWhere('type', 'lesson');
                    }
                    else if ($lower == 'journal') {
                        $in_query->orWhere('type', 'journal');
                    }
                    else if ($lower == 'bonus') {
                        $in_query->orWhere('optional', true);
                    }
                    else if ($lower == 'reflection') {
                        $in_query->orWhere('type', 'reflection');
                    }
                }
            });
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
            $query->where('time', '<=', $end)->where('time', '>=', $start);
        }
            
        $activities = $query->with('day.module')->orderBy('order')->paginate(6);
        $view = view('components.search-results', ['activities' => $activities, 'random' => $random_act])->render();

        // count($activities) === 0
        return response()->json(['html' => $view, 'empty' => false]);
    }

    public function favoritesLibrary(Request $request)
    {
        $base_param = 'favorited';

        $page_info = [
            'journal' => false,
            'title' => 'Favorites',
            'search_route' => route('library.search'),
            'search_text' => 'Search for your favorite activity...'
        ];

        // $categories = [];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.favorites'));
        Session::put('current_nav', ['route' => route('library.favorites'), 'back' => 'Favorites']);
        return view('other.library', compact( 'base_param', 'page_info'));
    }
    public function mainLibrary(Request $request)
    {
        $base_param = 'main';

        $page_info = [
            'journal' => false,
            'title' => 'Search',
            'search_route' => route('library.search'),
            'search_text' => 'Search for any activity...'
        ];

        $categories = ['Practice', 'Lesson', 'Reflection', 'Journal', 'Favorited', 'Bonus'];

        //set as the previous library and save as exit
        Session::put('previous_library', route('library.main'));
        Session::put('current_nav', ['route' => route('library.main'), 'back' => 'Search']);
        return view("other.library", compact('base_param', 'page_info', 'categories'));
    }
    
    public function journal(Request $request)
    {
        //journal navbutton
        $previous = Session::get('previous_journal');
        if ($previous) {
            return redirect()->to($previous);
        }
        else {
            return redirect()->route('journal.compose');
        }
    }

    public function journalCompose(Request $request)
    {
        $page_info = [
            'journal' => true,
            'title' => 'Write'
        ];

        $journal = new Journal();

        //set as the previous journal and save as exit
        Session::put('previous_journal', route('journal.compose'));
        Session::put('current_nav', ['route' => route('journal.compose'), 'back' => 'Write']);
        return view('other.compose', compact('page_info', 'journal'));
    }
    public function journalLibrary (Request $request) {
        $base_param = 'journal';

        $wipe_filters = $request->activity ? true : false;

        $page_info = [
            'journal' => true,
            'title' => 'Journal Library',
            'search_route' => route('journal.search'),
            'search_text' => 'Search your past journals...'
        ];

        $categories = ['Self-care', 'Self-understanding', 'Parenting', 'Gratitude', 'Joy', 'Love', 'Relationships', 'Boundaries', 'No Topic'];

        //set as the previous library and save as exit
        Session::put('previous_journal', route('journal.library'));
        Session::put('current_nav', ['route' => route('journal.library'), 'back' => 'Journal Library']);
        return view("other.library", compact('base_param', 'page_info', 'categories', 'wipe_filters'));
    }

    public function journalSearch (Request $request) {
        //get user
        $id = Auth::id();
        $query = Note::where('user_id', $id);

        //check if empty
        $empty = !$query->exists();
        if ($empty) {
            $view = view('components.journal-search-results', ['empty_text' => '<span>Continue progressing to find a Journal activity, or write your first journal in the <a href="/journal">Journal</a> tab.</span>'])->render();
            return response()->json(['html' => $view]);
        }

        //handle search
        if ($request->has('search') && $request->search != '') {
            $query->where(function($in_query) use ($request) {
                $in_query->where('topic', 'like', '%' . $request->search . '%')
                    ->orWhere('note', 'like', '%' . $request->search . '%');
            });
        }

        //handle categories
        $categories = $request->input('category', []);
        if (!empty($categories)) {
            $query->where(function($in_query) use ($categories) {
                //filter based on the categories
                foreach($categories as $category) {
                    if ($category == 'Activities') {
                        $in_query->orWhere('activity_id', '!=', null);
                    }
                    else {
                        $in_query->orWhere('topic', Str::slug($category));
                    }
                }
            });
        }

        $notes = $query->orderBy('updated_at', 'desc')->paginate(3);

        //formatting the date
        foreach ($notes as $note) {
            $date = Carbon::parse($note->created_at);
            $date->setTimezone(new \DateTimeZone('EST'));
            $note->formatted_date = $date->diffForHumans().', '.$date->toFormattedDayDateString();
        }

        //render view
        $view = view('components.journal-search-results', ['notes' => $notes])->render();
        return response()->json(['html' => $view]);
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
        $teachers = Teacher::all();
        return view("other.help", compact('faqs', 'teachers'));
    }
}
