<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\Module;
use App\Models\Day;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Mail;

class ContentManagementController extends Controller
{
    public function adminLanding(Request $request) {
        $title = "Admin";
        $head = "Admin Options";
        $back_route = route('account');
        return view('admin.landing', compact('title', 'head', 'back_route'));
    }

    public function usersList(Request $request) {
        $registration_locked = getConfig('registration_locked', false);
        $users = User::orderBy('last_active_at', 'desc')->get();
        foreach ($users as $user) {
            $user->formatted_time = 'inactive';
            if ($user->last_active_at) {
                $date = Carbon::parse($user->last_active_at);
                $date->setTimezone(new \DateTimeZone('EST'));
                $user->formatted_time = $date->diffForHumans().', '.$date;
            }
        }
        $title = "Admin: Access Control";
        $head = "Access Control";
        $page_info = [
            'hide_bottom_nav' => true,
            'back_route' => route('admin.landing'),
            'back_label' => 'Admin Landing',
        ];
        return view('admin.users', compact('users', 'title', 'head', 'page_info', 'registration_locked'));
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

    public function emailTesting(Request $request, $type) {
        try {
            $email = config('mail.test_email');
            $user = User::where('email', $email)->first();

            if ($request->type == 'reminder') {
                Mail::to($user->email)->send(new \App\Mail\InactivityReminder($user));
            }
            else if ($request->type == 'contact') {
                $inquiry = Inquiry::create([
                    'name' => 'Test Inquiry',
                    'email' => $user->email,
                    'subject' => 'Test Inquiry Subject',
                    'message' => 'Test Inquiry Message',
                ]);

                Mail::to($user->email)->send(new \App\Mail\InquiryReceived($inquiry));
            }
            // force send built in verification email
            else if ($request->type == 'verification') {
                $user->email_verified_at = null;
                $user->save();
                $user->sendEmailVerificationNotification();
            }
            else {
                return response()->json(['error_message' => 'Invalid email type.'], 400);
            }
        }
        catch (\Exception $e) {
            return response()->json(['error_message' => 'Failed to send test email.', 'error' => $e], 500);
        }
    }

    public function registrationAccess(Request $request) {
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
