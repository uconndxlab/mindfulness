<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class ContactFormController extends Controller
{
    public function submitForm(Request $request)
    {
        try {
            $previous_url = URL::previous();
            $redirect = $previous_url.'#contactUs';

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

            return redirect($redirect)->with([
                'success' => 'Your inquiry has been submitted!'
            ]);
        }
        catch (ValidationException $e) {
            return redirect($redirect)->withErrors($e->errors())->withInput();
        }
    }
}
