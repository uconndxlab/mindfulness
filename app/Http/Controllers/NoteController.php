<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Note;
use App\Rules\ActIdRule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{

    public function noteValidator($request) {
        $validator = null;
        if ($request->activity) {
            $validator = Validator::make($request->all(), [
                'note' => ['required', 'string', 'max:1027', 'regex:/^[\w\s.,!?()\-\'"&]*$/'],
                'activity_id' => ['required', 'integer', new ActIdRule()]
            ], [
                'note.required' => 'A note is required.',
                'note.string' => 'The note must be a string.',
                'note.max' => 'The note may not be greater than 1027 characters.',
                'note.regex' => 'The note may only contain letters, numbers, spaces, and basic punctuation.'
            ]);
        }
        else {
            $validator = Validator::make($request->all(), [
                'note' => ['required', 'string', 'max:1027', 'regex:/^[\w\s.,!?()\-\'"&]*$/'],
                'topic' => ['in:self-care,self-understanding,parenting,gratitude,joy,love,relationships,boundaries,no-topic', 'regex:/^[\w\s.,!?()\-\'"&]*$/']
            ], [
                'note.required' => 'A note is required.',
                'note.string' => 'The note must be a string.',
                'note.max' => 'The note may not be greater than 1027 characters.',
                'note.regex' => 'The note may only contain letters, numbers, spaces, and basic punctuation.',
                'topic.in' => 'Word of the day must come from the provided list.',
                'topic.regex' => 'Word of the day must come from the provided list.'
            ]);
        }
        return $validator;
    }

    public function store(Request $request)
    {
        try {
            // throttle
            $key = sha1('store_note|'.$request->ip().'|'.Auth::user()->email);
            $limit = ['attempts' => 3, 'decay' => 60]; // 3 successes per minute
            if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
                $seconds = RateLimiter::availableIn($key);
                $timeLeft = Carbon::now()->addSeconds($seconds)->diffForHumans(null, true);
                return response()->json(['error_message' => "Too many attempts. Please try again in {$timeLeft}."], 429);
            }

            $validator = $this->noteValidator($request);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
    
            if ($request->activity_id) {
                $act = Activity::findOrFail($request->activity_id);
                Note::updateOrCreate([
                    'user_id' => Auth::id(),
                    'activity_id' => $request->activity_id
                ],[
                    'note' => $request->note,
                    'topic' => '<a href="/explore/activity/'.$act->id.'">'.$act->title.'</a> - '.$act->day->name.', '.$act->day->module->name.'</span>',
                ]);
            }
            else {
                Note::create([
                    'user_id' => Auth::id(),
                    'note' => $request->note,
                    'topic' => $request->topic,
                ]);
            }

            RateLimiter::hit($key, $limit['decay']);
            return response()->json(['success' => 'Note submitted!'], 200);
        }
        catch (ValidationException $e) {
            return response()->json(['error_message' => 'Failed to submit note.', 'error' => $e], 500);
        }
    }
}
