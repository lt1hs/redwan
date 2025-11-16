<?php
// Bootstrap Laravel but handle requests directly

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
    // Bootstrap Laravel
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    // Boot the application
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    // Get the database connection
    $db = $app->make('db');
    
    // Log request information
    $logMessage = "Passport API request received: " . date('Y-m-d H:i:s') . "\n";
    $logMessage .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
    $logMessage .= "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "\n";
    $logMessage .= "POST data: " . print_r($_POST, true) . "\n";
    $logMessage .= "FILES data: " . print_r($_FILES, true) . "\n";
    file_put_contents(__DIR__ . '/passport-api.log', $logMessage, FILE_APPEND);
    
    // Handle POST request for creating passport
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process file uploads manually
        $personalPhotoPath = null;
        $passportPhotoPath = null;
        
        if (isset($_FILES['personal_photo']) && $_FILES['personal_photo']['error'] === UPLOAD_ERR_OK) {
            $filename = 'personal_' . time() . '_' . $_FILES['personal_photo']['name'];
            $uploadDir = __DIR__ . '/../storage/app/public/photos/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . $filename;
            
            // Debugging: Check path before moving file
            var_dump('Attempting to move personal_photo to: ' . $uploadPath);
            exit();

            if (move_uploaded_file($_FILES['personal_photo']['tmp_name'], $uploadPath)) {
                $personalPhotoPath = 'photos/' . $filename;
            }
        }
        
        if (isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] === UPLOAD_ERR_OK) {
            $filename = 'passport_' . time() . '_' . $_FILES['passport_photo']['name'];
            $uploadDir = __DIR__ . '/../storage/app/public/photos/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['passport_photo']['tmp_name'], $uploadPath)) {
                $passportPhotoPath = 'photos/' . $filename;
            }
        }
        
        // Create the passport using the database connection
        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'nationality' => $_POST['nationality'] ?? '',
            'passport_number' => $_POST['passport_number'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'residence_expiry_date' => $_POST['residence_expiry_date'] ?? null,
            'phone_number' => $_POST['phone_number'] ?? '',
            'mobile_number' => $_POST['mobile_number'] ?? '',
            'passport_status' => $_POST['passport_status'] ?? '',
            'passport_delivery_date' => $_POST['passport_delivery_date'] ?? null,
            'transaction_type' => $_POST['transaction_type'] ?? '',
            'payment_status' => $_POST['payment_status'] ?? 'pending',
            'delivered_by' => $_POST['delivered_by'] ?? '',
            'address' => $_POST['address'] ?? '',
            'zipcode' => $_POST['zipcode'] ?? '',
            'email' => $_POST['email'] ?? null,
            'unique_code' => $_POST['unique_code'] ?? 'PC-' . uniqid(),
            'personal_photo' => $personalPhotoPath,
            'passport_photo' => $passportPhotoPath,
            'sponsor_name' => $_POST['sponsor_name'] ?? '',
            'relationship' => $_POST['relationship'] ?? '',
            'extension_reason' => $_POST['extension_reason'] ?? '',
            'barcode' => $_POST['barcode'] ?? '',
            'signature_data' => $_POST['signature_data'] ?? '',
            'no_signature' => isset($_POST['no_signature']) ? (bool)$_POST['no_signature'] : false,
            'no_signature_reason' => $_POST['no_signature_reason'] ?? '',
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Debugging: Dump the data array before insertion
        var_dump($data);
        exit();

        $id = $db->table('passports')->insertGetId($data);
        
        // Log successful creation
        $successMessage = "Passport created successfully: " . date('Y-m-d H:i:s') . "\n";
        $successMessage .= "ID: " . $id . "\n";
        $successMessage .= "Passport Number: " . $data['passport_number'] . "\n";
        file_put_contents(__DIR__ . '/passport-api-success.log', $successMessage, FILE_APPEND);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Passport created successfully',
            'data' => $data + ['id' => $id]
        ]);
        exit();
    }
    
    // Handle GET request (for testing the endpoint)
    echo json_encode([
        'success' => true,
        'message' => 'Passport API endpoint is working'
    ]);
} catch (\Exception $e) {
    // Log error
    $errorMessage = "Error in passport-api.php: " . date('Y-m-d H:i:s') . "\n";
    $errorMessage .= "Error: " . $e->getMessage() . "\n";
    $errorMessage .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    $errorMessage .= "Trace: " . $e->getTraceAsString() . "\n";
    file_put_contents(__DIR__ . '/passport-api-error.log', $errorMessage, FILE_APPEND);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
