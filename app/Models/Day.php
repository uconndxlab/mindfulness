<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function finalActivity()
    {
        return $this->activities()->where('optional', false)->orderBy('order', 'desc')->first();
    }

    // user progress functions
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_day')
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
}
