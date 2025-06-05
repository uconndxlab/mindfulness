<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class XapiCheckAuthorizationToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // get auth header
        $header = $request->header('Authorization');
        if (!$header) {
            return response()->json(['error' => 'Missing Authorization header'], 401);
        }

        try {
            // decode credentials
            $decodedCredentials = base64_decode($header);

            // check if credentials are valid
            if ($decodedCredentials === false || !str_contains($decodedCredentials, ':')) {
                throw new \Exception('Invalid credentials format');
            }

            // split credentials
            [$username, $token] = explode(':', $decodedCredentials);
            if (empty($token)) {
                throw new \Exception('No token provided');
            }

            // verify token
            $token = PersonalAccessToken::findToken($token);
            if (!$token || !$token->can('xapi-statements')) {
                return response()->json(['error' => 'Invalid token'], 401);
            }
            // find user
            $user = $token->tokenable;
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            // login user
            auth()->login($user);
            
            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid authentication credentials'
            ], 401);
        }
    }
}
