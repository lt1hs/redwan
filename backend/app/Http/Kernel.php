<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful; // Don't forget to import this!
use App\Http\Middleware\Cors; // Assuming this is your custom CORS middleware if you choose to use it globally.

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class, // Use Laravel's built-in HandleCors with config/cors.php
        // If you are using Barryvdh/Laravel-Cors, you would use:
        // \Barryvdh\Cors\HandleCors::class, // <-- Uncomment this if you installed barryvdh/laravel-cors and want to use it globally
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // *** CRITICAL CHANGES FOR API GROUP ***
            // 1. Ensure EnsureFrontendRequestsAreStateful is present and is the first middleware for SPA authentication.
            // 2. Removed \App\Http\Middleware\Cors::class from here (it's handled globally by \Illuminate\Http\Middleware\HandleCors or Barryvdh one).
            // 3. Removed 'api.csrf' to disable the custom CSRF middleware on API routes.
            EnsureFrontendRequestsAreStateful::class, // <--- ADD THIS FOR SANCTUM SPAs, MUST BE FIRST
            // \App\Http\Middleware\Cors::class, // <--- REMOVE THIS LINE IF YOU HAD IT HERE
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // 'api.csrf', // <--- REMOVE THIS LINE
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'api.csrf' => \App\Http\Middleware\ApiCsrfMiddleware::class, // This alias can remain if 'api.csrf' is used elsewhere (unlikely for API routes with Sanctum)
    ];
}