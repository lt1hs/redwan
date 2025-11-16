<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            // Persian/Arabic fields
            $table->string('full_name_fa')->comment('نام و نام خانوادگی');
            $table->string('father_name_fa')->comment('نام پدر');
            // English fields
            $table->string('full_name_en');
            $table->string('father_name_en');
            // Common fields
            $table->string('passport_number');
            $table->string('national_id')->comment('کد فراگیر');
            $table->string('police_code')->comment('کد پلیس');
            $table->date('card_expiry_date')->comment('اعتبار کارت');
            $table->string('personal_photo')->nullable();
            // Relationships
            $table->foreignId('passport_id')->nullable()->constrained('passports')->onDelete('set null');
            $table->string('card_type')->comment('personal, wife, son, daughter');
            $table->foreignId('parent_card_id')->nullable()->constrained('cards')->onDelete('cascade');
            // Additional fields
            $table->string('status')->default('active');
            $table->timestamps();
            
            // Indexes
            $table->index('passport_number');
            $table->index('national_id');
            $table->index('police_code');
            $table->index('card_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
}; 