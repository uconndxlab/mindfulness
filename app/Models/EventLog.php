<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class EventLog extends SpatieActivity
{
    protected $table = 'event_log';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->casts = array_merge($this->casts, [
            'local_timestamp' => 'datetime',
        ]);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (EventLog $activity) {
            $timezone = Auth::check()
                ? (Auth::user()->timezone ?? config('app.timezone'))
                : config('app.timezone');

            $activity->local_timestamp = Carbon::now($timezone);
        });
    }
} 