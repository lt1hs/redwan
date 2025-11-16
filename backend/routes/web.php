<?php

use App\Http\Controllers\PublicWebsiteController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController; // <-- This import is correct

// Add this route for API health check
Route::get('/', function () {
    return response()->json(['status' => 'ok']);
});

// *** IMPORTANT: Add this route for Sanctum's CSRF cookie using explicit array syntax ***
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, '__invoke']); // <--- CHANGE THIS LINE

require __DIR__ . '/auth.php';

Route::get('download-post-categories/{ids}', [PublicWebsiteController::class, 'downloadPostCategories']);