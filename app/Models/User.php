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

    protected function reflectionAnswersQuery()
    {
        return $this->quiz_answers()->reflections();
    }

    protected function reflectionAnswersForModule(Module $module)
    {
        return $this->reflectionAnswersQuery()
            ->whereHas('activity.day', fn ($query) => $query->where('module_id', $module->id));
    }

    protected function reflectionScoreSummary($query): array
    {
        return [
            'average' => $query->avg('average'),
            'count' => $query->count(),
        ];
    }

    protected function selfRatingBreakdown($query): array
    {
        $byTitle = function (string $title) use ($query) {
            $scoped = (clone $query)
                ->join('activities', 'quiz_answers.activity_id', '=', 'activities.id')
                ->where('activities.title', $title);

            return [
                'average' => $scoped->avg('quiz_answers.average'),
                'count' => $scoped->count(),
            ];
        };

        return [
            'emotions' => $byTitle('Rate My Emotions'),
            'awareness' => $byTitle('Rate My Awareness'),
            'parenting' => $byTitle('Rate My Presence in Parenting'),
        ];
    }

    public function getAccountProgress(iterable $modules): array
    {
        $parts = [];
        $totals = [
            'days' => ['completed' => 0, 'total' => 0],
            'check_ins' => ['completed' => 0, 'total' => 0],
            'self_ratings' => ['completed' => 0, 'total' => 0],
        ];

        foreach ($modules as $module) {
            $moduleProgress = $module->getStats($this);
            $moduleReflections = $this->reflectionAnswersForModule($module);
            $checkInScores = $this->reflectionScoreSummary((clone $moduleReflections)->checkIns());
            $selfRatingScores = $this->reflectionScoreSummary((clone $moduleReflections)->selfRatings());

            $parts[] = [
                'order' => $module->order,
                'name' => $module->partName(),
                'short_name' => 'Part ' . $module->order,
                'days' => [
                    'completed' => $moduleProgress['daysCompleted'],
                    'total' => $moduleProgress['totalDays'],
                ],
                'check_ins' => [
                    'completed' => $moduleProgress['completedCheckInActivities'],
                    'total' => $moduleProgress['totalCheckInActivities'],
                    'average' => $checkInScores['average'],
                    'count' => $checkInScores['count'],
                ],
                'self_ratings' => [
                    'completed' => $moduleProgress['completedSelfRatings'],
                    'total' => $moduleProgress['totalSelfRatings'],
                    'average' => $selfRatingScores['average'],
                    'count' => $selfRatingScores['count'],
                ],
            ];

            $totals['days']['completed'] += $moduleProgress['daysCompleted'];
            $totals['days']['total'] += $moduleProgress['totalDays'];
            $totals['check_ins']['completed'] += $moduleProgress['completedCheckInActivities'];
            $totals['check_ins']['total'] += $moduleProgress['totalCheckInActivities'];
            $totals['self_ratings']['completed'] += $moduleProgress['completedSelfRatings'];
            $totals['self_ratings']['total'] += $moduleProgress['totalSelfRatings'];
        }

        $reflections = $this->reflectionAnswersQuery();
        $checkIns = (clone $reflections)->checkIns();
        $selfRatings = (clone $reflections)->selfRatings();

        $totals['check_ins'] = array_merge($totals['check_ins'], $this->reflectionScoreSummary($checkIns));
        $totals['self_ratings'] = array_merge(
            $totals['self_ratings'],
            $this->reflectionScoreSummary($selfRatings),
            $this->selfRatingBreakdown($selfRatings)
        );

        return [
            'parts' => $parts,
            'totals' => $totals,
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

    public function setFavoriteActivity(?Activity $activity, bool $favorited): bool
    {
        if (!$activity) {
            return false;
        }

        // should exist if unlocked?
        $this->activities()->updateExistingPivot($activity->id, [
            'favorited' => $favorited,
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
