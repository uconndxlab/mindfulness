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

        // build modal
        $modalData = $this->buildModalData($day, $hasBonus);

        session([
            'modal_data' => $modalData
        ]);
    }

    private function buildModalData(Day $day, bool $hasBonus): array
    {
        $label = "Congrats on completing {$day->name}! 🎉";
        $body = $this->buildModalBody($day, $hasBonus);
        $media = Storage::url('flowers/'.($day->media_path ? $day->media_path : ''));
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

        if ($hasBonus) {
            $modalData['route'] = route('explore.module.bonus', ['day_id' => $day->id]);
            $modalData['buttonLabel'] = 'View Bonus Activities';
        }

        return $modalData;
    }

    private function buildModalBody(Day $day, bool $hasBonus): string
    {
        $converter = app(\League\CommonMark\CommonMarkConverter::class);
        
        $bodyMessage = $day->completion_message ?? "Congratulations on completing **{$day->module->name}: {$day->name}**!";

        if ($hasBonus) {
            $bonusRoute = route('explore.module.bonus', ['day_id' => $day->id]);
            $bodyMessage .= "\n\nYou have also unlocked bonus activities for this day! Click [here]({$bonusRoute}) to view them.";
        }

        return (string) $converter->convert($bodyMessage);
    }
}
