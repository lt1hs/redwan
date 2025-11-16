<?php

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Import facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

// Allow CORS - Updated to accept multiple origins
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost:8080',
    'http://localhost:3000',
    // Add your frontend domain here if needed
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Process the POST request for logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check for the Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['message' => 'Unauthorized - No valid token provided']);
            exit();
        }
        
        // Extract the token
        $token = str_replace('Bearer ', '', $authHeader);
        
        // Find the token in the database
        $tokenModel = PersonalAccessToken::findToken($token);
        if ($tokenModel) {
            // Get the user ID for logging
            $userId = $tokenModel->tokenable_id;
            
            // Delete the token
            $tokenModel->delete();
            
            // Log the logout
            Log::info('User logged out', ['user_id' => $userId]);
        }
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Successfully logged out']);
    } catch (Exception $e) {
        // Log any exceptions
        Log::error('Logout exception', ['message' => $e->getMessage()]);
        
        // Return error response
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['message' => 'Server error: ' . $e->getMessage()]);
    }
    
    exit();
}

// Return error for unsupported methods
header('HTTP/1.1 405 Method Not Allowed');
echo json_encode(['message' => 'Method not allowed']);
exit(); 