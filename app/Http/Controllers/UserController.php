<?php

namespace App\Http\Controllers;

use App\Events\BonusUnlocked;
use App\Events\FinalActivityCompleted;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\UserActivity;
use App\Models\Activity;
use App\Models\Day;
use App\Models\Favorite;
use Log;

class UserController extends Controller
{
    public function updateVoice(Request $request) {
        //check for voiceId
        if ($request->filled('voiceId')) {
            $request->validate([
                'voiceId'=> ['in:someName,otherName']
            ]);
            //get user
            $user = Auth::user();
    
            //update
            $user->voiceId = $request->voiceId;
            $user->save();
        }

        return redirect(route('explore.home'));
    }

    public function completeLater(Request $request) {
        // find activity and get status
        $activity = Activity::findOrFail($request->activity_id);
        $user = Auth::user();
        $user->load('progress_activities');
        $status = $user->progress_activities->where('activity_id', $activity->id)->first()->status ?? 'locked';

        //check if locked, final, or optional
        if ($status == 'locked' || $activity->final || $activity->optional || $activity->no_skip) {
            return response()->json(['message' => 'Forbidden'], 203);
        }
        // check if already completed
        else if ($status == 'completed') {
            return response()->json(['message' => 'Activity already completed']);
        }
        else if ($status == 'unlocked') {
            // call unlockNext and get the results
            $response = $this->unlockNext($request);
            // if success, redirect to next activity
            if ($response->status() == 200) {
                // return redirect(route('explore.module.bonus', ['module_id' => $activity->day->module_id, 'day_id_accordion' => $activity->day_id]));
                $next_id = $response->original['next_id'];
                return redirect(route('explore.activity', ['activity_id' => $next_id]));
            }
            else {
                return response()->json(['message' => 'Error completing activity later'], 500);
            }
        }

    }

    public function unlockNext(Request $request) {
        // find activity and get status
        $activity = Activity::findOrFail($request->activity_id);
        $user = Auth::user();
        
        // unlock next if in same day
        try {
            if ($activity->next != null) {
                $next = Activity::findOrFail($activity->next);
                if ($next->day == $activity->day) {
                    // get status of next activity
                    $next_activity_status = $user->progress_activities->where('activity_id', $next->id)->first()->status ?? 'locked';
                    if ($next_activity_status == 'locked') {
                        //update entry for next
                        UserActivity::updateOrCreate([
                            "user_id" => Auth::id(),
                            "activity_id" => $next->id,
                        ],[
                            "status" => 'unlocked'
                        ]);
                        Session::forget('progress_modules');
                        Session::forget('progress_days');
                        Cache::forget('user_'.Auth::id().'_progress_activities');
                    }
                }
            }
            return response()->json(['message' => 'Next activity unlocked', 'next_id' => $next->id], 200);
        }
        catch (Exception $e) {
            return response()->json(['message' => 'Error unlocking next activity'], 500);
        }
    }

