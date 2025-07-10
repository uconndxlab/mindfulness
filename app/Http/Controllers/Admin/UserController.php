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

    public function events()
    {
        return view('admin.events');
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
