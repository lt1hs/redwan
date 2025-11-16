<?php
// Simple upload script without any Laravel dependencies

// Set up error reporting
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

try {
    // Log request information
    $logMessage = "Simple upload request received: " . date('Y-m-d H:i:s') . "\n";
    $logMessage .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
    $logMessage .= "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "\n";
    $logMessage .= "POST data: " . print_r($_POST, true) . "\n";
    $logMessage .= "FILES data: " . print_r($_FILES, true) . "\n";
    file_put_contents(__DIR__ . '/simple-upload.log', $logMessage, FILE_APPEND);
    
    // Handle POST request for testing uploads
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process file uploads manually
        $processedFiles = [];
        
        // Log all uploaded files
        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $processedFiles[$key] = [
                    'name' => $file['name'],
                    'type' => $file['type'],
                    'size' => $file['size'],
                    'status' => 'received'
                ];
                
                // We don't actually need to save the files for testing
                // This is just to confirm we can read them
                $processedFiles[$key]['content_hash'] = md5_file($file['tmp_name']);
            } else {
                $processedFiles[$key] = [
                    'error' => $file['error'],
                    'status' => 'failed'
                ];
            }
        }
        
        // Process form fields
        $processedFields = [];
        foreach ($_POST as $key => $value) {
            $processedFields[$key] = $value;
        }
        
        // Return success response with all data
        echo json_encode([
            'success' => true,
            'message' => 'Upload test successful',
            'files' => $processedFiles,
            'fields' => $processedFields,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
    
    // Handle GET request (for testing the endpoint)
    echo json_encode([
        'success' => true,
        'message' => 'Simple upload endpoint is working',
        'time' => date('Y-m-d H:i:s')
    ]);
} catch (\Exception $e) {
    // Log error
    $errorMessage = "Error in simple-upload.php: " . date('Y-m-d H:i:s') . "\n";
    $errorMessage .= "Error: " . $e->getMessage() . "\n";
    $errorMessage .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    file_put_contents(__DIR__ . '/simple-upload-error.log', $errorMessage, FILE_APPEND);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 