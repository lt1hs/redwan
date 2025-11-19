<?php

require_once '/root/redwan/backend/vendor/autoload.php';

// Load Laravel environment
$app = require_once '/root/redwan/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if ($argc < 2) {
    echo "Usage: php import_csv.php <path_to_csv_file>\n";
    exit(1);
}

$csvFile = $argv[1];

if (!file_exists($csvFile)) {
    echo "Error: File not found: $csvFile\n";
    exit(1);
}

echo "Clearing existing unfinished passports data...\n";
DB::table('unfinished_passports')->truncate();

echo "Reading CSV file: $csvFile\n";

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle); // Read header row
$imported = 0;

while (($row = fgetcsv($handle)) !== FALSE) {
    $data = array_combine($header, $row);
    
    try {
        DB::table('unfinished_passports')->insert([
            'gender' => $data['Gender'] ?? null,
            'full_name' => $data['Full_Name'] ?? null,
            'passport_id' => $data['Passport_ID'] ?? null,
            'passport_number' => $data['Passport_ID'] ?? null,
            'nationality' => $data['Nationality'] ?? null,
            'date_of_birth' => !empty($data['Date_of_Birth']) ? date('Y-m-d', strtotime($data['Date_of_Birth'])) : null,
            'phone_number' => $data['Phone_Number'] ?? null,
            'mobile_number' => $data['Phone_Number'] ?? null,
            'expiration_date' => !empty($data['Expiration_Date']) ? date('Y-m-d', strtotime($data['Expiration_Date'])) : null,
            'residence_expiry_date' => !empty($data['Expiration_Date']) ? date('Y-m-d', strtotime($data['Expiration_Date'])) : null,
            'address' => $data['Address'] ?? null,
            'governorate' => $data['Governorate'] ?? null,
            'najacode' => $data['najacode'] ?? null,
            'zipcode' => $data['najacode'] ?? null,
            'completion_status' => 'مسودة',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $imported++;
        
        if ($imported % 100 == 0) {
            echo "Imported $imported records...\n";
        }
    } catch (Exception $e) {
        echo "Error importing record: " . $e->getMessage() . "\n";
        echo "Data: " . json_encode($data) . "\n";
    }
}

fclose($handle);
echo "Successfully imported $imported records.\n";
