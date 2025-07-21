<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\EventLog;
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
        $start_log = EventLog::findOrFail($request->start_log_id) ?? null;

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        // check if unlocked
        if (!$user->canAccessActivity($activity)) {
            return response()->json(['succcess' => false,'message' => 'Activity locked'], 403);
        }

        $already_completed = $user->isActivityCompleted($activity);

        // get time to complete and log activity
        if ($start_log) {
            $time_to_complete = $start_log->created_at->diffInSeconds(now());
        }
        activity('activity')
            ->event('activity_completed')
            ->performedOn($activity)
            ->causedBy($user)
            ->withProperties([
                'activity' => $activity->title,
                'day' => $activity->day->name,
                'module' => $activity->day->module->name,
                'activity_type' => $activity->type,
                'repeat_completion' => $already_completed,
                'duration_in_seconds' => $time_to_complete,
            ])
            ->log('Activity completed');
        // day and module completion logged in ProgressService

        // redirect url
        $redirect_url = null;
        // check if practice - redirect to the next activity (slider reflection usually)
        if ($activity->type === 'practice') {
            $next_activity = $activity->nextActivity();
            if ($next_activity && $next_activity->type === 'reflection') {
                $redirect_url = route('explore.activity', ['activity_id' => $next_activity->id]);
            }
        }

        // check if already completed
        if ($already_completed) {
            // potential for debugging in case the next day did not unlock
            return response()->json(['success' => true, 'message' => 'Activity already completed', 'redirect_url' => $redirect_url], 200);
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

        return response()->json(['success' => true, 'message' => $message, 'day_completed' => $day_completed, 'redirect_url' => $redirect_url], 200);
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

    public function logInteraction(Request $request)
    {
        $validated = $request->validate([
            'activity_id' => 'required|integer|exists:activities,id',
            'event_type' => 'required|string|in:focused,unfocused,exited',
            'start_log_id' => 'sometimes|integer|exists:event_log,id',
            'duration' => 'sometimes|integer',
        ]);

        $activity = Activity::with('day.module')->find($validated['activity_id']);
        $user = Auth::user();
        $properties = [
            'activity' => $activity->title,
            'day' => $activity->day->name,
            'module' => $activity->day->module->name,
            'activity_type' => $activity->type,
        ];

        if (isset($validated['duration'])) {
            $properties['duration_in_seconds'] = $validated['duration'];
        }

        // if the event is exited, check start log for auth
        if ($validated['event_type'] === 'exited' && isset($validated['start_log_id'])) {
            $startLog = EventLog::find($validated['start_log_id']);
            if (!$startLog || $startLog->causer_id !== $user->id) {
                return response()->json(['status' => 'unauthorized'], 403);
            }
        }

        activity('activity')
            ->event('activity_' . $validated['event_type'])
            ->performedOn($activity)
            ->causedBy($user)
            ->withProperties($properties)
            ->log('Activity ' . $validated['event_type']);

        return response()->json(['status' => 'success'], 200);
    }
}
