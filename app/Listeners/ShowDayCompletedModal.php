<?php

namespace App\Listeners;

use App\Events\DayCompleted;
use App\Models\Day;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class ShowDayCompletedModal
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DayCompleted $event): void
    {
        $day = Day::find($event->day);
        $hasBonus = $day->activities->where('optional', true)->count() > 0;
        $nextDay = $day->module->days()
            ->where('order', '>', $day->order)
            ->orderBy('order')
            ->first();

        // build modal
        $modalData = $this->buildModalData($day, $hasBonus, $nextDay);

        session([
            'modal_data' => $modalData
        ]);
    }

    private function buildModalData(Day $day, bool $hasBonus, Day $nextDay = null): array
    {
        $label = "Congrats on completing {$day->name}! 🎉";
        $body = $this->buildModalBody($day, $hasBonus, $nextDay);
        $media = $day->media_path ? Storage::url('flowers/'.$day->media_path) : null;
        $route = '/home';

        $modalData = [
            'show_modal' => true,
            'label' => $label,
            'body' => $body,
            'media' => $media,
            'route' => $route,
            'method' => 'GET',
            'buttonLabel' => 'Home',
        ];

        // check in has precedence over bonus
        if ($nextDay && $nextDay->is_check_in) {
            $modalData['route'] = route('explore.module', ['module_id' => $nextDay->module_id, 'activity_id' => $nextDay->activities->first()->id]);
            $modalData['buttonLabel'] = "Go to {$nextDay->name}";
        }
        else if ($hasBonus) {
            $modalData['route'] = route('explore.module', ['module_id' => $day->module_id, 'activity_id' => $day->activities->where('optional', true)->first()->id]);
            $modalData['buttonLabel'] = 'View Bonus Activities';
        }

        return $modalData;
    }

    private function buildModalBody(Day $day, bool $hasBonus, Day $nextDay = null): string
    {
        $converter = app(\League\CommonMark\CommonMarkConverter::class);
        
        $bodyMessage = $day->completion_message ?? "Congratulations on completing **{$day->module->name}: {$day->name}**!";
        $bodyMessage .= "\n\n Return [Home](/home) to view your progress!";

        if ($nextDay && $nextDay->is_check_in) {
            $order = $nextDay->module->order;
            $nextPartOrder = $order + 1;
            $checkInRoute = route('explore.module', ['module_id' => $nextDay->module_id, 'activity_id' => $nextDay->activities->first()->id]);
            $bodyMessage .= "\n\n You have unlocked **{$nextDay->name}**! Click [here]({$checkInRoute}) to complete **{$nextDay->name}** before moving on to Part {$nextPartOrder}.";
        }
        if ($hasBonus) {
            $bonusRoute = route('explore.module', ['module_id' => $day->module_id, 'activity_id' => $day->activities->where('optional', true)->first()->id]);
            $bodyMessage .= "\n\nYou have also unlocked bonus activities for this day! Click [here]({$bonusRoute}) to view them.";
        }

        return (string) $converter->convert($bodyMessage);
    }
}
