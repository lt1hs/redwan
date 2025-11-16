<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('passports', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('nationality');
            $table->string('passport_number')->unique();
            $table->string('unique_code')->unique()->nullable();
            $table->date('date_of_birth');
            $table->date('residence_expiry_date');
            $table->string('phone_number')->nullable();
            $table->string('mobile_number');
            $table->enum('passport_status', ['قيد الانجاز', 'جاهز للاستلام', 'تم تسليمه']);
            $table->date('passport_delivery_date');
            $table->string('transaction_type');
            $table->enum('payment_status', ['تم', 'لم يتم', 'يدفع لاحقا']);
            $table->string('delivered_by');
            $table->text('address');
            $table->string('zipcode')->nullable();
            $table->string('personal_photo')->nullable();
            $table->string('passport_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('passports');
    }
};