<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->date('contract_date');
            $table->string('contract_place');
            
            // Husband details
            $table->string('husband_name');
            $table->string('husband_nationality');
            $table->string('husband_id_number');
            $table->string('husband_phone');
            $table->string('husband_address')->nullable();
            
            // Wife details
            $table->string('wife_name');
            $table->string('wife_nationality');
            $table->string('wife_id_number');
            $table->string('wife_phone');
            $table->string('wife_address')->nullable();
            
            // Contract details
            $table->decimal('dowry_amount', 10, 2);
            $table->string('officiant_name');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};