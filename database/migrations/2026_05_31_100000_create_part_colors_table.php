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
        Schema::create('part_colors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('color_id')->constrained()->cascadeOnDelete();

            $table->string('bricqer_definition_id');

            $table->unique(['part_id', 'color_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_colors');
    }
};
