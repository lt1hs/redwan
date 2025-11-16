<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unfinished_passports', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('residence_expiry_date')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('transaction_type')->nullable();
            $table->text('address')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('personal_photo')->nullable();
            $table->string('passport_photo')->nullable();
            $table->text('notes')->nullable();
            $table->enum('completion_status', ['مسودة', 'قيد المراجعة', 'جاهز للنقل'])->default('مسودة');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unfinished_passports');
    }
};
