<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bricqer_id')->nullable()->unique();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('area')->nullable();
            $table->unsignedInteger('price_cents')->default(0);
            $table->boolean('track_trace')->default(false);
            $table->json('countries')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
