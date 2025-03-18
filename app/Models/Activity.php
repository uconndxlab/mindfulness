<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';
    
    protected $casts = [
        'optional' => 'boolean',
        'skippable' => 'boolean',
    ];

    public function day()
    {
        return $this->belongsTo(Day::class);
    }

    // activity type
    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function content()
    {
        return $this->hasOne(Content::class);
    }

    public function journal()
    {
        return $this->hasOne(Journal::class);
    }

    // user progress functions
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_activity')
            ->withPivot('completed', 'unlocked', 'favorited');
    }

    public function nextActivity() {
        return $this->day->activities()->where('order', '>', $this->order)->first();
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

    public function isFavoritedBy(?User $user)
    {
        return $user ? $this->users()
            ->where('user_id', $user->id)
            ->wherePivot('favorited', true)
            ->exists() : false;
    }
}
