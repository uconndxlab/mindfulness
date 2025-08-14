<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\Activity;

class UserController extends Controller
{
    public function toggleFavorite(Request $request) {
        // get user
        $user = Auth::user();
        $activity = Activity::findOrFail($request->activity_id) ?? null;

        $status = $user->toggleFavoriteActivity($activity);
        if ($status) {
            return response()->json(['message' => 'Activity favorited'], 200);
        }
        else {
            return response()->json(['message' => 'Activity unfavorited'], 200);
        }
    }
}
