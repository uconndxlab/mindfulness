<?php

namespace App\Support;

use App\Models\Module;
use App\Models\User;
use App\Services\ModuleScheduleService;
use Carbon\Carbon;

class HomeDateFormatter
{
    public function __construct(
        private ModuleScheduleService $scheduleService,
    ) {}

    public function moduleStatusText(
        User $user,
        Module $module,
        Carbon $startDate,
        Carbon $completionDate,
        bool $completed,
        bool $unlocked,
        bool $started,
    ): string {
        $today = $this->scheduleService->today($user);

        if ($completed) {
            $pivot = $user->modules()->where('module_id', $module->id)->first()?->pivot;
            $completedDate = $pivot?->completed_at
                ? Carbon::parse($pivot->completed_at)->timezone($this->timezone($user))->startOfDay()
                : $completionDate;

            return $this->completedLabel($user, $completedDate);
        }

        if ($unlocked && ! $started) {
            return $this->startLabel($user, $startDate);
        }

        if ($unlocked && $started) {
            return $this->finishLabel($user, $completionDate);
        }

        return 'To Start '.$this->absoluteDate($user, $startDate);
    }

    public function featuredModule(User $user, array $schedule, ?Module $currentModule): ?Module
    {
        return $this->completedModuleAwaitingNextStart($user, $schedule) ?? $currentModule;
    }

    public function allModulesCompleted(User $user, array $schedule): bool
    {
        return $schedule['modules']->every(fn (Module $module) => $module->isCompletedBy($user));
    }

    public function todayGoalData(User $user, array $schedule, ?Module $currentModule, iterable $modules): array
    {
        $today = $schedule['today'];
        $completions = $schedule['completions'];

        // user has completed 5 days, is on self reflection
        // flower has bloomed, still need to finish module
        if ($reflectionPrompt = $this->reflectionPrompt($currentModule)) {
            return $reflectionPrompt;
        }

        if ($this->allModulesCompleted($user, $schedule)) {
            return [
                'text' => 'All Flowers Have Bloomed',
                'linkText' => null,
                'moduleId' => null,
                'activityId' => null,
            ];
        }

        if ($featuredCompletedModule = $this->completedModuleAwaitingNextStart($user, $schedule)) {
            return [
                'text' => 'Your '.$featuredCompletedModule->flowerColorName().' Flower Has Bloomed!',
                'linkText' => null,
                'moduleId' => null,
                'activityId' => null,
            ];
        }

        if (! $currentModule) {
            return [
                'text' => 'Keep Growing your flower',
                'linkText' => null,
                'moduleId' => null,
                'activityId' => null,
            ];
        }

        $order = $currentModule->order;
        $color = $currentModule->flowerColorName();
        $started = $this->scheduleService->hasModuleStarted($user, $currentModule);

        if (! $started) {
            return [
                'text' => 'Start Growing your '.$color.' Flower',
                'linkText' => null,
                'moduleId' => null,
                'activityId' => null,
            ];
        }

        // if the flower is to be completed today
        if ($today->equalTo($completions[$order])) {
            return [
                'text' => 'Finish Growing your '.$color.' Flower',
                'linkText' => null,
                'moduleId' => null,
                'activityId' => null,
            ];
        }

        return [
            'text' => 'Keep Growing your '.$color.' Flower',
            'linkText' => null,
            'moduleId' => null,
            'activityId' => null,
        ];
    }

    private function reflectionPrompt(?Module $module): ?array
    {
        if (! $module || $module->completed || ($module->daysCompleted ?? 0) < FlowerAssets::MAX_PETALS) {
            return null;
        }

        $reflectionDay = $module->days()->where('is_check_in', true)->orderBy('order')->first();
        $reflectionActivity = $reflectionDay?->activities()->where('optional', false)->orderBy('order')->first();

        return [
            'text' => 'Your '.$module->flowerColorName().' Flower Has Bloomed!',
            'linkText' => $reflectionActivity ? 'Please Reflect on Part '.$module->order : null,
            'moduleId' => $reflectionActivity ? $module->id : null,
            'activityId' => $reflectionActivity?->id,
        ];
    }

    private function completedModuleAwaitingNextStart(User $user, array $schedule): ?Module
    {
        $modules = $schedule['modules'];
        $today = $schedule['today'];
        $starts = $schedule['starts'];

        $lastCompleted = $modules
            ->filter(fn (Module $module) => $module->isCompletedBy($user))
            ->sortByDesc('order')
            ->first();

        if (! $lastCompleted) {
            return null;
        }

        $nextModule = $modules->firstWhere('order', $lastCompleted->order + 1);

        if (! $nextModule) {
            return $lastCompleted;
        }

        if (
            $today->lt($starts[$nextModule->order])
            && ! $this->scheduleService->hasModuleStarted($user, $nextModule)
        ) {
            return $lastCompleted;
        }

        return null;
    }

    private function completedLabel(User $user, Carbon $date): string
    {
        $relative = $this->scheduleService->formatDate($date, $user);

        if ($relative === 'Yesterday') {
            return 'Completed Yesterday!';
        }

        return 'Completed on '.$this->absoluteDate($user, $date);
    }

    private function startLabel(User $user, Carbon $date): string
    {
        $relative = $this->scheduleService->formatDate($date, $user);

        return match ($relative) {
            'Today' => 'Start Today!',
            'Tomorrow' => 'Start Tomorrow!',
            default => 'To Start '.$this->absoluteDate($user, $date),
        };
    }

    private function finishLabel(User $user, Carbon $date): string
    {
        $relative = $this->scheduleService->formatDate($date, $user);

        return match ($relative) {
            'Today' => 'Finish Today!',
            'Tomorrow' => 'Finish Tomorrow!',
            default => 'To Finish '.$this->absoluteDate($user, $date),
        };
    }

    private function absoluteDate(User $user, Carbon $date): string
    {
        return $date->copy()->timezone($this->timezone($user))->format('F j');
    }

    private function timezone(User $user): string
    {
        return $user->timezone ?? config('app.timezone');
    }
}
