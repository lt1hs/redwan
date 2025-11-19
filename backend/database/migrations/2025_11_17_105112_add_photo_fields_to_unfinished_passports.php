<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('unfinished_passports', function (Blueprint $table) {
            $table->string('residence_photo')->nullable()->after('passport_photo');
            $table->string('passport_extension_photo')->nullable()->after('residence_photo');
        });
    }

    public function down()
    {
        Schema::table('unfinished_passports', function (Blueprint $table) {
            $table->dropColumn(['residence_photo', 'passport_extension_photo']);
        });
    }
};
