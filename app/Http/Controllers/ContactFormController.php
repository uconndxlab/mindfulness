<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;

class ContactFormController extends Controller
{
    public function submitForm(Request $request)
    {
        try {
            //validate
            $request->validate([
                'message' => ['required', 'string'],
                'subject' => ['required', 'string']
            ], [
                'message.string' => 'Invalid input.',
                'subject.required' => 'Please provide a brief subject to better help us find and answer your inquiry.',
                'subject.string' => 'Invalid input.'
            ]);
    
            //save to db
            $inquiry = new Inquiry();
            $inquiry->name = Auth::user()->name;
            $inquiry->email = Auth::user()->email;
            $inquiry->message = $request->message;
            $inquiry->subject = $request->subject;
            $inquiry->save();
    
            //email
            Mail::to('admin@example.com')->send(new InquiryReceived($inquiry));
    
            return back()->with([
                'success' => 'Your inquiry has been submitted!',
                'submit' => 'true',
            ]);
        }
        catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('submit', 'true');
        }
    }
}
