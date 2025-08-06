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
        if (!$user) {
            return 0;
        }

        $days = $this->days;
        $completedDays = $days->filter(function ($day) use ($user) {
            return $day->isCompletedBy($user);
        });
        return $completedDays->count();
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
    
    public static function setNewOrder(array $order): void
    {
        foreach ($order as $i => $id) {
            static::find($id)->update(['order' => $i + 1]);
        }
    }
}
