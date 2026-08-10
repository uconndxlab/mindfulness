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

    public function allFlowersHaveBloomed(User $user, array $schedule): bool
    {
        return $schedule['modules']->every(fn (Module $module) => $module->isCompletedBy($user));
    }

    public function gentleIntentionHeading(User $user, array $schedule, ?Module $currentModule): string
    {
        $today = $schedule['today'];
        $completions = $schedule['completions'];

        if ($this->allFlowersHaveBloomed($user, $schedule)) {
            return 'All Flowers Have Bloomed';
        }

        if ($featuredCompletedModule = $this->completedModuleAwaitingNextStart($user, $schedule)) {
            return 'Your '.$featuredCompletedModule->flowerColorName().' Flower Has Bloomed!';
        }

        if (! $currentModule) {
            return 'Keep Growing your flower';
        }

        $order = $currentModule->order;
        $color = $currentModule->flowerColorName();
        $started = $this->scheduleService->hasModuleStarted($user, $currentModule);

        if (! $started) {
            return 'Start Growing your '.$color.' Flower';
        }

        // if the flower is to be completed today
        if ($today->equalTo($completions[$order])) {
            return 'Finish Growing your '.$color.' Flower';
        }

        return 'Keep Growing your '.$color.' Flower';
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
