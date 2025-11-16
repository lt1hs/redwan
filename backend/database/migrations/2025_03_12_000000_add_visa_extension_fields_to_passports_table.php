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
            $table->string('sponsor_name')->nullable()->after('transaction_type');
            $table->string('relationship')->nullable()->after('sponsor_name');
            $table->text('extension_reason')->nullable()->after('relationship');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passports', function (Blueprint $table) {
            $table->dropColumn(['sponsor_name', 'relationship', 'extension_reason']);
        });
    }
}; 