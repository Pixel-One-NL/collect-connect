<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products become unique per part *and* color. Existing rows predate color
     * splitting and are fully derived from the Bricqer import, so they are
     * cleared and re-imported rather than migrated in place.
     */
    public function up(): void
    {
        DB::table('products')->delete();

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['productable_type', 'productable_id']);

            $table->foreignId('color_id')->after('productable_id')->constrained()->cascadeOnDelete();

            $table->unique(['productable_type', 'productable_id', 'color_id']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['productable_type', 'productable_id', 'color_id']);
            $table->dropConstrainedForeignId('color_id');
            $table->unique(['productable_type', 'productable_id']);
        });
    }
};
