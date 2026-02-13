<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Illuminate\Support\Str;
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
        'timezone',
        'terms_accepted_at',
        'terms_version',
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
            'lock_access' => 'boolean',
            'timezone' => 'string',
            'last_reminded_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'terms_version' => 'string',
        ];
    }

    public function sendEmailVerificationNotification()
    {        
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    protected static function booted(): void
    {
        // generate the hh_id
        static::creating(function (User $user) {
            if (empty($user->hh_id)) {
                $user->hh_id = self::generateHhId();
            }
        });
    }

    public static function generateHhId(): string
    {
        $randomLength = 8;

        do {
            $random = Str::random(8);
            $id = 'HH-'.$random;
        } while (self::where('hh_id', $id)->exists());

        return $id;
    }

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function milestones()
    {
        return $this->hasMany(UserMilestone::class);
    }

    public function quiz_answers($quiz_id = null)
    {
        $query = $this->hasMany(QuizAnswers::class);

        if ($quiz_id) {
            $query->where('quiz_id', $quiz_id);
        }

        return $query;
    }

    public function getStats()
    {
        $qas = $this->quiz_answers()->reflections();
        $qas_check_ins = (clone $qas)->where('reflection_type', 'check_in');
        $qas_rmas = (clone $qas)->where('reflection_type', 'rate_my_awareness');
        return [
            'pq_check_ins' => $qas_check_ins->avg('average'),
            'count_check_ins' => $qas_check_ins->count(),
            'pq_rmas' => $qas_rmas->avg('average'),
            'count_rmas' => $qas_rmas->count(),
            'pq_avg' => $qas->avg('average'),
        ];
    }

    // MDA relations
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'user_activity')
            ->withPivot('completed', 'unlocked', 'favorited', 'completed_at');
    }

    public function days()
    {
        return $this->belongsToMany(Day::class, 'user_day')
            ->withPivot('completed', 'unlocked', 'completed_at');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'user_module')
            ->withPivot('completed', 'unlocked', 'completed_at');
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

    // bonus activites
    public function bonusActivities()
    {
        return $this->activities()
            ->wherePivot('unlocked', true)
            ->where('optional', true);
    }

    public function completedBonusActivities()
    {
        return $this->activities()
            ->wherePivot('unlocked', true)
            ->wherePivot('completed', true)
            ->where('optional', true);
    }

    public function getBonusStats()
    {

        return [
            'totalBonus' => Activity::where('optional', true)->count(),
            'numberBonusCompleted' => $this->completedBonusActivities()->count(),
            'numberBonusUnlocked' => $this->bonusActivities()->count(),
        ];
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
            return;
        }

        // should exist if unlocked?
        $this->activities()->updateExistingPivot($activity->id, [
            'favorited' => !$this->isActivityFavorited($activity)
        ]);

        return $this->isActivityFavorited($activity);
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

    public function latestUnlock()
    {
        return $this->activities()
            ->wherePivot('unlocked', true)
            ->wherePivot('completed', false)
            ->where('optional', false)
            ->orderBy('order', 'desc')
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
