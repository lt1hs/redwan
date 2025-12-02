<?php

// Simple script to create user folders for existing unfinished passports
require_once 'backend/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/backend');
$dotenv->load();

// Database connection
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_DATABASE'] ?? 'database';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all unfinished passports
    $stmt = $pdo->query("SELECT id, full_name FROM unfinished_passports");
    $passports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $created = 0;
    $baseDir = __DIR__ . '/backend/public/storage/users';
    
    // Create base users directory if it doesn't exist
    if (!file_exists($baseDir)) {
        mkdir($baseDir, 0755, true);
    }
    
    foreach ($passports as $passport) {
        $userDir = $baseDir . '/' . $passport['id'];
        
        if (!file_exists($userDir)) {
            mkdir($userDir, 0755, true);
            
            // Create subfolders
            mkdir($userDir . '/personal', 0755, true);
            mkdir($userDir . '/passport', 0755, true);
            mkdir($userDir . '/residence', 0755, true);
            mkdir($userDir . '/extension', 0755, true);
            
            $created++;
            echo "Created folder for user {$passport['id']}: {$passport['full_name']}\n";
        }
    }
    
    echo "\nTotal folders created: $created\n";
    echo "User folders are ready for file management!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
