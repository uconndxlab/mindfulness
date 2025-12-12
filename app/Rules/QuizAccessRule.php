<?php

namespace App\Rules;

use App\Models\Quiz;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class QuizAccessRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $quiz = Quiz::find($value);
        
        if (!$quiz) {
            $fail("Quiz not found.");
            return;
        }
        
        // check if user has access to the activity this quiz belongs to
        $activity = $quiz->activity;
        if (!$activity) {
            $fail("Quiz is not associated with an activity.");
            return;
        }
        
        $user = Auth::user();
        if (!$user || !$activity->canBeAccessedBy($user)) {
            $fail("You do not have access to this quiz.");
            return;
        }
    }
}

