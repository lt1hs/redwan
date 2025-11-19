<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('unfinished_passports', function (Blueprint $table) {
            $table->string('residence_authority')->nullable()->after('transaction_type');
        });
    }

    public function down()
    {
        Schema::table('unfinished_passports', function (Blueprint $table) {
            $table->dropColumn('residence_authority');
        });
    }
};
