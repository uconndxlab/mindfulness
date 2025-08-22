<?php

namespace App\Listeners;

use App\Events\BonusUnlocked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ShowBonusModal
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
    public function handle(BonusUnlocked $event)
    {
        $label = 'Bonus activities unlocked! 🎉';
        $body = (string) app(\League\CommonMark\CommonMarkConverter::class)->convert('Congratulations on completing: **'.$event->day->module->name.': '.$event->day->name.'**! You have unlocked bonus activities.');
        
        $route = route('explore.module.bonus', [
            'day_id' => $event->day->id
        ]);
        
        $route_label = '<i class="bi bi-gift me-2"></i>View Bonus Activities';
        session([
            'modal_data' => [
                'show_modal' => true,
                'label' => $label,
                'body' => $body,
                'route' => $route,
                'method' => 'GET',
                'buttonLabel' => $route_label
            ]
        ]);
    }
}