    public function completeActivity(Request $request) {
        $request->validate([
            'activity_id' => ['required', 'exists:activities,id'],
        ]);

        $activity = Activity::findOrFail($request->activity_id);
        $activity_progress = Auth::user()->load('progress_activities')->progress_activities;

        //get the current activity status
        $current_activity_progress = $activity_progress->where('activity_id', $activity->id)->first();

        //make sure they have this unlocked
        if ($current_activity_progress->status == 'unlocked') {
            //update
            $current_activity_progress->status = 'completed';
            $current_activity_progress->save();

            // check if day is completed using helper
            $day_completed = checkUserDay(Auth::id(), $activity->day_id);

            $next_id = $activity->next;
            // if day is completed...
            if ($day_completed) {
                // unlock optional activities within day
                $optional_activities = Activity::where('optional', true)->where('day_id', $activity->day_id)->get();
                foreach ($optional_activities as $optional) {
                    $optional_status = $activity_progress->where('activity_id', $optional->id)->first()->status ?? 'locked';
                    if ($optional_status == 'locked') {
                        // fire modal event for unlocked bonus
                        event(new BonusUnlocked($activity->day));
                        //update entry for optional
                        UserActivity::updateOrCreate([
                            "user_id" => Auth::id(),
                            "activity_id" => $optional->id,
                        ],[
                            "status" => 'unlocked'
                        ]);
                    }
                }

                // get the id for the proper next day
                $next_id = Activity::where('day_id', $activity->day_id)->where('optional', false)->orderBy('order')->get()->last()->next;
            }

            Log::info('Day completed: '.($day_completed ? 'true' : 'false'));
            Log::info('Next ID: '.$next_id);
            // check next
            if ($next_id != null) {
                //find next
                $next = Activity::findOrFail($activity->next);
                if ($next->day == $activity->day || $day_completed) {
                    Log::info('Next in same day or day completed');
                    // get status of next activity
                    $next_activity_status = $activity_progress->where('activity_id', $next_id)->first()->status ?? 'locked';
                    if ($next_activity_status == 'locked') {
                        //update entry for next
                        UserActivity::updateOrCreate([
                            "user_id" => Auth::id(),
                            "activity_id" => $next_id,
                        ],[
                            "status" => 'unlocked'
                        ]);
                    }
                }
            }

            //updating the session saved progress - getting will update
            Session::forget('progress_modules');
            Session::forget('progress_days');
            Cache::forget('user_'.Auth::id().'_progress_activities');
            return response()->json(['message' => 'Progress updated', 'day_completed' => $day_completed], 200);
        }
        else if ($current_activity_progress->status == 'completed') {
            return response()->json(['message' => 'Activity already completed']);
        }

        //should not reach here
        return response()->json(['message' => 'Forbidden'], 203);
    }

    public function updateNamePass(Request $request) {
        //get user
        $user = Auth::user();

        try {
            //check for changes
            if ($request->name != $user->name || $request->password != null) {
                //validate
                $validator = Validator::make($request->all(), [
                    'name' => ['sometimes', 'string', 'max:255'],
                    'password' => ['sometimes', Password::min(8)->mixedCase()->numbers(), 'nullable'],
                    'oldPass' => ['required'],
                ], [
                    'name.max' => 'Name must be no longer than 255 characters.',
                    'oldPass.required' => 'Please enter your password to save changes.'
                ]);
    
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
    
                //check password before making updates
                if (!Hash::check($request->oldPass, $user->password)) {
                    return response()->json(['errors' => ['oldPass' => 'The password you entered is incorrect.']], 422);
                }
                if ($request->name && $request->name != $user->name) {
                    $user->name = $request->name;
                }
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();
                return response()->json(['success' => 'Your information has been updated successfully.'], 200);
            }
            else {
                return response()->json(['success' => 'No changes were made.'], 200);
            }
        }
        catch (Exception $e) {
            return response()->json(['error_message' => 'Failed to update user information.', 'error' => $e], 500);
        }
    }

    public function addFavorite(Request $request) {
        $request->validate([
            'activity_id' => ['required', 'exists:activities,id']

        ]);

        $user = Auth::user();
        //check if user is here yet
        $activity = Activity::findOrFail($request->activity_id);
        $user->load('progress_activities');
        $status = $user->progress_activities->where('activity_id', $activity->id)->first()->status ?? 'locked';
        if ($status == 'locked') {
            return response()->json(['message' => 'Forbidden'], 203);
        }

        Favorite::create([
            'user_id' => $user->id,
            'activity_id' => $request->activity_id
        ]);

        return response()->json(['message' => 'Favorite added'], 201);
    }

    public function deleteFavorite($activity_id) {
        Activity::findOrFail($activity_id);

        $user = Auth::user();

        Favorite::where('user_id', $user->id)
            ->where('activity_id', $activity_id)
            ->delete();

        return response()->json(['message' => 'Favorite removed'], 200);
    }
}
