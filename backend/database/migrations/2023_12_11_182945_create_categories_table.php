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
        // I took a look at spatie/laravel-tags package migrations for structuring this
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->longText('description')->nullable();
            $table->unsignedInteger('order_column')->nullable()->index();

            $table->timestamps();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->morphs('categorizable');

            $table->unique(['category_id', 'categorizable_id', 'categorizable_type'], 'three_uniques');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorizables');
        Schema::dropIfExists('categories');
    }
};
