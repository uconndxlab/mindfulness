<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Config;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use Validator;

class ContactFormController extends Controller
{
    public function submitForm(Request $request)
    {
        try {
            // throttle
            $key = sha1('contact_email|'.$request->ip().'|'.Auth::user()->email);
            $limit = ['attempts' => 3, 'decay' => 60]; // 3 successes per minute
            if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
                $seconds = RateLimiter::availableIn($key);
                $timeLeft = Carbon::now()->addSeconds($seconds)->diffForHumans(null, true);
                return response()->json(['error_message' => "Too many attempts. Please try again in {$timeLeft}."], 429);
            }

            //validate
            $validator = Validator::make($request->all(), [
                'message' => ['required', 'string'],
                'subject' => ['required', 'string']
            ], [
                'message.string' => 'Invalid input.',
                'subject.required' => 'Please provide a brief subject to better help us find and answer your inquiry.',
                'subject.string' => 'Invalid input.'
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            //save to db
            $inquiry = new Inquiry();
            $inquiry->name = Auth::user()->name;
            $inquiry->email = Auth::user()->email;
            $inquiry->message = $request->message;
            $inquiry->subject = $request->subject;
            $inquiry->save();
    
            //email
            Mail::to(Config::get('mail.contact_email'))->send(new InquiryReceived($inquiry));

            RateLimiter::hit($key, $limit['decay']);
            return response()->json(['success' => 'Your inquiry has been submitted!'], 200);
        }
        catch (ValidationException $e) {
            return response()->json(['error_message' => 'Failed to submit quiz answers.', 'error' => $e], 500);
        }
    }
}
