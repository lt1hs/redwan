<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ApiCsrfMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log request details for debugging
        Log::info('CSRF Middleware Processing', [
            'method' => $request->method(),
            'url' => $request->url(),
            'headers' => $request->headers->all(),
            'cookies' => $request->cookies->all()
        ]);

        // Skip CSRF check for GET, HEAD, OPTIONS requests
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // Get token from header or cookie
        $token = $request->header('X-XSRF-TOKEN') ?: $request->cookie('XSRF-TOKEN');

        // If no token is present, try to get a new one
        if (!$token) {
            Log::warning('CSRF token missing, attempting to get new token');
            try {
                // Get new CSRF token
                $response = response()->json(['message' => 'CSRF token missing'], 419);
                $response->headers->set('X-CSRF-TOKEN', csrf_token());
                return $response;
            } catch (\Exception $e) {
                Log::error('Failed to get new CSRF token', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'CSRF token missing'], 419);
            }
        }

        // Verify token matches session token
        $sessionToken = $request->session()->token();
        if ($token !== $sessionToken) {
            Log::warning('CSRF token mismatch', [
                'provided' => $token,
                'expected' => $sessionToken
            ]);
            
            // Try to get a new token
            try {
                $response = response()->json(['message' => 'CSRF token mismatch'], 419);
                $response->headers->set('X-CSRF-TOKEN', csrf_token());
                return $response;
            } catch (\Exception $e) {
                Log::error('Failed to get new CSRF token', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'CSRF token mismatch'], 419);
            }
        }

        return $next($request);
    }
} 