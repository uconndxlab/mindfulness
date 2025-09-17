<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description', 'workbook_path', 'order'];

    public function days()
    {
        return $this->hasMany(Day::class);
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
        if (!$user) {
            return 0;
        }
        $completedDays = 0;
        $completedCheckInDays = 0;
        $totalCheckInActivities = 0;
        $completedCheckInActivities = 0;

        $days = $this->days->load('activities');
        foreach ($days as $day) {
            if ($day->isCompletedBy($user)) {
                if ($day->is_check_in) {
                    $completedCheckInDays++;
                } else {
                    $completedDays++;
                }
            }

            foreach ($day->activities as $activity) {
                if ($activity->is_check_in) {
                    $totalCheckInActivities++;
                    if ($activity->isCompletedBy($user)) {
                        $completedCheckInActivities++;
                    }
                }
            }
        }
        return [$completedDays, $completedCheckInDays, $totalCheckInActivities, $completedCheckInActivities];
    }

    // when progress is actually checked, it does NOT use this
    // a module is completed if ALL days are completed regardless of check-in or not
    public function getStats(?User $user)
    {
        $unlocked = $this->canBeAccessedBy($user);
        $completed = $this->isCompletedBy($user);
        [$daysCompleted, $completedCheckInDays, $totalCheckInActivities, $completedCheckInActivities] = $this->numberDaysCompletedBy($user);
        $totalDays = $this->days->where('is_check_in', false)->count();
        $totalCheckInDays = $this->days->count() - $totalDays;

        return [
            'unlocked' => $unlocked,
            'completed' => $completed,
            'daysCompleted' => $daysCompleted,
            'totalDays' => $totalDays,
            'completedCheckInDays' => $completedCheckInDays,
            'totalCheckInDays' => $totalCheckInDays,
            'totalCheckInActivities' => $totalCheckInActivities,
            'completedCheckInActivities' => $completedCheckInActivities,
        ];
    }
    
    public static function setNewOrder(array $order): void
    {
        foreach ($order as $i => $id) {
            static::find($id)->update(['order' => $i + 1]);
        }
    }

    public function lastActivity()
    {
        return $this->days()->orderBy('order', 'desc')->first()->activities()->where('optional', false)->orderBy('order', 'desc')->first();
    }
}
