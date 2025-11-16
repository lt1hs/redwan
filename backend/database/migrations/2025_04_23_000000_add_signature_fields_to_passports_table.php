<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('passports', function (Blueprint $table) {
            // Add signature related fields
            $table->text('signature_data')->nullable()->after('extension_reason');
            $table->boolean('no_signature')->default(false)->after('signature_data');
            $table->string('no_signature_reason')->nullable()->after('no_signature');
            
            // Add barcode field
            $table->string('barcode')->nullable()->after('unique_code');
            
            // Add email field if it doesn't exist
            if (!Schema::hasColumn('passports', 'email')) {
                $table->string('email')->nullable()->after('mobile_number');
            }
            
            // Update payment_status enum to match frontend values
            if (Schema::hasColumn('passports', 'payment_status')) {
                // We'll create a new column with the updated enum values
                $table->dropColumn('payment_status');
            }
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->after('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passports', function (Blueprint $table) {
            $table->dropColumn([
                'signature_data',
                'no_signature',
                'no_signature_reason',
                'barcode'
            ]);
            
            // Restore the original payment_status
            $table->dropColumn('payment_status');
            $table->enum('payment_status', ['تم', 'لم يتم', 'يدفع لاحقا'])->after('transaction_type');
        });
    }
}; 