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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_type')->nullable()->after('contract_date');
            $table->string('husband_birth_date')->nullable()->after('husband_id_number');
            $table->string('husband_passport_number')->nullable()->after('husband_address');
            $table->string('wife_birth_date')->nullable()->after('wife_id_number');
            $table->string('wife_passport_number')->nullable()->after('wife_address');
            $table->decimal('present_dowry', 15, 2)->nullable()->after('wife_passport_number');
            $table->decimal('deferred_dowry', 15, 2)->nullable()->after('present_dowry');
            $table->text('husband_conditions_arabic')->nullable()->after('deferred_dowry');
            $table->text('husband_conditions_persian')->nullable()->after('husband_conditions_arabic');
            $table->text('wife_conditions_arabic')->nullable()->after('husband_conditions_persian');
            $table->text('wife_conditions_persian')->nullable()->after('wife_conditions_arabic');
            $table->string('first_witness')->nullable()->after('wife_conditions_persian');
            $table->string('second_witness')->nullable()->after('first_witness');

            // Drop the old dowry_amount column if it exists and is no longer needed
            if (Schema::hasColumn('contracts', 'dowry_amount')) {
                $table->dropColumn('dowry_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'contract_type',
                'husband_birth_date',
                'husband_passport_number',
                'wife_birth_date',
                'wife_passport_number',
                'present_dowry',
                'deferred_dowry',
                'husband_conditions_arabic',
                'husband_conditions_persian',
                'wife_conditions_arabic',
                'wife_conditions_persian',
                'first_witness',
                'second_witness'
            ]);

            // Re-add the old dowry_amount column if it was dropped
            $table->decimal('dowry_amount', 8, 2)->nullable();
        });
    }
};
