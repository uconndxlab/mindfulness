<?php

namespace Tests\Feature\Mail;

use App\Mail\InactivityReminder;
use App\Models\Email_Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class InactivityReminderTest extends TestCase
{

    public function test_inactivity_reminder_has_valid_subject(): void
    {
        $user = User::factory()->create();
        $mail = new InactivityReminder($user);

        $possibleSubjects = Email_Subject::where('type', 'reminder')->pluck('subject');
        
        $this->assertContains(
            $mail->envelope()->subject,
            $possibleSubjects
        );
    }
}
