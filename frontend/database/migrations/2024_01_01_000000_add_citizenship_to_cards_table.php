<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('citizenship_fa')->nullable()->after('nationality_en');
            $table->string('citizenship_en')->nullable()->after('citizenship_fa');
        });
    }

    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['citizenship_fa', 'citizenship_en']);
        });
    }
}; 