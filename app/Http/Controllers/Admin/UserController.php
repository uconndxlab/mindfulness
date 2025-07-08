<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Mail;

class UserController extends Controller
{
    public function dashboard()
    {
        $registration_locked = getConfig('registration_locked', false);
        return view('admin.dashboard', compact('registration_locked'));
    }
    public function index()
    {
        return view('admin.users');
    }

    public function changeAccess(Request $request) {
        try {
            $user = User::findOrFail($request->user_id);
            $user->update(['lock_access' => !$user->lock_access]);

            return response()->json(['success' => 'Access updated for '.$user->email, 'status' => $user->lock_access], 200);
        }
        catch (\Exception $e) {
            return response()->json(['error_message' => 'Failed to change access.', 'error' => $e], 500);
        }
    }

    public function emailRemindUser(Request $request) {
        try {
            $user = User::findOrFail($request->user_id);

            $remind_limit = (int) config('mail.remind_email_day_limit');
            $last_active = $user->last_active_at ? Carbon::parse($user->last_active_at) : null;
            $last_reminded = $user->last_reminded_at ? Carbon::parse($user->last_reminded_at) : null;

            //check if user locked
            if ($user->lock_access) {
                return response()->json(['error_message' => 'User access is locked.'], 400);
            }

            //check if user active or reminded within the limit
            if (($last_active && $last_active->diffInDays(Carbon::now()) < $remind_limit) || 
                ($last_reminded && $last_reminded->diffInDays(Carbon::now()) < $remind_limit)) {
                return response()->json(['error_message' => 'User has been active or reminded within the limit.'], 400);
            }
            
            Mail::to($user->email)->send(new \App\Mail\InactivityReminder($user));
            $user->last_reminded_at = Carbon::now();
            $user->save();

            return response()->json(['success' => 'Reminder email sent to '.$user->email], 200);
        }
        catch (\Exception $e) {
            return response()->json(['error_message' => 'Failed to send reminder email.', 'error' => $e], 500);
        }
    }

    public function lockRegistrationAccess(Request $request) {
        try {
            $locked = getConfig('registration_locked', false);
            updateConfig('registration_locked', !$locked);
            $msg = !$locked ? 'locked' : 'unlocked';
            return response()->json(['success' => 'Registration has been '.$msg.'!', 'status' => !$locked], 200);
        }
        catch (\Exception $e) {
            return response()->json(['error_message' => 'Failed to change registration access.', 'error' => $e], 500);
        }
    }
}
