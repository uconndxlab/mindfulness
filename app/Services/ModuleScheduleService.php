<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleScheduleService
{
    public const IDEAL_GAP = 7;
    public const MIN_GAP = 5;
    public const MIN_MODULE_DAYS = 4;

    /** Total program length; day 1 = registration, last day = registration + (length - 1). */
    public const JOURNEY_GOAL_DAYS = 28;

    public function initializeForUser(User $user): void
    {
        $this->ensureModuleRows($user);

        $registrationDay = $this->registrationDay($user);

        foreach (Module::orderBy('order')->get() as $module) {
            DB::table('user_module')
                ->where('user_id', $user->id)
                ->where('module_id', $module->id)
                ->update([
                    'start_date' => $registrationDay->copy()->addDays(($module->order - 1) * self::IDEAL_GAP)->toDateString(),
                    'active_days_count' => 0,
                    'updated_at' => now(),
                ]);
        }

        $user->active_days_count = 0;
        $user->save();
    }

    public function syncForUser(User $user): void
    {
        $this->ensureModuleRows($user);

        $today = $this->today($user);
        $isNewDay = $this->isNewCalendarDay($user, $today);

        if ($isNewDay) {
            $user->active_days_count = ($user->active_days_count ?? 0) + 1;
        }

        $modules = Module::orderBy('order')->get();
        $starts = $this->computeStarts($user, $modules, $today);

        foreach ($modules as $module) {
            if ($this->hasModuleStarted($user, $module)) {
                continue;
            }

            DB::table('user_module')
                ->where('user_id', $user->id)
                ->where('module_id', $module->id)
                ->update([
                    'start_date' => $starts[$module->order]->toDateString(),
                    'updated_at' => now(),
                ]);
        }

        if ($isNewDay) {
            foreach ($modules as $module) {
                $pivot = $user->modules()->where('module_id', $module->id)->first()?->pivot;

                if (! $pivot?->unlocked || $pivot->completed || $today->lt($starts[$module->order])) {
                    continue;
                }

                DB::table('user_module')
                    ->where('user_id', $user->id)
                    ->where('module_id', $module->id)
                    ->increment('active_days_count');
            }
        }

        $user->last_active_at = now();
        $user->last_inactivity_reminder_day = null;
        $user->save();
    }

    public function getSchedule(User $user): array
    {
        $modules = Module::orderBy('order')->get();
        $today = $this->today($user);
        $starts = $this->computeStarts($user, $modules, $today);

        $completions = $this->computeCompletions($starts, $this->journeyGoalDate($user));
        $journeyGoalDate = $this->journeyGoalDate($user);

        return [
            'starts' => $starts,
            'completions' => $completions,
            'journeyGoalDate' => $journeyGoalDate,
            'showJourneyGoal' => $completions[4]->lte($journeyGoalDate),
            'modules' => $modules,
            'today' => $today,
        ];
    }

    public function hasModuleStarted(User $user, Module $module): bool
    {
        return Activity::query()
            ->where('optional', false)
            ->whereHas('day', fn ($query) => $query->where('module_id', $module->id))
            ->whereHas('users', fn ($query) => $query
                ->where('users.id', $user->id)
                ->where('user_activity.completed', true))
            ->exists();
    }

    public function moduleActivityProgress(User $user, Module $module): array
    {
        $total = Activity::query()
            ->where('optional', false)
            ->whereHas('day', fn ($query) => $query->where('module_id', $module->id))
            ->count();

        $completed = Activity::query()
            ->where('optional', false)
            ->whereHas('day', fn ($query) => $query->where('module_id', $module->id))
            ->whereHas('users', fn ($query) => $query
                ->where('users.id', $user->id)
                ->where('user_activity.completed', true))
            ->count();

        return [
            'completed' => $completed,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'percentLeft' => $total > 0 ? (int) round(100 - (($completed / $total) * 100)) : 100,
        ];
    }

    public function totalCompletedActivities(User $user): int
    {
        return $user->activities()
            ->wherePivot('completed', true)
            ->where('optional', false)
            ->count();
    }

    public function journeyGoalDate(User $user): Carbon
    {
        return $this->registrationDay($user)->copy()->addDays(self::JOURNEY_GOAL_DAYS - 1);
    }

    public function registrationDay(User $user): Carbon
    {
        return Carbon::parse($user->created_at)
            ->timezone($this->timezone($user))
            ->startOfDay();
    }

    public function today(User $user): Carbon
    {
        return now()->timezone($this->timezone($user))->startOfDay();
    }

    public function formatAbsoluteDate(Carbon $date, User $user): string
    {
        return $date->copy()->timezone($this->timezone($user))->format('F j');
    }

    public function formatDate(Carbon $date, User $user): string
    {
        $localized = $date->copy()->timezone($this->timezone($user))->startOfDay();
        $today = $this->today($user);

        if ($localized->equalTo($today)) {
            return 'Today';
        }

        if ($localized->equalTo($today->copy()->subDay())) {
            return 'Yesterday';
        }

        if ($localized->equalTo($today->copy()->addDay())) {
            return 'Tomorrow';
        }

        return $localized->format('F j');
    }

    /** @return array<int, Carbon> */
    public function computeCompletions(array $starts, Carbon $journeyGoalDate): array
    {
        $completions = [];

        for ($order = 1; $order <= 3; $order++) {
            $completions[$order] = $starts[$order + 1]->copy()->subDay();
        }

        $daysUntilGoal = $starts[4]->diffInDays($journeyGoalDate);
        $completions[4] = $daysUntilGoal > self::MIN_GAP
            ? $journeyGoalDate->copy()
            : $starts[4]->copy()->addDays(self::MIN_MODULE_DAYS);

        return $completions;
    }

    /**
     * Compute start dates for parts 1–4 in order.
     *
     * @return array<int, Carbon>
     */
    private function computeStarts(User $user, Collection $modules, Carbon $today): array
    {
        $tz = $this->timezone($user);
        $reg = $this->registrationDay($user);
        $starts = [];

        $module = fn (int $order) => $modules->firstWhere('order', $order);
        $pivot = fn (int $order) => $user->modules()->where('module_id', $module($order)->id)->first()?->pivot;

        // Part 1 — slides to today when not started
        $pushed = [1 => false, 2 => false, 3 => false, 4 => false];

        if ($this->hasModuleStarted($user, $module(1))) {
            $starts[1] = $this->frozenStart($user, $module(1), $pivot(1), $tz);
        } else {
            $starts[1] = $today->copy();
            $pushed[1] = true;
        }

        for ($n = 2; $n <= 4; $n++) {
            $prev = $module($n - 1);

            if ($this->hasModuleStarted($user, $module($n))) {
                $starts[$n] = $this->frozenStart($user, $module($n), $pivot($n), $tz);
                continue;
            }

            $starts[$n] = $this->scheduledStart($pivot($n), $reg, $n, $tz);

            // is this even needed?

            if ($prev->isCompletedBy($user) && $pivot($n - 1)?->completed_at) {
                $dayAfterComplete = Carbon::parse($pivot($n - 1)->completed_at)
                    ->timezone($tz)->startOfDay()->addDay();
                if ($dayAfterComplete->gt($starts[$n])) {
                    $starts[$n] = $dayAfterComplete;
                }
            }

            // update next start to tomorrow, this should cover previous clause
            if (! $prev->isCompletedBy($user) && $today->gte($starts[$n])) {
                $starts[$n] = $today->copy()->addDay();
                $pushed[$n] = true;
            }

            if ($pushed[$n - 1] && $starts[$n - 1]->diffInDays($starts[$n]) < self::MIN_GAP) {
                $starts[$n] = $starts[$n - 1]->copy()->addDays(self::MIN_GAP);
                $pushed[$n] = true;
            }
        }

        return $starts;
    }

    private function ensureModuleRows(User $user): void
    {
        foreach (Module::orderBy('order')->get() as $module) {
            DB::table('user_module')->insertOrIgnore([
                'user_id' => $user->id,
                'module_id' => $module->id,
                'unlocked' => $module->order === 1,
                'completed' => false,
                'active_days_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function scheduledStart($pivot, Carbon $reg, int $order, string $tz): Carbon
    {
        if ($pivot?->start_date) {
            return Carbon::parse($pivot->start_date, $tz)->startOfDay();
        }

        // fallback
        return $reg->copy()->addDays(($order - 1) * self::IDEAL_GAP);
    }

    private function frozenStart(User $user, Module $module, $pivot, string $tz): Carbon
    {
        if ($pivot?->start_date) {
            return Carbon::parse($pivot->start_date, $tz)->startOfDay();
        }

        // get first completed activity for this module
        $completedAt = $user->activities()
            ->wherePivot('completed', true)
            ->where('optional', false)
            ->whereHas('day', fn ($query) => $query->where('module_id', $module->id))
            ->orderBy('user_activity.completed_at')
            ->first()
            ?->pivot
            ?->completed_at;

        if ($completedAt) {
            return Carbon::parse($completedAt)->timezone($tz)->startOfDay();
        }

        return $this->registrationDay($user);
    }

    private function isNewCalendarDay(User $user, Carbon $today): bool
    {
        if (! $user->last_active_at) {
            return true;
        }

        $lastActiveDay = Carbon::parse($user->last_active_at)
            ->timezone($this->timezone($user))
            ->startOfDay();

        return ! $lastActiveDay->equalTo($today);
    }

    private function timezone(User $user): string
    {
        return $user->timezone ?? config('app.timezone');
    }
}
