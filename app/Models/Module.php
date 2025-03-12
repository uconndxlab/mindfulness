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

    public function isCompletedBy(User $user)
    {
        return $this->users()
            ->where('user_id', $user->id)
            ->wherePivot('completed', true)
            ->exists();
    }

    public function canBeAccessedBy(User $user)
    {
        return $this->users()
            ->where('user_id', $user->id)
            ->wherePivot('unlocked', true)
            ->exists();
    }
}
