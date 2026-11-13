<?php

use App\Models\EventLog;
use App\Models\User;

test('activity logging writes an event log with a local timestamp', function () {
    config(['activitylog.enabled' => true]);
    $user = User::factory()->create(['timezone' => 'America/New_York']);
    $this->actingAs($user);

    activity('auth')->event('login')->causedBy($user)->withProperties(['source' => 'test'])->log('Authenticated');

    $log = EventLog::sole();

    expect($log->causer->is($user))->toBeTrue()
        ->and($log->properties['source'])->toBe('test')
        ->and($log->local_timestamp)->toBeInstanceOf(DateTimeInterface::class);
});
