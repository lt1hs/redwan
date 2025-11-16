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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->text('message');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->string('type')->nullable();
            $table->integer('related_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('error')->nullable();
            $table->text('response_data')->nullable();
            $table->string('message_id')->nullable()->comment('ID from MeliPayamak for status tracking');
            $table->integer('retries')->default(0);
            $table->timestamps();
            
            // Indexes for faster querying
            $table->index('status');
            $table->index('phone');
            $table->index('type');
            $table->index('related_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
}; 