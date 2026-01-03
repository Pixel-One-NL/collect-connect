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
        Schema::create('inventory_minifigs', function (Blueprint $table) {
            $table->string('rebrickable_id', 20)->unique();

            $table->foreignId('inventory_id')->references('id')->on('inventories');
            $table->foreignId('part_id')->references('id')->on('parts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_minifigs');
    }
};
