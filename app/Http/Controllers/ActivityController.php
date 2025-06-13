<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    protected $progressService;
    
    public function __construct(ProgressService $progressService) {
        $this->progressService = $progressService;
    }

    // complete activity
    public function complete(Request $request) {
        /** @var ?User $user */
        $user = Auth::user() ?? null;
        $activity = Activity::findOrFail($request->activity_id) ?? null;

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        // check if unlocked
        if (!$user->canAccessActivity($activity)) {
            return response()->json(['succcess' => false,'message' => 'Activity locked'], 403);
        }

        // check if already completed
        if ($user->isActivityCompleted($activity)) {
            // potential for debugging in case the next day did not unlock
            return response()->json(['success' => true, 'message' => 'Activity already completed'], 200);
        }
        
        // complete activity
        $result = $this->progressService->completeActivity($user, $activity);
        
        // return full result as json
        $day_completed = false;
        
        // build response
        $message = 'Activity completed';
        if (isset($result['optional_unlocked']) && $result['optional_unlocked']) {
            $message .= ', optional activities unlocked';
        }
        if (isset($result['next_activity_unlocked']) && $result['next_activity_unlocked']) {
            $message .= ', next activity unlocked';
        }
        if (isset($result['day_completed']) && $result['day_completed']) {
            $message .= ', day completed';
            $day_completed = true;
            if (isset($result['next_day_unlocked']) && $result['next_day_unlocked']) {
                $message .= ', next day unlocked';
            }
        }
        if (isset($result['module_completed']) && $result['module_completed']) {
            $message .= ', module completed';
            if (isset($result['next_module_unlocked']) && $result['next_module_unlocked']) {
                $message .= ', next module unlocked';
            }
        }
        if (isset($result['course_completed']) && $result['course_completed']) {
            $message .= ', course completed';
        }

        return response()->json(['success' => true, 'message' => $message, 'day_completed' => $day_completed], 200);
    }

    // skip activity
    public function skip(Request $request) {
        /** @var ?User $user */
        $user = Auth::user() ?? null;
        $activity = Activity::findOrFail($request->activity_id) ?? null;

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // check if unlocked
        if (!$user->canAccessActivity($activity)) {
            return response()->json(['success' => false, 'message' => 'Activity locked', 'id' => $activity->id], 403);
        }
        // check if skippable
        if (!$activity->skippable) {
            return response()->json(['success' => false, 'message' => 'Activity not skippable'], 403);
        }

        // unlock next activity
        $nextAct = $activity->day->activities()
            ->where('order', '>', $activity->order)
            ->where('optional', false)
            ->orderBy('order')
            ->first();
        if ($nextAct) {
            $this->progressService->unlockActivity($user, $nextAct);
        }
        else {
            return response()->json(['success' => false, 'message' => 'No next activity found'], 404);
        }

        // success
        return redirect(route('explore.activity', ['activity_id' => $nextAct->id]));
    }
}
