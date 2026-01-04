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
            $table->foreignId('inventory_id')->references('id')->on('inventories');
            $table->foreignId('minifig_id')->references('id')->on('minifigs');

            $table->integer('quantity');
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
