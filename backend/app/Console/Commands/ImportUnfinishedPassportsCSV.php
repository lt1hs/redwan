<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportUnfinishedPassportsCSV extends Command
{
    protected $signature = 'import:unfinished-passports {file}';
    protected $description = 'Import unfinished passports from CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Clear existing data
        $this->info('Clearing existing unfinished passports data...');
        DB::table('unfinished_passports')->truncate();

        // Read CSV
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        
        $records = $csv->getRecords();
        $imported = 0;

        $this->info('Importing CSV data...');
        
        foreach ($records as $record) {
            try {
                DB::table('unfinished_passports')->insert([
                    'gender' => $record['Gender'] ?? null,
                    'full_name' => $record['Full_Name'] ?? null,
                    'passport_id' => $record['Passport_ID'] ?? null,
                    'passport_number' => $record['Passport_ID'] ?? null, // Map to existing field
                    'nationality' => $record['Nationality'] ?? null,
                    'date_of_birth' => !empty($record['Date_of_Birth']) ? date('Y-m-d', strtotime($record['Date_of_Birth'])) : null,
                    'phone_number' => $record['Phone_Number'] ?? null,
                    'mobile_number' => $record['Phone_Number'] ?? null, // Map to existing field
                    'expiration_date' => !empty($record['Expiration_Date']) ? date('Y-m-d', strtotime($record['Expiration_Date'])) : null,
                    'residence_expiry_date' => !empty($record['Expiration_Date']) ? date('Y-m-d', strtotime($record['Expiration_Date'])) : null, // Map to existing field
                    'address' => $record['Address'] ?? null,
                    'governorate' => $record['Governorate'] ?? null,
                    'najacode' => $record['najacode'] ?? null,
                    'zipcode' => $record['najacode'] ?? null, // Map to existing field
                    'completion_status' => 'مسودة',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $this->error("Error importing record: " . $e->getMessage());
                $this->error("Record data: " . json_encode($record));
            }
        }

        $this->info("Successfully imported {$imported} records.");
        return 0;
    }
}
