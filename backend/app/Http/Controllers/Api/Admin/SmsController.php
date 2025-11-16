<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SmsLog; // Ensure this model exists and is correctly configured
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Validation\ValidationException; // For proper validation error handling

class SmsController extends Controller
{
    // MeliPayamak configuration properties (loaded from .env in constructor)
    protected $apiKey;
    protected $sender;
    protected $username;
    protected $password;

    // MeliPayamak API base URLs
    protected $sendUrl;
    protected $statusUrl;
    protected $messagesUrl;
    protected $creditUrl;

    public function __construct()
    {
        // Load credentials from environment variables for security
        $this->apiKey = env('MELIPAYAMAK_API_KEY');
        $this->sender = env('MELIPAYAMAK_SENDER');
        $this->username = env('MELIPAYAMAK_USERNAME');
        $this->password = env('MELIPAYAMAK_PASSWORD');

        // Define MeliPayamak API endpoints based on the latest user confirmation
        $this->sendUrl = 'https://console.melipayamak.com/api/send/simple';
        $this->statusUrl = 'https://console.melipayamak.com/api/receive/status';
        $this->messagesUrl = 'https://console.melipayamak.com/api/receive/messages';
        $this->creditUrl = 'https://console.melipayamak.com/api/receive/credit'; // Confirmed by user's Postman test

        // Log a warning if any essential credentials are missing during construction
        if (empty($this->apiKey) || empty($this->sender) || empty($this->username) || empty($this->password)) {
            Log::warning('MeliPayamak credentials are not fully configured in the .env file. SMS functionality may not work as expected.');
        }
    }

