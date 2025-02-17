<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        // throttle password reset email requests
        $key = sha1('password_reset|'.$request->ip());
        $limit = ['attempts' => 4, 'decay' => 60]; // 4 successes per minute
        if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
            $seconds = RateLimiter::availableIn($key);
            $timeLeft = Carbon::now()->addSeconds($seconds)->diffForHumans(null, true);
            return back()->withErrors(['error' => "Too many attempts. Please try again in {$timeLeft}."]);
        }

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        // count if the response was successful
        if ($response === \Password::RESET_LINK_SENT) {
            RateLimiter::hit($key, $limit['decay']);
        }

        return $response === \Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
