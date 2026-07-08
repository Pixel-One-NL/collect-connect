<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A product belongs to exactly one productable entity, so the polymorphic
     * pair must be unique. The unique index also lets the inventory import use
     * an efficient upsert keyed on these columns.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['productable_type', 'productable_id']);
            $table->unique(['productable_type', 'productable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['productable_type', 'productable_id']);
            $table->index(['productable_type', 'productable_id']);
        });
    }
};
