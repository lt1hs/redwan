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
            // Modify the transaction_type column to handle Arabic text
            $table->string('transaction_type', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passports', function (Blueprint $table) {
            // Revert the change if needed
            $table->string('transaction_type')->change();
        });
    }
}; 