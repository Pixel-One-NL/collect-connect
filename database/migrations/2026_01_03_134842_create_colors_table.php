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
        Schema::create('colors', function (Blueprint $table) {
            $table->id();

            $table->string('rebrickable_id', 20)->nullable()->unique()->index();
            $table->string('bricqer_color_id')->nullable()->unique();
            $table->string('bricklink_color_id')->nullable()->unique();

            $table->string('name');
            $table->string('hex', 6);
            $table->boolean('is_transparent')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
