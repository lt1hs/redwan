<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log the request method and content type for debugging
        Log::info('CORS middleware processing request', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'origin' => $request->header('Origin'),
            'url' => $request->url(),
            'headers' => $request->headers->all()
        ]);

        // Handle preflight OPTIONS requests first
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Get the origin
        $origin = $request->header('Origin');
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:8000',
            'http://localhost:3000'
        ];

        // Check if the origin is allowed
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, X-CSRF-TOKEN, Accept, Origin, Cookie');
            $response->headers->set('Access-Control-Max-Age', '86400');
            $response->headers->set('Access-Control-Expose-Headers', 'Authorization, X-CSRF-TOKEN, X-XSRF-TOKEN');
        }

        return $response;
    }
} 