<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function updateVoice(Request $request) {
        //check for voiceId
        if ($request->filled('voiceId')) {
            //get user
            $user = Auth::user();
    
            //update
            $user->voiceId = $request->input('voiceId');
            $user->save();
        }

        return redirect(route('explore.home'));
    }
}
