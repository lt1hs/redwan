<?php

// Suppress all PHP notices and warnings to prevent them from appearing in API responses
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

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

header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Process the login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get input data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Log the request for debugging
        Log::info('Login attempt', ['email' => $data['email'] ?? 'not provided']);
        
        // Validate input
        if (!isset($data['email']) || !isset($data['password'])) {
            header('HTTP/1.1 422 Unprocessable Entity');
            echo json_encode(['message' => 'Email and password are required']);
            exit();
        }
        
        // Attempt authentication
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $user = Auth::user();
            
            // Create a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Check if user has Super-Admin role
            $isAdmin = $user->hasRole('Super-Admin');
            
            // Get user permissions (empty for Super-Admin)
            $permissions = !$isAdmin ? $user->getAllPermissions()->pluck('name')->toArray() : [];
            $roles = $user->roles->pluck('name')->toArray();
            
            // Log success for debugging
            Log::info('Login successful', ['user_id' => $user->id]);
            
            // Return success response
            header('Content-Type: application/json');
            echo json_encode([
                'token' => $token,
                'user' => array_merge(
                    $user->toArray(),
                    [
                        'permissions' => $permissions,
                        'roles' => $roles,
                    ]
                )
            ]);
        } else {
            // Log failed login attempt
            Log::warning('Login failed', ['email' => $data['email']]);
            
            // Return error response
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['message' => 'Invalid credentials']);
        }
    } catch (Exception $e) {
        // Log any exceptions
        Log::error('Login exception', ['message' => $e->getMessage()]);
        
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