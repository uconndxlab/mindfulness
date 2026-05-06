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

        $favorited = $request->boolean('favorited', null);

        // Preferred: set explicitly (idempotent) so clients can coalesce rapid clicks.
        // Backward compatible: if `favorited` not provided, fall back to toggle.
        if ($request->has('favorited')) {
            $status = $user->setFavoriteActivity($activity, $favorited);
        } else {
            $status = $user->toggleFavoriteActivity($activity);
        }

        return response()->json([
            'favorited' => (bool) $status,
        ], 200);
    }
}
