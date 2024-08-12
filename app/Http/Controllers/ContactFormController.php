<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use Validator;

class ContactFormController extends Controller
{
    public function submitForm(Request $request)
    {
        try {
            //validate
            $validator = Validator::make($request->all(), [
                'message' => ['required', 'string'],
                'subject' => ['required', 'string']
            ], [
                'message.string' => 'Invalid input.',
                'subject.required' => 'Please provide a brief subject to better help us find and answer your inquiry.',
                'subject.string' => 'Invalid input.'
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            //save to db
            $inquiry = new Inquiry();
            $inquiry->name = Auth::user()->name;
            $inquiry->email = Auth::user()->email;
            $inquiry->message = $request->message;
            $inquiry->subject = $request->subject;
            $inquiry->save();
    
            //email
            Mail::to('admin@example.com')->send(new InquiryReceived($inquiry));

            return response()->json(['success' => 'Your inquiry has been submitted!'], 200);
        }
        catch (ValidationException $e) {
            return response()->json(['error_message' => 'Failed to submit quiz answers.', 'error' => $e], 500);
        }
    }
}
