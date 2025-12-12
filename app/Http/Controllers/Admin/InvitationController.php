<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvitationEmail;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller
{
    public function index()
    {
        $registration_locked = getConfig('registration_locked', false);
        $invitation_only_mode = getConfig('invitation_only_mode', false);
        
        return view('admin.invitations', compact('registration_locked', 'invitation_only_mode'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email:rfc,dns', 'max:255'],
            ], [
                'email.required' => 'Please enter an email address.',
                'email.email' => 'Not a valid email address.',
                'email.max' => 'Email must be no longer than 255 characters.',
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            // check if user exists
            $userExists = User::where('email', $request->email)->exists();
            if ($userExists) {
                return back()->withErrors(['email' => 'A user with this email already exists.'])->withInput();
            }

            // check if inv exists for this email
            $existingInvitation = Invitation::where('email', $request->email)
                ->where('status', 'pending')
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($existingInvitation) {
                return back()->withErrors(['email' => 'An active invitation already exists for this email.'])->withInput();
            }

            // calculate expiration date
            $expirationDays = (int) config('invitations.expiration_days', 7);
            $expiresAt = Carbon::now()->addDays($expirationDays);

            // create invitation
            $invitation = Invitation::create([
                'email' => $request->email,
                'token' => Invitation::generateUniqueToken(),
                'invited_by' => Auth::id(),
                'status' => 'pending',
                'expires_at' => $expiresAt,
            ]);

            // send invitation email
            Mail::to($invitation->email)->send(new InvitationEmail($invitation));

            return back()->with('success', 'Invitation sent successfully to ' . $invitation->email . '!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send invitation. Please try again.'])->withInput();
        }
    }

    public function toggleInvitationMode(Request $request)
    {
        try {
            $invitationMode = getConfig('invitation_only_mode', false);
            updateConfig('invitation_only_mode', !$invitationMode);
            $msg = !$invitationMode ? 'enabled' : 'disabled';
            return response()->json(['success' => 'Invitation-only mode has been ' . $msg . '!', 'status' => !$invitationMode], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to toggle invitation mode.'], 500);
        }
    }
}
