<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Module;
use App\Models\User;
use App\Services\ModuleScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BackfillModuleSchedules extends Command
{
    protected $signature = 'users:backfill-module-schedules';

    protected $description = 'Backfill module start dates and active day counters for existing users';

    public function handle(ModuleScheduleService $scheduleService): int
    {
        $modules = Module::orderBy('order')->get();

        foreach (User::all() as $user) {
            $timezone = $user->timezone ?? config('app.timezone');
            $backfillActiveDays = ($user->active_days_count ?? 0) <= 1;
            $moduleStartDates = [];

            foreach ($modules as $module) {
                if ($scheduleService->hasModuleStarted($user, $module)) {
                    $firstCompletedAt = Activity::query()
                        ->where('optional', false)
                        ->whereHas('day', fn ($query) => $query->where('module_id', $module->id))
                        ->whereHas('users', fn ($query) => $query
                            ->where('users.id', $user->id)
                            ->where('user_activity.completed', true))
                        ->join('user_activity', 'activities.id', '=', 'user_activity.activity_id')
                        ->where('user_activity.user_id', $user->id)
                        ->orderBy('user_activity.completed_at')
                        ->value('user_activity.completed_at');

                    $moduleStartDates[$module->id] = $firstCompletedAt
                        ? Carbon::parse($firstCompletedAt)->timezone($timezone)->startOfDay()->toDateString()
                        : null;

                    DB::table('user_module')
                        ->where('user_id', $user->id)
                        ->where('module_id', $module->id)
                        ->update([
                            'start_date' => $moduleStartDates[$module->id],
                            'updated_at' => now(),
                        ]);
                } else {
                    $moduleStartDates[$module->id] = null;

                    DB::table('user_module')
                        ->where('user_id', $user->id)
                        ->where('module_id', $module->id)
                        ->update([
                            'start_date' => null,
                            'updated_at' => now(),
                        ]);
                }
            }

            if ($backfillActiveDays) {
                $activeDayCounts = $this->computeActiveDayCounts(
                    $user->id,
                    $timezone,
                    $moduleStartDates,
                );

                $user->active_days_count = $activeDayCounts['user'];

                foreach ($modules as $module) {
                    DB::table('user_module')
                        ->where('user_id', $user->id)
                        ->where('module_id', $module->id)
                        ->update([
                            'active_days_count' => $activeDayCounts['modules'][$module->id] ?? 0,
                            'updated_at' => now(),
                        ]);
                }
            }

            $user->save();

            $scheduleService->syncForUser($user->fresh());

            $this->line("Backfilled schedule for user {$user->id}");
        }

        $this->info('Module schedule backfill complete.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string|null>  $moduleStartDates
     * @return array{user: int, modules: array<int, int>}
     */
    private function computeActiveDayCounts(
        int $userId,
        string $timezone,
        array $moduleStartDates,
    ): array {
        $completions = DB::table('user_activity')
            ->join('activities', 'user_activity.activity_id', '=', 'activities.id')
            ->join('days', 'activities.day_id', '=', 'days.id')
            ->where('user_activity.user_id', $userId)
            ->where('user_activity.completed', true)
            ->whereNotNull('user_activity.completed_at')
            ->where('activities.optional', false)
            ->select('days.module_id', 'user_activity.completed_at')
            ->get();

        /** @var Collection<int, string> $userDays */
        $userDays = collect();
        /** @var array<int, Collection<int, string>> $moduleDays */
        $moduleDays = [];

        foreach ($completions as $completion) {
            $day = Carbon::parse($completion->completed_at)->timezone($timezone)->startOfDay();
            $dateString = $day->toDateString();

            $userDays->push($dateString);

            $startDate = $moduleStartDates[$completion->module_id] ?? null;

            if ($startDate !== null && $day->lt(Carbon::parse($startDate, $timezone)->startOfDay())) {
                continue;
            }

            $moduleDays[$completion->module_id] ??= collect();
            $moduleDays[$completion->module_id]->push($dateString);
        }

        return [
            'user' => $userDays->unique()->count(),
            'modules' => collect($moduleStartDates)
                ->mapWithKeys(fn ($startDate, $moduleId) => [
                    $moduleId => ($moduleDays[$moduleId] ?? collect())->unique()->count(),
                ])
                ->all(),
        ];
    }
}
