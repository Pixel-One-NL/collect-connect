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
        Schema::create('minifigs', function (Blueprint $table) {
            $table->id();

            $table->string('rebrickable_id', 20)->unique()->index();
            $table->string('bricklink_id', 20)->nullable()->unique();
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minifigs');
    }
};
