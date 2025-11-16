<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('passports', function (Blueprint $table) {
        //     $table->string('unique_code')->unique()->nullable()->after('passport_number');
        // });

        // Generate unique codes for existing passports
        // DB::table('passports')->orderBy('id')->each(function ($passport) {
        //     DB::table('passports')
        //         ->where('id', $passport->id)
        //         ->update([
        //             'unique_code' => 'PC' . 
        //                 now()->format('ymdHis') . 
        //         str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT) . 
        //         substr($passport->passport_number, -4)
        //     ]);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passports', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });
    }
};
