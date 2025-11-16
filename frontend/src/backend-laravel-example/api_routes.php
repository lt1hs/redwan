<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\SmsController;

// These routes should be added to your routes/api.php file
// They should be protected by authentication middleware

Route::middleware(['auth:sanctum', 'verified'])->prefix('admin')->group(function () {
    // SMS endpoints
    Route::prefix('sms')->group(function () {
        // Send SMS
        Route::post('/send', [SmsController::class, 'send']);
        
        // Get SMS logs
        Route::get('/logs', [SmsController::class, 'logs']);
        
        // Get recent SMS logs
        Route::get('/logs/recent', [SmsController::class, 'recentLogs']);
        
        // Retry failed SMS
        Route::post('/retry/{id}', [SmsController::class, 'retry']);
        
        // Delete SMS log
        Route::delete('/logs/{id}', [SmsController::class, 'delete']);
        
        // Get SMS statistics
        Route::get('/statistics', [SmsController::class, 'statistics']);
        
        // Get SMS credit (you'll need to implement this based on your provider)
        Route::get('/credit', [SmsController::class, 'credit']);
        
        // MeliPayamak specific endpoints
        Route::get('/status/{messageId}', [SmsController::class, 'checkStatus']);
        Route::get('/messages', [SmsController::class, 'getMessages']);
    });
}); 