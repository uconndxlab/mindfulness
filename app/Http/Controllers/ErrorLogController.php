<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ErrorLogController extends Controller
{
    public function logClientError(Request $request)
    {
        $request->validate([
            'error_type' => 'required|string|max:100',
            'message' => 'required|string|max:1000',
            'user_agent' => 'nullable|string|max:500',
            'url' => 'nullable|string|max:500',
            'additional_data' => 'nullable|array'
        ]);

        $userId = Auth::id() ?? 'guest';
        $userName = Auth::user()?->name ?? 'Guest User';
        
        $logData = [
            'user_id' => $userId,
            'user_name' => $userName,
            'error_type' => $request->error_type,
            'message' => $request->message,
            'user_agent' => $request->user_agent,
            'url' => $request->url,
            // 'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString(),
            'additional_data' => $request->additional_data ?? []
        ];

        // log to laravel log with a specific prefix for easy filtering
        Log::channel('single')->info('[CLIENT_ERROR] ' . $request->error_type . ': ' . $request->message, $logData);

        return response()->json(['status' => 'logged'], 200);
    }
}
