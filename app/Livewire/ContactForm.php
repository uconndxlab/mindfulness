<?php

namespace App\Livewire;

use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Livewire\Attributes\Validate;
use Exception;

class ContactForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $subject = '';

    #[Validate('required|string|max:5000')]
    public string $message = '';

    public ?string $successMessage = null;
    public string $formKey;

    public function mount(): void
    {
        $this->formKey = uniqid();
    }

    protected function messages(): array
    {
        return [
            'subject.required' => 'Please provide a brief subject to better help us find and answer your inquiry.',
            'subject.string' => 'Invalid input.',
            'subject.max' => 'Subject must be no longer than 255 characters.',
            'message.required' => 'Please provide a message.',
            'message.string' => 'Invalid input.',
            'message.max' => 'Message must be no longer than 5000 characters.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        // throttle
        // $userIdentifier = Auth::check() ? Auth::user()->hh_id : request()->ip();
        $key = 'contact_email|' . request()->ip();
        $limit = ['attempts' => 3, 'decay' => 60]; // 3 successes per minute

        if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
            $seconds = RateLimiter::availableIn($key);
            $timeLeft = Carbon::now()->addSeconds($seconds)->diffForHumans(null, true);
            $this->addError('subject', "Too many attempts. Please try again in {$timeLeft}.");
            return;
        }

        try {
            $inquiry = new Inquiry();
            $inquiry->name = Auth::user()?->name ?? '';
            $inquiry->email = Auth::user()?->email ?? '';
            $inquiry->message = $this->message;
            $inquiry->subject = $this->subject;
            $inquiry->save();

            Mail::to(Config::get('mail.contact_email'))->send(new InquiryReceived($inquiry));

            RateLimiter::hit($key, $limit['decay']);
            
            $this->formKey = uniqid();
            $this->successMessage = 'Your inquiry has been submitted!';
            $this->reset(['subject', 'message']);
        } catch (Exception $e) {
            $this->addError('subject', 'Failed to submit inquiry. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}


