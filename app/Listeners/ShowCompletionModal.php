<?php

namespace App\Listeners;

use App\Events\FinalActivityCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Storage;

class ShowCompletionModal
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
    public function handle(FinalActivityCompleted $event)
    {
        $label = 'Congrats on completing '.$event->day->name.'!';
        $body = $event->day->completion_message ?? 'Congrats on completing '.$event->day->name.'!';
        $file = $event->day->media_path ?? '';
        $media = Storage::url('content/'.$file);
        session([
            'modal_data' => [
                'show_modal' => true,
                'label' => $label,
                'body' => $body,
                'media' => $media
            ]
        ]);
    }
}
