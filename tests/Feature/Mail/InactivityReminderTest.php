<?php

use App\Mail\InactivityReminder;
use App\Models\Email_Body;
use App\Models\Email_Subject;
use App\Models\User;

test('the inactivity reminder uses a reminder subject', function () {
    Email_Subject::forceCreate(['type' => 'reminder', 'subject' => 'We miss you']);
    Email_Body::forceCreate(['type' => 'reminder', 'body' => 'Come back and practice.']);

    $mail = new InactivityReminder(User::factory()->create());

    expect($mail->envelope()->subject)->toBe('We miss you');
});
