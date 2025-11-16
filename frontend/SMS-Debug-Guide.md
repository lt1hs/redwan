# SMS Functionality Debugging Guide

This guide helps resolve common issues with the SMS functionality in the Reality360D Dashboard.

## Common Errors

### 1. 404 Not Found for SMS API Routes

```
Failed to load resource: the server responded with a status of 404 (Not Found)
Error fetching SMS credit: AxiosError
```

**Solution:**
We've implemented mock API responses for development mode. These mock handlers intercept 404 errors and provide simulated responses for SMS-related endpoints such as:
- `/api/admin/sms/credit`
- `/api/admin/sms/logs`
- `/api/admin/sms/logs/recent`
- `/api/admin/sms/send`
- `/api/admin/sms/retry/{id}`
- `/api/admin/sms/statistics`

These mocks should allow you to develop and test the SMS features without having the actual backend API endpoints ready.

### 2. CORS Errors with MeliPayamak API

```
Access to XMLHttpRequest at 'https://console.melipayamak.com/api/send/simple/...' 
has been blocked by CORS policy
```

**Solution:**
We've updated the `SmsService` to route all SMS requests through your backend Laravel API instead of calling the MeliPayamak API directly. This approach:
1. Avoids CORS issues as the API call is made server-to-server
2. Centralizes error handling and logging
3. Provides a consistent interface for the frontend

### 3. Locale Issues with QDate

```
Invalid prop: type check failed for prop "locale". Expected Object, got String with value "ar".
```

**Solution:**
We've added an `i18n.ts` boot file that:
1. Imports the Arabic locale from Quasar
2. Sets the Quasar language to Arabic
3. Is registered in the Quasar config boot array
4. Ensures QDate and other components use proper localization

### 4. Error Handling in Stores

```
TypeError: Cannot read properties of undefined (reading 'notify')
```

**Solution:**
We've improved error handling throughout the SMS-related code:
1. The `SmsService` now includes better error handling and fallbacks
2. The `sms-logs` store now manages errors internally without using external composables
3. Components now safely handle and display errors from the store
4. The notification composable uses direct Quasar Notify import rather than useQuasar

## Testing SMS Functionality

1. **Check SMS Credit:** Go to SMS Templates page and view the credit information at the top
2. **Send Test SMS:** Use the test form on the SMS Templates page
3. **Check Logs:** Navigate to SMS Logs page to see sent/failed messages
4. **Passport Notifications:** Create or edit a passport to test the notification system

## Additional Configuration

For production, make sure to:

1. Set up the actual Laravel routes in your backend API
2. Configure MeliPayamak credentials in your Laravel .env file
3. Test the real API integrations with the live MeliPayamak service

## How Development Mode Works

When in development mode, the application will:
1. Attempt to call the real API endpoints first
2. If a 404 error occurs, intercept it and provide mock data instead
3. Show console warnings when using mocked responses
4. The mock data is realistic but not persistent

This allows you to develop and test UI functionality without having the complete backend implementation.

# MeliPayamak Integration Guide

This guide provides information about the MeliPayamak SMS integration in the Reality360D Dashboard.

## MeliPayamak API Endpoints

The application uses the following MeliPayamak API endpoints:

1. **Send SMS Messages:**
   ```
   https://console.melipayamak.com/api/send/simple/8bc35ff213bc4692a8d15ca3ae8a50e5
   ```

2. **Check SMS Delivery Status:**
   ```
   https://console.melipayamak.com/api/receive/status/8bc35ff213bc4692a8d15ca3ae8a50e5
   ```

3. **Get Received Messages:**
   ```
   https://console.melipayamak.com/api/receive/messages/8bc35ff213bc4692a8d15ca3ae8a50e5
   ```

## Backend Implementation

The Laravel backend acts as a proxy for MeliPayamak API requests. This approach:

1. Avoids CORS issues by making server-to-server requests
2. Centralizes error handling and logging
3. Provides a consistent API for the frontend
4. Allows for caching and rate limiting if needed

### Laravel Routes

The following routes are implemented:

| Route | Method | Description |
|-------|--------|-------------|
| `/api/admin/sms/send` | POST | Send SMS messages |
| `/api/admin/sms/logs` | GET | Get SMS logs with pagination |
| `/api/admin/sms/logs/recent` | GET | Get recent SMS logs |
| `/api/admin/sms/retry/{id}` | POST | Retry sending a failed SMS |
| `/api/admin/sms/logs/{id}` | DELETE | Delete an SMS log |
| `/api/admin/sms/statistics` | GET | Get SMS statistics |
| `/api/admin/sms/credit` | GET | Check SMS credit |
| `/api/admin/sms/status/{messageId}` | GET | Check SMS delivery status |
| `/api/admin/sms/messages` | GET | Get received messages |

## Frontend Implementation

The frontend uses the `SmsService` class to interact with the backend API. This service provides methods for:

1. Sending SMS messages
2. Retrieving logs and statistics
3. Managing SMS credit
4. Checking delivery status
5. Fetching received messages

## Development Mode

In development mode, the application uses mock handlers to simulate API responses when the backend endpoints are not available. This allows you to develop and test SMS functionality without having the complete backend implementation.

### How Development Mode Works

1. The application attempts to call the real API endpoints first
2. If a 404 error occurs, it intercepts the request and provides mock data
3. Console warnings appear when using mocked responses
4. The mock data is realistic but not persistent

## Common Errors and Solutions

### 1. 404 Not Found for SMS API Routes

```
Failed to load resource: the server responded with a status of 404 (Not Found)
Error fetching SMS credit: AxiosError
```

**Solution:**
The mock API handlers should automatically intercept these 404 errors in development mode. If you're seeing these errors in production, make sure your Laravel backend is properly set up with the SMS routes.

### 2. CORS Errors with MeliPayamak API

```
Access to XMLHttpRequest at 'https://console.melipayamak.com/api/send/simple/...' 
has been blocked by CORS policy
```

**Solution:**
The frontend should not call MeliPayamak directly. All requests should go through the Laravel backend proxy. If you're seeing CORS errors, check that your `SmsService` is using the correct API endpoints.

### 3. Locale Issues with QDate

```
Invalid prop: type check failed for prop "locale". Expected Object, got String with value "ar".
```

**Solution:**
The i18n boot file should handle proper Arabic locale configuration for Quasar components.

## Production Setup

For production, make sure to:

1. Set your MeliPayamak API key in the Laravel `.env` file
2. Run the migration to create the SMS logs table
3. Ensure your server can make outbound HTTP requests to the MeliPayamak API
4. Test the SMS functionality with real phone numbers before going live

## API Request Examples

### Sending an SMS

```php
$data = [
    'from' => '5000xxx', // Your sender ID
    'to' => '98XXXXXXXXX', // Recipient number
    'text' => 'Your message here'
];

$response = Http::post("https://console.melipayamak.com/api/send/simple/YOUR_API_KEY", $data);
```

### Checking Delivery Status

```php
$messageId = '12345'; // The message ID returned from the send API
$response = Http::get("https://console.melipayamak.com/api/receive/status/YOUR_API_KEY", [
    'messageId' => $messageId
]);
```

### Getting Received Messages

```php
$response = Http::get("https://console.melipayamak.com/api/receive/messages/YOUR_API_KEY");
``` 