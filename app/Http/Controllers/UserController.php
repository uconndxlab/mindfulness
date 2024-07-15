<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\UserActivity;
use App\Models\UserDay;
use App\Models\UserModule;
use App\Models\Activity;
use App\Models\Favorite;

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

    public function updateProgress(Request $request) {
        //TODO
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

            //check next
            if ($activity->next != null) {
                //find next
                $next = Activity::findOrFail($activity->next);
                $next_activity_progress = $activity_progress->where('activity_id', $next->id)->first();
                if ($next_activity_progress == null || $next_activity_progress->status == 'locked') {
                    //update entry for next
                    UserActivity::updateOrCreate([
                        "user_id" => Auth::id(),
                        "activity_id" => $next->id,
                    ],[
                        "status" => 'unlocked'
                    ]);
                }
            }
            return response()->json(['message' => 'Progress updated']);
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

        //check for changes
        if ($request->name != $user->name || $request->password != null) {
            //validate
            $request->validate([
                'name'=> ['sometimes', 'string', 'max:255'],
                'password'=> ['sometimes', Password::defaults(), 'nullable'],
                'oldPass'=>['required'],
            ], [
                'name.max' => 'Name must be no longer than 255 characters.',
                'oldPass.required' => 'Please enter your password to save changes.'
            ]);

            //check password before making updates
            if (!Hash::check($request->oldPass, $user->password)) {
                return back()->withErrors(['oldPass' => 'The password you entered is incorrect.'])->withInput();;
            }
            if ($request->name && $request->name != $user->name) {
                $user->name = $request->name;
            }
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
            return back()->with('success','Your information has been updated successfully.');
        }
        else {
            return back();
        }
    }

    public function addFavorite(Request $request) {
        $request->validate([
            'activity_id' => ['required', 'exists:activities,id']

        ]);

        $user = Auth::user();
        //check if user is here yet
        if ($user->progress_activity < Activity::findOrFail($request->activity_id)->order) {
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
