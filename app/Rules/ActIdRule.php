<?php

namespace App\Rules;

use App\Models\Activity;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Translation\PotentiallyTranslatedString;

class ActIdRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $activity = Activity::find($value);

        if (! $activity || $activity->type != 'journal') {
            $fail('Activity not found.');

            return;
        }

        // check if user has access to this activity
        $user = Auth::user();
        if (! $user || ! $activity->canBeAccessedBy($user)) {
            $fail('You do not have access to this activity.');

            return;
        }
    }
}
