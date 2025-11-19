<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('unfinished_passports', function (Blueprint $table) {
            // Add new columns from CSV
            $table->string('gender')->nullable()->after('id');
            $table->string('passport_id')->nullable()->after('passport_number');
            $table->date('expiration_date')->nullable()->after('residence_expiry_date');
            $table->string('governorate')->nullable()->after('address');
            $table->string('najacode')->nullable()->after('zipcode');
            
            // Rename zipcode to match your CSV (keeping both for compatibility)
            // We'll map najacode to zipcode in the import
        });
    }

    public function down()
    {
        Schema::table('unfinished_passports', function (Blueprint $table) {
            $table->dropColumn(['gender', 'passport_id', 'expiration_date', 'governorate', 'najacode']);
        });
    }
};
