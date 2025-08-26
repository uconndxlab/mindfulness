<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Journal;
use App\Models\Quiz;
use App\Models\Note;
use App\Models\Activity;
use App\Models\Module;
use App\Models\QuizAnswers;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Faq;
use Carbon\Carbon;
use Illuminate\Support\Str;
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

        // get modules and which unlocked with pivot
        $user = Auth::user() ?? null;
        $modules = Module::orderBy('order', 'asc')->get();
        foreach ($modules as $module) {
            $stats = $module->getStats($user);
            $module->unlocked = $stats['unlocked'];
            $module->completed = $stats['completed'];
            $module->daysCompleted = $stats['daysCompleted'];
            $module->totalDays = $stats['totalDays'];
        }
    
        return view("explore.home", compact('modules'));
    }

    public function exploreModule($module_id, $accordion_day=null)
    {
        $user = Auth::user();
        //find the module
        // order days and activities by order
        $module = Module::with('days.activities')->findOrFail($module_id);
        $module->days = $module->days->sortBy('order');
        foreach ($module->days as $day) {
            $day->activities = $day->activities->sortBy('order');
        }

        // get user_module information
        $stats = $module->getStats($user);
        $module->unlocked = $stats['unlocked'];
        $module->completed = $stats['completed'];
        $module->daysCompleted = $stats['daysCompleted'];
        $module->totalDays = $stats['totalDays'];
        
        // check if module locked
        if (!$module->unlocked) {
            return redirect()->route('explore.home');
        }
        
        //get progress
        foreach ($module->days as $day) {
            $day->unlocked = $day->canBeAccessedBy($user);
            $day->completed = $day->isCompletedBy($user);

            // show accordion day, or last unlocked and incomplete
            if ($accordion_day) {
                // if accordion day is set, only one possible active day
                if ($day->id == $accordion_day) {
                    $day->active = true;
                }
            } else if ($day->unlocked && !$day->completed) {
                // if accordion not set, show last unlocked and incomplete day
                $day->active = true;
            } else {
                $day->active = false;
            }

            // get same statuses for days
            foreach ($day->activities as $activity) {
                $activity->unlocked = $activity->canBeAccessedBy($user);
                $activity->completed = $activity->isCompletedBy($user);
            }
        }

        //set back route
        $page_info['back_label'] = " Back to Home";
        $page_info['back_route'] = route('explore.home');

        //handle navigation
        Session::put('current_nav', ['route' => route('explore.module', ['module_id' => $module_id]), 'back' => 'Part '.$module_id]);
        Session::put('previous_explore', route('explore.module', ['module_id' => $module_id]));
        
        return view("explore.module", compact('module', 'page_info', 'accordion_day'));
    }

    public function exploreModuleBonus(Request $request) {
        $accordion_day = $request->day_id ?? null;
        $module_id = Day::findOrFail($accordion_day)->module_id ?? null;
        return $this->exploreModule($module_id, $accordion_day);
    }

    public function checkActivityLocked($activity_id) {
        // get user
        $user = Auth::user();
        $activity = Activity::findOrFail($activity_id) ?? null;
        $locked = !($user->canAccessActivity($activity));
        
        // // check if locked
        if ($locked) {
            return response()->json(['locked' => true, 'modalContent' => [
                'label' => 'Activity Locked: '.$activity->title,
                'body' => 'This activity is currently locked. Continue progressing to unlock this activity.'
            ]]);
        }

        // check for quick progress warning
        $user = Auth::user();
        $explore_day = $activity->day;
        
        // check for progress warning, last day completed id, and if day to explore is not completed
        if ($user->quick_progress_warning && $user->last_day_completed_id && !$user->isDayCompleted($explore_day)) {
            // get time and name of day completion
            /** @var ?Day $completedDay */
            $completedDay = Day::find($user->last_day_completed_id) ?? null;

            if ($completedDay) {
                $lastCompleteTime = $user->dayCompletedAt($completedDay);
                $last_day_name = $completedDay->name;

                $userTimezone = $user->timezone ?? config('app.timezone');
                
                // get local times
                $lastCompletionLocal = Carbon::parse($lastCompleteTime)->setTimezone($userTimezone);
                $now = now()->setTimezone($userTimezone);
    
                // if it is not yet the next day, return modal content (or less than two hours)
                if ($lastCompletionLocal->isSameDay($now) || $lastCompletionLocal->diffInHours($now) < 2) {
                    return response()->json(['locked' => true, 'modalContent' => [
                        'label' => 'You are progressing fast!',
                        'body' => 'It appears you have already completed **'.$last_day_name.'** today. '.
                            'While your efforts are admirable, we recommend you take your time through this program and take it one day at a time. '.
                            'How about repeating your favorite activity?',
                        'route' => route('explore.activity.bypass', ['activity_id' => $activity->id]),
                        'method' => 'GET',
                        'buttonLabel' => 'No, go to the next module',
                        'buttonClass' => 'btn-danger',
                        'closeLabel' => 'Okay. Go back'
                    ]]);
                }
            }
            
        }
        return response()->json(['locked' => false]);
    }

    public function exploreActivity($activity_id, Request $request)
    {
        $user = Auth::user() ?? null;
        //find activity and check progress again
        $activity = Activity::findOrFail($activity_id) ?? null;

        // check status
        if (!$user->canAccessActivity($activity)) {
            return redirect()->route('explore.home');
        }
        $activity->unlocked = true;
        $activity->completed = $user->isActivityCompleted($activity);

        // check if last activity in day (not skippable)
        $activity->final = $activity->day->activities()
            ->where('order', '>', $activity->order)
            ->where('optional', false)
            ->orderBy('order')
            ->first() == null;
        
        $activity->skippable = $activity->skippable && !$activity->final && !$activity->completed && isset($activity->type);

        //favoriting
        $activity->favorited = $user->isActivityFavorited($activity);

        $page_info = [];
        
        //setting exit button
        $exit = Session::get('current_nav');
        $page_info['exit_route'] = $exit ? $exit['route'] : route('explore.home');
        
        //NEXT/FINISH redirect
        //make sure that if doing next, the day is not changing
        if (!$request->library) {
            $next = $activity->nextActivity();
            // check if this activity will complete the day
            if (lastActivityInDay($activity, $user) && !$activity->day->isCompletedBy($user)) {
                $page_info['redirect_label'] = "Complete ".$activity->day->name;
                $page_info['redirect_route'] = $page_info['exit_route'];
            }
            // day is not completed, not completion activity, and there is next activity
            else if ($next) {
                $page_info['redirect_label'] = "Next Activity";
                $page_info['redirect_route'] = route('explore.activity', ['activity_id' => $next->id]);
            }
            // day is not completed, not completion activty, and no next activity
            else {
                $page_info['redirect_label'] = "Back to Part ".$activity->day->module->id;
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
            $quiz->question_options = $quiz->question_options;
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

        // log activity start
        $start_log = activity('activity')
            ->event('activity_started')
            ->performedOn($activity)
            ->causedBy($user)
            ->withProperties([
                'activity' => $activity->title,
                'day' => $activity->day->name,
                'module' => $activity->day->module->name,
                'activity_type' => $activity->type,
                'already_completed' => $activity->completed,
            ])
            ->log('Activity started');
        $start_log_id = $start_log->id;
        
        return view("explore.activity", compact('activity', 'page_info', 'content', 'quiz', 'journal', 'start_log_id'));
    }

    public function exploreActivityBypass($activity_id) {
        // user bypassed warning modal - remove warning information
        $user = Auth::user();
        $user->quick_progress_warning = false;
        $user->last_day_completed_id = null;
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
        catch (\Throwable $e) {
            \Log::error('Quiz submission failed', [
                'user_id' => Auth::id(),
                'quiz_id' => $request->quiz_id ?? null,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error_message' => 'Failed to submit quiz answers.'], 500);
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
        // get users unlocked activities
        $user = Auth::user();
        $query = $user->unlockedActivities();
        $rand_query = $user->unlockedActivities();
        
        //base param
        $empty_page = null;
        if ($request->base_param) {
            if ($request->base_param == 'main') {
                $empty_page = 'main';
            }
            else if ($request->base_param == 'favorited') {
                $query = $user->favoritedActivities();
                $rand_query = $user->favoritedActivities();
                $empty_page = 'favorited';
            }
        }

        //check if empty
        $empty = !$query->exists();
        if ($empty) {
            $view = view('components.search-results', ['empty_page' => $empty_page])->render();
            return response()->json(['html' => $view, 'empty' => true]);
        }

        // using query copy get random activity
        $rand_acts = $rand_query->where('type', 'practice')->get();
        $random_act = $rand_acts->count() > 0 ? $rand_acts->random() : null;
        
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
            $query->where(function($q) use ($categories, $user) {
                //filter based on the categories
                foreach($categories as $category) {
                    $lower = strtolower($category);
                    if ($lower == 'favorited') {
                        $fav_ids = $user->favoritedActivities()->pluck('activity.id')->toArray();
                        $q->orWhereIn('id', $fav_ids);
                    }
                    else if ($lower == 'practice') {
                        $q->orWhere('type', 'practice');
                    }
                    else if ($lower == 'lesson') {
                        $q->orWhere('type', 'lesson');
                    }
                    else if ($lower == 'journal') {
                        $q->orWhere('type', 'journal');
                    }
                    else if ($lower == 'bonus') {
                        $q->orWhere('optional', true);
                    }
                    else if ($lower == 'reflection') {
                        $q->orWhere('type', 'reflection');
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
        $user = Auth::user();
        if (!$user) {
            return;
        }
        $query = Note::where('user_id', $user->id);

        //check if empty
        $empty = !$query->exists();
        if ($empty) {
            $view = view('components.journal-search-results')->render();
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
        foreach ($modules as $module) {
            $stats = $module->getStats(Auth::user() ?? null);
            $module->unlocked = $stats['unlocked'];
            $module->completed = $stats['completed'];
            $module->daysCompleted = $stats['daysCompleted'];
            $module->totalDays = $stats['totalDays'];
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
