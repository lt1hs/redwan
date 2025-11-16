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

header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Process the GET request for user data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Check for the Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            Log::warning('User data request missing Authorization header', [
                'headers' => getallheaders(),
                'auth_header' => $authHeader
            ]);
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['message' => 'Unauthorized - No valid token provided']);
            exit();
        }
        
        // Extract the token
        $token = str_replace('Bearer ', '', $authHeader);
        Log::info('Token received', ['token_prefix' => substr($token, 0, 10) . '...']);
        
        // Find the token in the database
        $tokenModel = PersonalAccessToken::findToken($token);
        if (!$tokenModel) {
            Log::warning('Invalid token used for authentication', [
                'token_prefix' => substr($token, 0, 10) . '...',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['message' => 'Unauthorized - Invalid token']);
            exit();
        }
        
        // Get the token's owner
        $user = $tokenModel->tokenable;
        if (!$user) {
            Log::warning('Token without valid user', [
                'token_id' => $tokenModel->id,
                'tokenable_type' => $tokenModel->tokenable_type,
                'tokenable_id' => $tokenModel->tokenable_id
            ]);
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['message' => 'Unauthorized - User not found']);
            exit();
        }
        
        // Check if user has Super-Admin role
        $isAdmin = $user->hasRole('Super-Admin');
        
        // Get user permissions (empty for Super-Admin)
        $permissions = !$isAdmin ? $user->getAllPermissions()->pluck('name')->toArray() : [];
        $roles = $user->roles->pluck('name')->toArray();
        
        // Log success for debugging
        Log::info('User data fetched', ['user_id' => $user->id]);
        
        // Return user data
        header('Content-Type: application/json');
        echo json_encode([
            'user' => array_merge(
                $user->toArray(),
                [
                    'permissions' => $permissions,
                    'roles' => $roles,
                ]
            )
        ]);
    } catch (Exception $e) {
        // Log any exceptions
        Log::error('User data exception', ['message' => $e->getMessage()]);
        
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