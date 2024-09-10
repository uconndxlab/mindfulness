<?php

namespace App\Rules;

use App\Models\Activity;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActIdRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $activity = Activity::find($value);
        if (!$activity || $activity->type != 'journal') {
            $fail("Activity not found.");
        }
    }
}
