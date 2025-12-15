<?php

namespace App\Http\Middleware;

use App\Models\Invitation;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInvitationRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if invitation-only mode is enabled
        $invitationOnlyMode = getConfig('invitation_only_mode', false);
        
        // if invitation-only mode is disabled, allow access
        if (!$invitationOnlyMode) {
            return $next($request);
        }

        // check for invitation token in query string
        $token = $request->query('token') ?? $request->session()->get('invitation_token');

        // if no token provided, redirect to login with error
        if (!$token) {
            return redirect()->route('login')->withErrors([
                'credentials' => 'Registration is currently invitation-only. Please check your email for an invitation link.'
            ]);
        }

        // validate the token
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            $request->session()->forget(['invitation_token', 'invitation_email']);
            return redirect()->route('login')->withErrors([
                'credentials' => 'Invalid invitation link.'
            ]);
        }

        // check if invitation is valid
        if (!$invitation->isValid()) {
            // clear session data for invalid cases
            $request->session()->forget(['invitation_token', 'invitation_email']);
            
            if ($invitation->status === 'expired' || $invitation->expires_at->isPast()) {
                return redirect()->route('login')->withErrors([
                    'credentials' => 'This invitation has expired.'
                ]);
            } elseif ($invitation->status === 'accepted') {
                return redirect()->route('login')->withErrors([
                    'credentials' => 'This invitation has already been used.'
                ]);
            } elseif ($invitation->status === 'revoked') {
                return redirect()->route('login')->withErrors([
                    'credentials' => 'This invitation has been revoked.'
                ]);
            }
        }

        // store valid token in session for use during registration
        $request->session()->put('invitation_token', $token);
        $request->session()->put('invitation_email', $invitation->email);

        return $next($request);
    }
}
