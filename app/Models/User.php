<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method static findOrFail(int $user_id)
 */

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'voiceId',
        'last_active_at',
        'lock_access'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function quiz_answers($quiz_id = null)
    {
        $query = $this->hasMany(QuizAnswers::class);

        if ($quiz_id) {
            $query->where('quiz_id', $quiz_id);
        }

        return $query;
    }

    // MDA relations
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'user_activity')
            ->withPivot('completed', 'unlocked', 'favorited')
            ->orderBy('order');
    }

    public function days()
    {
        return $this->belongsToMany(Day::class, 'user_day')
            ->withPivot('completed', 'unlocked')
            ->orderBy('order');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'user_module')
            ->withPivot('completed', 'unlocked')
            ->orderBy('order');
    }

    // activity progress functions
    public function isActivityCompleted(?Activity $activity)
    {
        return $activity ? $this->activities()
            ->where('activity_id', $activity->id)
            ->wherePivot('completed', true)
            ->exists() : false;
    }

    public function canAccessActivity(?Activity $activity)
    {
        return $activity ? $this->activities()
            ->where('activity_id', $activity->id)
            ->wherePivot('unlocked', true)
            ->exists() : false;
    }

    public function unlockedActivities()
    {
        return $this->activities()
        ->wherePivot('unlocked', true);
    }

    public function isActivityFavorited(?Activity $activity)
    {
        return $activity ? $this->activities()
            ->where('activity_id', $activity->id)
            ->wherePivot('favorited', true)
            ->exists() : false;
    }

    public function favoritedActivities()
    {
        return $this->activities()
            ->wherePivot('unlocked', true)
            ->wherePivot('favorited', true);
    }

    public function toggleFavoriteActivity(Activity $activity)
    {
        $current_pivot = $this->activities()
            ->where('activity_id', $activity->id)
            ->first();
        
        if ($current_pivot) {
            $status = !($current_pivot->pivot->favorited ?? false);
            $this->activities()->updateExistingPivot($activity->id, [
                'favorited' => $status
            ]);
            return $status;
        }
        else {
            $this->activities()->attach($activity->id, [
                'favorited' => true
            ]);
            return true;
        }  
    }

    // day progress functions
    public function isDayCompleted(Day $day)
    {
        return $this->days()
            ->where('day_id', $day->id)
            ->wherePivot('completed', true)
            ->exists();
    }

    public function canAccessDay(Day $day)
    {
        return $this->days()
            ->where('day_id', $day->id)
            ->wherePivot('unlocked', true)
            ->exists();
    }

    // module progress functions
    public function isModuleCompleted(Module $module)
    {
        return $this->modules()
            ->where('module_id', $module->id)
            ->wherePivot('completed', true)
            ->exists();
    }

    public function canAccessModule(Module $module)
    {
        return $this->modules()
            ->where('module_id', $module->id)
            ->wherePivot('unlocked', true)
            ->exists();
    }
}
