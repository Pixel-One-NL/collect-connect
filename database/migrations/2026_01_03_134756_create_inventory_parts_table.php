<?php

declare(strict_types=1);

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
        Schema::create('inventory_parts', function (Blueprint $table) {
            $table->string('rebrickable_id', 20)->unique();

            $table->foreignId('inventory_id')->references('id')->on('inventories');
            $table->foreignId('part_id')->references('id')->on('parts');

            $table->foreignId('color_id')->references('id')->on('colors');

            $table->integer('quantity');
            $table->boolean('is_spare')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_parts');
    }
};
