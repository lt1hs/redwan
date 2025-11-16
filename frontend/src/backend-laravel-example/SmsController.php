<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SmsLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    // MeliPayamak configuration
    protected $apiKey = '8bc35ff213bc4692a8d15ca3ae8a50e5';
    protected $sender = '5000xxx';
    protected $baseUrl = 'https://console.melipayamak.com/api/send/simple';
    protected $statusUrl = 'https://console.melipayamak.com/api/receive/status';
    protected $messagesUrl = 'https://console.melipayamak.com/api/receive/messages';

    /**
     * Send SMS message
     */
    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'related_id' => 'nullable|integer',
            'recipient_name' => 'nullable|string',
        ]);

        try {
            // Format phone number if needed
            $phone = $this->formatPhoneNumber($request->phone);
            
            // Prepare data for MeliPayamak API
            $data = [
                'from' => $this->sender,
                'to' => $phone,
                'text' => $request->message
            ];

            // Send SMS via MeliPayamak API
            $response = Http::post("{$this->baseUrl}/{$this->apiKey}", $data);
            
            // Log the response
            Log::info('MeliPayamak API response', [
                'response' => $response->body(),
                'status' => $response->status(),
                'phone' => $phone
            ]);
            
            // Determine status based on API response
            $status = $response->successful() ? 'sent' : 'failed';
            
            // Create SMS log
            $smsLog = SmsLog::create([
                'phone' => $phone,
                'message' => $request->message,
                'status' => $status,
                'type' => $request->type,
                'related_id' => $request->related_id,
                'recipient_name' => $request->recipient_name,
                'error' => !$response->successful() ? $response->body() : null,
                'response_data' => $response->body(),
            ]);
            
            return response()->json($smsLog, 200);
        } catch (\Exception $e) {
            Log::error('Error sending SMS', [
                'error' => $e->getMessage(),
                'phone' => $request->phone
            ]);
            
            // Create failed SMS log
            $smsLog = SmsLog::create([
                'phone' => $request->phone,
                'message' => $request->message,
                'status' => 'failed',
                'type' => $request->type,
                'related_id' => $request->related_id,
                'recipient_name' => $request->recipient_name,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Error sending SMS',
                'error' => $e->getMessage(),
                'log' => $smsLog
            ], 500);
        }
    }

    /**
     * Get SMS logs
     */
    public function logs(Request $request)
    {
        $query = SmsLog::query();
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('phone') && $request->phone) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDesc = $request->input('sort_desc', 1);
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        
        // Paginate results
        $perPage = $request->input('limit', 10);
        $logs = $query->paginate($perPage);
        
        return response()->json([
            'data' => $logs->items(),
            'total' => $logs->total(),
            'per_page' => $logs->perPage(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }

    /**
     * Get recent SMS logs
     */
    public function recentLogs(Request $request)
    {
        $limit = $request->input('limit', 5);
        $logs = SmsLog::orderBy('created_at', 'desc')->limit($limit)->get();
        
        return response()->json($logs);
    }

    /**
     * Retry sending a failed SMS
     */
    public function retry($id)
    {
        $smsLog = SmsLog::findOrFail($id);
        
        if ($smsLog->status !== 'failed') {
            return response()->json([
                'message' => 'Only failed SMS can be retried',
            ], 400);
        }
        
        try {
            // Prepare data for MeliPayamak API
            $data = [
                'from' => $this->sender,
                'to' => $smsLog->phone,
                'text' => $smsLog->message
            ];
            
            // Send SMS via MeliPayamak API
            $response = Http::post("{$this->baseUrl}/{$this->apiKey}", $data);
            
            // Determine status based on API response
            $status = $response->successful() ? 'sent' : 'failed';
            
            // Update SMS log
            $smsLog->update([
                'status' => $status,
                'error' => !$response->successful() ? $response->body() : null,
                'response_data' => $response->body(),
                'retries' => $smsLog->retries + 1,
            ]);
            
            return response()->json($smsLog);
        } catch (\Exception $e) {
            Log::error('Error retrying SMS', [
                'error' => $e->getMessage(),
                'sms_id' => $id
            ]);
            
            $smsLog->update([
                'error' => $e->getMessage(),
                'retries' => $smsLog->retries + 1,
            ]);
            
            return response()->json([
                'message' => 'Error retrying SMS',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an SMS log
     */
    public function delete($id)
    {
        $smsLog = SmsLog::findOrFail($id);
        $smsLog->delete();
        
        return response()->json(['message' => 'SMS log deleted successfully']);
    }

    /**
     * Get SMS statistics
     */
    public function statistics()
    {
        $total = SmsLog::count();
        $sent = SmsLog::where('status', 'sent')->count();
        $failed = SmsLog::where('status', 'failed')->count();
        $pending = SmsLog::where('status', 'pending')->count();
        
        // Count today's messages
        $today = SmsLog::whereDate('created_at', Carbon::today())->count();
        
        return response()->json([
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'today' => $today
        ]);
    }

    /**
     * Get SMS credit
     */
    public function credit()
    {
        try {
            // MeliPayamak username/password - replace these with your actual credentials
            // In a production environment, store these in .env
            $username = env('MELIPAYAMAK_USERNAME', 'your_username');
            $password = env('MELIPAYAMAK_PASSWORD', 'your_password');
            
            // Make an API call to MeliPayamak's getCredit endpoint
            // Documentation: https://www.melipayamak.com/webservice/documentation/
            $response = Http::post('https://rest.payamak-panel.com/api/SendSMS/GetCredit', [
                'username' => $username,
                'password' => $password
            ]);
            
            // Log the response for debugging
            Log::info('MeliPayamak credit check response', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Check if the credit value is available in the response
                // The exact structure depends on MeliPayamak's API
                $credit = $data['Value'] ?? $data['Credit'] ?? 0;
                
                return response()->json([
                    'balance' => $credit,
                    'currency' => 'IRR', // Iranian Rial for MeliPayamak
                    'expiry_date' => Carbon::now()->addDays(30)->toDateString() // Expiry date may not be provided by API
                ]);
            } else {
                // If the API call fails, return a fallback response
                Log::warning('MeliPayamak credit check failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return response()->json([
                    'balance' => 0, 
                    'currency' => 'IRR',
                    'expiry_date' => null,
                    'error' => 'API call failed: ' . $response->status()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error checking SMS credit', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'balance' => 0,
                'currency' => 'IRR',
                'expiry_date' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format phone number
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/\D/', '', $phone);
        
        // If it starts with '+', remove it (already handled by regex above)
        
        // Handle specific country codes
        if (strpos($cleaned, '98') === 0) {
            // For Saudi numbers, keep the format with country code
            return $cleaned;
        } else if (strpos($cleaned, '0') !== 0) {
            // Add leading zero if missing (for local numbers)
            $cleaned = '0' . $cleaned;
        }
        
        return $cleaned;
    }

    /**
     * Check SMS delivery status
     */
    public function checkStatus($messageId)
    {
        try {
            // Use the status API endpoint
            $response = Http::get("{$this->statusUrl}/{$this->apiKey}", [
                'messageId' => $messageId
            ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to check message status'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error checking SMS status', [
                'error' => $e->getMessage(),
                'messageId' => $messageId
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get received messages
     */
    public function getMessages(Request $request)
    {
        try {
            $response = Http::get("{$this->messagesUrl}/{$this->apiKey}", $request->all());
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to fetch messages'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error fetching messages', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 