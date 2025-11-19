<?php

require_once '/root/redwan/backend/vendor/autoload.php';

// Load Laravel environment
$app = require_once '/root/redwan/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting image migration...\n";

$records = DB::table('unfinished_passports')
    ->whereNotNull('personal_photo')
    ->orWhereNotNull('passport_photo')
    ->orWhereNotNull('residence_photo')
    ->orWhereNotNull('passport_extension_photo')
    ->get();

$migrated = 0;
$storageDir = '/root/redwan/backend/public/storage/uploads/';

foreach ($records as $record) {
    $updated = false;
    $updateData = [];
    
    $photoFields = ['personal_photo', 'passport_photo', 'residence_photo', 'passport_extension_photo'];
    
    foreach ($photoFields as $field) {
        if ($record->$field && file_exists($record->$field)) {
            $oldPath = $record->$field;
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION) ?: 'jpg';
            $newFilename = time() . '_' . $record->id . '_' . $field . '.' . $extension;
            $newPath = $storageDir . $newFilename;
            
            if (copy($oldPath, $newPath)) {
                $updateData[$field] = '/storage/uploads/' . $newFilename;
                $updated = true;
                echo "Migrated {$field} for record {$record->id}: {$oldPath} -> {$newPath}\n";
            } else {
                echo "Failed to migrate {$field} for record {$record->id}: {$oldPath}\n";
            }
        }
    }
    
    if ($updated) {
        DB::table('unfinished_passports')
            ->where('id', $record->id)
            ->update($updateData);
        $migrated++;
    }
}

echo "Migration completed. Migrated images for {$migrated} records.\n";
