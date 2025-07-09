<?php

namespace App\Models;

use Carbon\Carbon;
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
        'last_active_at',
        'lock_access',
        'timezone'
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
            'last_active_at' => 'datetime',
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
            ->withPivot('completed', 'unlocked', 'completed_at')
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

    public function toggleFavoriteActivity(?Activity $activity)
    {
        if (!$activity) {
            return false;
        }

        $exists = $this->activities()
            ->where('activity_id', $activity->id)
            ->exists();
        
        if ($exists) {
            // get status
            $status = $this->activities()
                ->wherePivot('activity_id', $activity->id)
                ->first()
                ->pivot
                ->favorited;

            // update
            $newStatus = !$status;
            $this->activities()->updateExistingPivot($activity->id, [
                'favorited' => $newStatus
            ]);
            return $newStatus;
        }
        else {
            // make new pivot
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

    public function dayCompletedAt(?Day $day)
    {
        return $day ? $this->days()
            ->where('day_id', $day->id)
            ->first()
            ?->pivot
            ->completed_at : null;
    }

    public function currentActivity()
    {
        return $this->activities()
            ->wherePivot('unlocked', true)
            ->wherePivot('completed', false)
            ->where('optional', false)
            ->first();
    }

    public function canSendReminder()
    {
        $inactive_limit = (int) config('mail.remind_email_day_limit', 30);
        $email_limit = 7;
        $last_active = $this->last_active_at ? Carbon::parse($this->last_active_at) : null;
        $last_reminded = $this->last_reminded_at ? Carbon::parse($this->last_reminded_at) : null;

        if (($last_active && $last_active->diffInDays(Carbon::now()) < $inactive_limit) ||
            ($last_reminded && $last_reminded->diffInDays(Carbon::now()) < $email_limit)) {
            return false;
        }

        return true;
    }
}
