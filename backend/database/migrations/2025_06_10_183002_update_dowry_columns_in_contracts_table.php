<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('present_dowry', 15, 2)->nullable()->change();
            $table->decimal('deferred_dowry', 15, 2)->nullable()->change();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('present_dowry', 8, 2)->nullable()->change();
            $table->decimal('deferred_dowry', 8, 2)->nullable()->change();
        });

        Schema::enableForeignKeyConstraints();
    }
};