    /**
     * Send SMS message
     *
     * This method expects 'to' (phone number) and 'text' (message content) from the frontend.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        Log::info('SMS Send Request Received', ['request_payload' => $request->all()]);
        try {
            $request->validate([
                'to' => 'required|string',
                'text' => 'required|string',
                'type' => 'nullable|string',
                'related_id' => 'nullable|integer',
                'recipient_name' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            Log::error('SMS Send Validation Failed', ['errors' => $e->errors(), 'request_payload' => $request->all()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        try {
            // Format phone number to MeliPayamak's expected format (e.g., 98XXXXXXXXX)
            $recipientPhoneNumber = $this->formatPhoneNumber($request->to);

            // Prepare data for MeliPayamak API
            $data = [
                'from' => $this->sender,
                'to' => $recipientPhoneNumber,
                'text' => $request->text,
            ];

            // Send SMS via MeliPayamak API - API key in path
            $response = Http::post("{$this->sendUrl}/{$this->apiKey}", $data);

            // Log the raw response from MeliPayamak for detailed debugging
            Log::info('MeliPayamak API raw send response', [
                'request_payload_to_melipayamak' => $data,
                'response_body_from_melipayamak' => $response->body(),
                'http_status_code' => $response->status(),
            ]);

            $smsLogData = [
                'phone' => $recipientPhoneNumber,
                'message' => $request->text,
                'type' => $request->type,
                'related_id' => $request->related_id,
                'recipient_name' => $request->recipient_name,
            ];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('MeliPayamak API successful send response body', ['response_data' => $responseData]);

                $isSuccessful = ($responseData['status'] ?? '') === 'ارسال موفق بود';
                $status = $isSuccessful ? 'sent' : 'failed';
                $errorMessage = $isSuccessful ? null : ($responseData['status'] ?? 'Unknown MeliPayamak error');
                $messageId = $isSuccessful ? ($responseData['recId'] ?? null) : null; // Use recId for message_id

                $smsLogData['status'] = $status;
                $smsLogData['error'] = $errorMessage;
                $smsLogData['response_data'] = json_encode($responseData);
                $smsLogData['message_id'] = $messageId;

                $smsLog = SmsLog::create($smsLogData);

                if ($isSuccessful) {
                    return response()->json([
                        'message' => 'SMS sent successfully',
                        'log' => $smsLog,
                        'melipayamak_response' => $responseData,
                    ], 200);
                } else {
                    Log::error('MeliPayamak SMS send reported business failure', [
                        'frontend_request_data' => $request->all(),
                        'melipayamak_response' => $responseData,
                        'http_status_code' => $response->status(),
                        'error_message' => $errorMessage,
                    ]);
                    return response()->json([
                        'message' => 'Failed to send SMS via MeliPayamak (business logic)',
                        'details' => $errorMessage,
                        'log' => $smsLog,
                        'melipayamak_response' => $responseData,
                    ], 400);
                }
            } else {
                // Handle non-successful HTTP responses (e.g., 4xx, 5xx from MeliPayamak)
                $errorMessage = 'MeliPayamak API HTTP error: ' . $response->status() . ' - ' . $response->body();
                Log::error('MeliPayamak API HTTP send failure', [
                    'frontend_request_data' => $request->all(),
                    'http_status_code' => $response->status(),
                    'response_body_from_melipayamak' => $response->body(),
                    'error_message' => $errorMessage,
                ]);

                $smsLogData['status'] = 'failed';
                $smsLogData['error'] = $errorMessage;
                $smsLogData['response_data'] = json_encode(['http_error' => $response->status(), 'body' => $response->body()]);
                $smsLogData['message_id'] = null; // No message ID on HTTP failure

                $smsLog = SmsLog::create($smsLogData);

                return response()->json([
                    'message' => 'Failed to send SMS due to MeliPayamak API HTTP error.',
                    'details' => $errorMessage,
                    'log' => $smsLog,
                ], 500); // Return 500 for upstream API errors
            }

        } catch (\Throwable $e) { // Catch Throwable to include Errors, not just Exceptions
            Log::error('Critical error occurred while attempting to send SMS', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
                'phone_attempted' => $request->to,
                'frontend_request_all' => $request->all(),
            ]);

            $smsLogData = [
                'phone' => $request->to,
                'message' => $request->text,
                'status' => 'failed',
                'type' => $request->type,
                'related_id' => $request->related_id,
                'recipient_name' => $request->recipient_name,
                'error' => 'Internal server error: ' . $e->getMessage(),
                'response_data' => json_encode(['internal_error' => $e->getMessage()]),
            ];
            // Ensure smsLog is created even if some data is missing due to critical error
            try {
                $smsLog = SmsLog::create($smsLogData);
            } catch (\Exception $logE) {
                Log::critical('Failed to create SMS log entry after critical SMS send error', [
                    'original_error' => $e->getMessage(),
                    'log_creation_error' => $logE->getMessage(),
                    'sms_log_data_attempted' => $smsLogData,
                ]);
                // Fallback if logging to DB also fails
                return response()->json([
                    'message' => 'A critical internal server error occurred and logging failed.',
                    'error' => $e->getMessage(),
                ], 500);
            }


            return response()->json([
                'message' => 'An internal server error occurred while trying to send SMS.',
                'error' => $e->getMessage(),
                'log' => $smsLog,
            ], 500);
        }
    }

    /**
     * Get SMS credit from MeliPayamak API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function credit()
    {
        try {
            // Make an API call to MeliPayamak's credit endpoint using the API key
            $response = Http::get("{$this->creditUrl}/{$this->apiKey}");

            Log::info('MeliPayamak credit check raw response', [
                'response_body_from_melipayamak' => $response->body(),
                'http_status_code' => $response->status(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Adapt parsing based on user's Postman successful response: {"amount": 777, "status": "عملیات موفق"}
                $credit = $data['amount'] ?? 0;

                return response()->json([
                    'balance' => $credit,
                    'currency' => 'IRR', // Assuming Iranian Rial, adjust if needed
                    'expiry_date' => null, // MeliPayamak API might not provide this
                    'melipayamak_status' => $data['status'] ?? null // Include status for debugging/display
                ], 200);
            } else {
                Log::warning('MeliPayamak credit check API call failed or returned non-successful response', [
                    'http_status_code' => $response->status(),
                    'response_body_from_melipayamak' => $response->body()
                ]);

                return response()->json([
                    'balance' => 0,
                    'currency' => 'IRR',
                    'expiry_date' => null,
                    'error' => 'API call failed: ' . $response->status(),
                    'details' => $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('General error occurred while checking SMS credit', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'balance' => 0,
                'currency' => 'IRR',
                'expiry_date' => null,
                'error' => 'An internal server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formats a phone number for MeliPayamak.
     * This logic targets Iranian numbers (starts with 98) as MeliPayamak is an Iranian provider.
     * Examples:
     * '09121234567' -> '989121234567'
     * '+989121234567' -> '989121234567'
     * '9121234567' -> '989121234567' (if 98 is not already present)
     *
     * @param string $phone The raw phone number string.
     * @return string The formatted phone number.
     */
    protected function formatPhoneNumber($phone)
    {
        $cleaned = preg_replace('/\D/', '', $phone); // Remove all non-digits (including '+')

        // Remove leading '0' if it's a mobile number (e.g., 0912... -> 912...)
        if (str_starts_with($cleaned, '0') && strlen($cleaned) > 1) { // Check length > 1 to avoid removing '0' from single-digit numbers
            $cleaned = substr($cleaned, 1);
        }

        // If the number doesn't start with Iran's country code ('98'), prepend it.
        // This assumes all numbers should be treated as Iranian mobile numbers.
        if (!str_starts_with($cleaned, '98')) {
            $cleaned = '98' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Get SMS logs with pagination and filters
     */
    public function logs(Request $request) { /* ... existing code ... */ }
    public function recentLogs(Request $request) { /* ... existing code ... */ }
    public function retry($id) { /* ... existing code ... */ }
    public function delete($id) { /* ... existing code ... */ }
    public function statistics() { /* ... existing code ... */ }
    public function checkStatus($messageId) { /* ... existing code ... */ }
    public function getMessages(Request $request) { /* ... existing code ... */ }
}
