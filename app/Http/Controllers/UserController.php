<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\Activity;

class UserController extends Controller
{
    public function updateNamePass(Request $request) {
        //get user
        $user = Auth::user();

        try {
            //check for changes
            if ($request->name != $user->name || $request->password != null) {
                //validate
                $validator = Validator::make($request->all(), [
                    'name' => ['sometimes', 'string', 'max:255'],
                    'password' => ['sometimes', Password::min(8)->mixedCase()->numbers(), 'nullable'],
                    'oldPass' => ['required'],
                ], [
                    'name.max' => 'Name must be no longer than 255 characters.',
                    'oldPass.required' => 'Please enter your password to save changes.'
                ]);
    
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
    
                //check password before making updates
                if (!Hash::check($request->oldPass, $user->password)) {
                    return response()->json(['errors' => ['oldPass' => 'The password you entered is incorrect.']], 422);
                }
                if ($request->name && $request->name != $user->name) {
                    $user->name = $request->name;
                }
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();
                return response()->json(['success' => 'Your information has been updated successfully.'], 200);
            }
            else {
                return response()->json(['success' => 'No changes were made.'], 200);
            }
        }
        catch (Exception $e) {
            return response()->json(['error_message' => 'Failed to update user information.', 'error' => $e], 500);
        }
    }

    public function toggleFavorite(Request $request) {
        // get user
        $user = Auth::user();
        $activity = Activity::findOrFail($request->activity_id)->first();

        $status = $user->toggleFavoriteActivity($activity);
        if ($status) {
            return response()->json(['message' => 'Activity favorited'], 200);
        }
        else {
            return response()->json(['message' => 'Activity unfavorited'], 200);
        }
    }

    public function deleteUser(Request $request, $user_id) {
        // admin middleware protected function
        try {
            $user = User::findOrFail($user_id);
            if ($user->isAdmin()) {
                return redirect()->back()->with('error', 'Cannot delete admin account');
            }
            $user->delete();
            return redirect()->back()->with('success', 'User deleted successfully');
        }
        catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting user');
        }
    }
}
