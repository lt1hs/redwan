<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 1. Change existing columns to nullable
            // These were 'required' in your controller, but NOT NULL in DB, causing the error.
            $table->string('contract_place')->nullable()->change();
            $table->string('husband_id_number')->nullable()->change();
            $table->string('husband_phone')->nullable()->change(); // You made this nullable in the controller too
            $table->string('wife_id_number')->nullable()->change();
            $table->string('wife_phone')->nullable()->change(); // You made this nullable in the controller too
            $table->string('officiant_name')->nullable()->change();

            // 2. Add columns that are in your controller's validation but missing from your old create_contracts_table migration.
            // These lines prevent errors if the column already exists.
            if (!Schema::hasColumn('contracts', 'contract_type')) {
                $table->string('contract_type')->nullable()->after('contract_date');
            }
            if (!Schema::hasColumn('contracts', 'husband_birth_date')) {
                $table->date('husband_birth_date')->nullable()->after('husband_id_number');
            }
            if (!Schema::hasColumn('contracts', 'husband_passport_number')) {
                $table->string('husband_passport_number')->nullable()->after('husband_phone');
            }
            if (!Schema::hasColumn('contracts', 'wife_birth_date')) {
                $table->date('wife_birth_date')->nullable()->after('wife_id_number');
            }
            if (!Schema::hasColumn('contracts', 'wife_passport_number')) {
                $table->string('wife_passport_number')->nullable()->after('wife_phone');
            }
            // If your controller uses 'present_dowry' and 'deferred_dowry' instead of 'dowry_amount',
            // you might need to drop 'dowry_amount' and add these. This is more complex if you have existing data.
            // For now, let's assume 'dowry_amount' is present_dowry or you'll handle it.
            // If 'present_dowry' and 'deferred_dowry' are new columns required by the controller
            if (!Schema::hasColumn('contracts', 'present_dowry')) {
                $table->decimal('present_dowry', 10, 2)->nullable()->after('dowry_amount');
            }
            if (!Schema::hasColumn('contracts', 'deferred_dowry')) {
                $table->decimal('deferred_dowry', 10, 2)->nullable()->after('present_dowry'); // Or after 'dowry_amount' if no present_dowry
            }

            if (!Schema::hasColumn('contracts', 'husband_conditions_arabic')) {
                $table->text('husband_conditions_arabic')->nullable();
            }
            if (!Schema::hasColumn('contracts', 'husband_conditions_persian')) {
                $table->text('husband_conditions_persian')->nullable();
            }
            if (!Schema::hasColumn('contracts', 'wife_conditions_arabic')) {
                $table->text('wife_conditions_arabic')->nullable();
            }
            if (!Schema::hasColumn('contracts', 'wife_conditions_persian')) {
                $table->text('wife_conditions_persian')->nullable();
            }
            if (!Schema::hasColumn('contracts', 'first_witness')) {
                $table->string('first_witness')->nullable();
            }
            if (!Schema::hasColumn('contracts', 'second_witness')) {
                $table->string('second_witness')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Revert existing columns to NOT nullable (be careful if there are existing NULLs)
            // This might fail if you have actual NULL values in these columns after running up()
            $table->string('contract_place')->nullable(false)->change();
            $table->string('husband_id_number')->nullable(false)->change();
            $table->string('husband_phone')->nullable(false)->change();
            $table->string('wife_id_number')->nullable(false)->change();
            $table->string('wife_phone')->nullable(false)->change();
            $table->string('officiant_name')->nullable(false)->change();

            // Drop columns added in up() if they were newly added
            if (Schema::hasColumn('contracts', 'contract_type')) {
                $table->dropColumn('contract_type');
            }
            if (Schema::hasColumn('contracts', 'husband_birth_date')) {
                $table->dropColumn('husband_birth_date');
            }
            if (Schema::hasColumn('contracts', 'husband_passport_number')) {
                $table->dropColumn('husband_passport_number');
            }
            if (Schema::hasColumn('contracts', 'wife_birth_date')) {
                $table->dropColumn('wife_birth_date');
            }
            if (Schema::hasColumn('contracts', 'wife_passport_number')) {
                $table->dropColumn('wife_passport_number');
            }
            if (Schema::hasColumn('contracts', 'present_dowry')) {
                $table->dropColumn('present_dowry');
            }
            if (Schema::hasColumn('contracts', 'deferred_dowry')) {
                $table->dropColumn('deferred_dowry');
            }
            if (Schema::hasColumn('contracts', 'husband_conditions_arabic')) {
                $table->dropColumn('husband_conditions_arabic');
            }
            if (Schema::hasColumn('contracts', 'husband_conditions_persian')) {
                $table->dropColumn('husband_conditions_persian');
            }
            if (Schema::hasColumn('contracts', 'wife_conditions_arabic')) {
                $table->dropColumn('wife_conditions_arabic');
            }
            if (Schema::hasColumn('contracts', 'wife_conditions_persian')) {
                $table->dropColumn('wife_conditions_persian');
            }
            if (Schema::hasColumn('contracts', 'first_witness')) {
                $table->dropColumn('first_witness');
            }
            if (Schema::hasColumn('contracts', 'second_witness')) {
                $table->dropColumn('second_witness');
            }
        });
    }
};