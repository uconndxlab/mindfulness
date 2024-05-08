<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function updateVoice(Request $request) {
        //check for voiceId
        if ($request->filled('voiceId')) {
            //get user
            $user = Auth::user();
    
            //update
            $user->voiceId = $request->voiceId;
            $user->save();
        }

        return redirect(route('explore.home'));
    }

    public function updateNamePass(Request $request) {
        //get user
        $user = Auth::user();

        //check for changes
        if ($request->name != $user->name || $request->password != null) {
            //validate
            $request->validate([
                'name'=> ['string', 'max:255'],
                'password'=> [Password::defaults(), 'nullable'],
                'oldPass'=>['required'],
            ]);
    
            //check password before making updates
            if (Hash::check($request->oldPass, $user->password)) {
                if ($request->name) {
                    $user->name = $request->name;
                }
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();
                return back()->with('success','Information updated.');
            }
            return back()->withErrors(['oldPass' => 'Incorrect password.'])->withInput();;
        } else {
            return back()->with('success', 'Nothing to change.');
        }
    }
}
