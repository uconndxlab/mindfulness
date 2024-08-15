<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Note;
use App\Rules\ActIdRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Validator;

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
                'word_otd' => ['required', 'in:relax,compassion,other', 'regex:/^[\w\s.,!?()\-\'"&]*$/']
            ], [
                'note.required' => 'A note is required.',
                'note.string' => 'The note must be a string.',
                'note.max' => 'The note may not be greater than 1027 characters.',
                'note.regex' => 'The note may only contain letters, numbers, spaces, and basic punctuation.',
                'word_otd.required' => 'Please select a word of the day.',
                'word_otd.in' => 'Word of the day must come from the provided list.',
                'word_otd.regex' => 'Word of the day must come from the provided list.'
            ]);
        }
        return $validator;
    }

    public function store(Request $request)
    {
        try {
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
                    'word_otd' => $act->title.' - '.$act->day->name.', '.$act->day->module->name,
                ]);
            }
            else {
                Note::create([
                    'user_id' => Auth::id(),
                    'note' => $request->note,
                    'word_otd' => ucfirst($request->word_otd),
                ]);
            }

            return response()->json(['success' => 'Note submitted!'], 200);
        }
        catch (ValidationException $e) {
            return response()->json(['error_message' => 'Failed to submit note.', 'error' => $e], 500);
        }
    }
}
