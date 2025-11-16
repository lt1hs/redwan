# SMS Integration Setup Guide

This guide provides step-by-step instructions for setting up the SMS functionality using MeliPayamak in your Reality360D Dashboard.

## Backend Setup (Laravel)

### 1. Copy Backend Files

First, copy the following files from `src/backend-laravel-example/` to your Laravel project:

- `SmsController.php` → `app/Http/Controllers/Api/Admin/`
- `SmsLog.php` → `app/Models/`
- `create_sms_logs_table.php` → `database/migrations/yyyy_mm_dd_hhmmss_create_sms_logs_table.php` (update timestamp)

### 2. Add Routes

Add the SMS routes to your `routes/api.php` file:

```php
// SMS Management Routes
Route::middleware(['auth:sanctum', 'verified'])->prefix('admin')->group(function () {
    Route::prefix('sms')->group(function () {
        // Core SMS operations
        Route::post('/send', [SmsController::class, 'send']);
        Route::get('/logs', [SmsController::class, 'logs']);
        Route::get('/logs/recent', [SmsController::class, 'recentLogs']);
        Route::post('/retry/{id}', [SmsController::class, 'retry']);
        Route::delete('/logs/{id}', [SmsController::class, 'delete']);
        Route::get('/statistics', [SmsController::class, 'statistics']);
        Route::get('/credit', [SmsController::class, 'credit']);
        
        // MeliPayamak specific endpoints
        Route::get('/status/{messageId}', [SmsController::class, 'checkStatus']);
        Route::get('/messages', [SmsController::class, 'getMessages']);
    });
});
```

### 3. Update Environment Variables

Add the following environment variables to your `.env` file:

```
MELIPAYAMAK_API_KEY=8bc35ff213bc4692a8d15ca3ae8a50e5
MELIPAYAMAK_SENDER=5000xxx
```

Replace these values with your actual MeliPayamak credentials.

### 4. Run Migration

Run the migration to create the `sms_logs` table:

```bash
php artisan migrate
```

## Frontend Setup

### 1. Verify SMS Service

Ensure the `SmsService` class is correctly set up in your frontend project at `src/services/sms.ts`.

### 2. Check Boot Files

Make sure the following boot files are correctly set up:
- `src/boot/axios.ts` - For API requests and development mode mocking
- `src/boot/i18n.ts` - For proper Arabic locale handling

### 3. Update Quasar Config

Ensure your `quasar.config.js` file includes both boot files:

```js
boot: [
  'axios',
  'i18n'
  // ... other boot files ...
]
```

## Testing the Integration

### 1. Development Mode Testing

In development mode, the application uses mock handlers to simulate API responses. You can test the SMS functionality without having the backend API ready.

To test:
1. Go to the SMS Templates page
2. Use the test form to send a sample SMS
3. Check the browser console to see the mock API responses

### 2. Production Testing

Once you've set up the backend:

1. Update your API base URL in the axios configuration
2. Send a test SMS to a real phone number
3. Check the SMS logs in your database
4. Verify delivery status using the status API

## Troubleshooting

### Common Issues

1. **404 Errors for API Routes**
   - Check that your Laravel routes are correctly defined
   - Verify that your controllers are in the correct namespace
   - Make sure your Laravel application is running

2. **CORS Issues**
   - Ensure your Laravel CORS configuration allows requests from your frontend
   - Add the frontend domain to your `config/cors.php` file

3. **SMS Not Sending**
   - Verify your MeliPayamak credentials
   - Check that your server can make outbound HTTP requests
   - Look for errors in the Laravel logs

4. **Incorrect Phone Number Format**
   - The `formatPhoneNumber` method should handle most cases
   - You might need to adjust it for your specific country codes

5. **UI Display Issues**
   - Make sure the i18n configuration is correct for Arabic support
   - Check that all Quasar components have proper RTL support

## MeliPayamak API Documentation

For more information about the MeliPayamak API, refer to their official documentation:

- API Key: Your personal API key is `8bc35ff213bc4692a8d15ca3ae8a50e5`
- Send SMS: `https://console.melipayamak.com/api/send/simple/YOUR_API_KEY`
- Check Status: `https://console.melipayamak.com/api/receive/status/YOUR_API_KEY`
- Get Messages: `https://console.melipayamak.com/api/receive/messages/YOUR_API_KEY`

## Additional Resources

- [SMS-Debug-Guide.md](./SMS-Debug-Guide.md) - For detailed debugging information
- [README-SMS.md](./README-SMS.md) - For general SMS functionality documentation 