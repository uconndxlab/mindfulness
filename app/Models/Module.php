<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    public function days()
    {
        return $this->hasMany(Day::class)->orderBy('order');
    }

    // user progress functions
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_module')
            ->withPivot('completed', 'unlocked');
    }

    public function isCompletedBy(?User $user)
    {
        return $user ? $this->users()
            ->where('user_id', $user->id)
            ->wherePivot('completed', true)
            ->exists() : false;
    }

    public function canBeAccessedBy(?User $user)
    {
        return $user ? $this->users()
            ->where('user_id', $user->id)
            ->wherePivot('unlocked', true)
            ->exists() : false;
    }

    public function numberDaysCompletedBy(?User $user)
    {
        return $user ? $this->days()
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->wherePivot('completed', true);
            })->count() : 0;
    }

    public function getStats(?User $user)
    {
        $unlocked = $this->canBeAccessedBy($user);
        $completed = $this->isCompletedBy($user);
        $daysCompleted = $this->numberDaysCompletedBy($user);
        $totalDays = $this->days->count();

        return [
            'unlocked' => $unlocked,
            'completed' => $completed,
            'daysCompleted' => $daysCompleted,
            'totalDays' => $totalDays,
        ];
    }
}
