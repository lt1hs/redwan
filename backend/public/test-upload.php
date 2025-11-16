<?php
// Simple PHP script to bypass Laravel middleware for testing uploads

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set up CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log request information
    $logMessage = "Request received: " . date('Y-m-d H:i:s') . "\n";
    $logMessage .= "Content-Type: " . $_SERVER['CONTENT_TYPE'] . "\n";
    
    // Log POST data
    $logMessage .= "POST data: " . print_r($_POST, true) . "\n";
    
    // Log FILES data
    $logMessage .= "FILES data: " . print_r($_FILES, true) . "\n";
    
    // Write to log file
    file_put_contents(__DIR__ . '/upload-test.log', $logMessage, FILE_APPEND);
    
    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Test upload received successfully',
        'post_data' => $_POST,
        'has_files' => !empty($_FILES),
        'files_info' => []
    ];
    
    // Process file information
    foreach ($_FILES as $key => $file) {
        if (is_uploaded_file($file['tmp_name'])) {
            $response['files_info'][$key] = [
                'name' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size']
            ];
        }
    }
    
    // Return JSON response
    echo json_encode($response);
    exit();
}

// Handle GET request (for testing the endpoint)
echo json_encode([
    'success' => true,
    'message' => 'Test upload endpoint is working'
]); 