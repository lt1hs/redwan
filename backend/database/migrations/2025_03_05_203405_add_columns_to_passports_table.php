<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('passports', function (Blueprint $table) {
            // Only add columns that don't exist
            if (!Schema::hasColumn('passports', 'nationality')) {
                $table->string('nationality')->nullable();
            }
            // Add other missing columns as needed
            if (!Schema::hasColumn('passports', 'residence_expiry_date')) {
                $table->date('residence_expiry_date')->nullable();
            }
            if (!Schema::hasColumn('passports', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('passports', 'passport_delivery_date')) {
                $table->date('passport_delivery_date')->nullable();
            }
            if (!Schema::hasColumn('passports', 'transaction_type')) {
                $table->string('transaction_type')->nullable();
            }
            if (!Schema::hasColumn('passports', 'payment_status')) {
                $table->string('payment_status')->nullable();
            }
            if (!Schema::hasColumn('passports', 'recipient_name')) {
                $table->string('recipient_name')->nullable();
            }
            if (!Schema::hasColumn('passports', 'zipcode')) {
                $table->string('zipcode')->nullable();
            }
            if (!Schema::hasColumn('passports', 'delivered_by')) {
                $table->string('delivered_by')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('passports', function (Blueprint $table) {
            $table->dropColumn([
                'nationality',
                'residence_expiry_date',
                'date_of_birth',
                'passport_delivery_date',
                'transaction_type',
                'payment_status',
                'recipient_name',
                'zipcode',
                'delivered_by'
            ]);
        });
    }
};