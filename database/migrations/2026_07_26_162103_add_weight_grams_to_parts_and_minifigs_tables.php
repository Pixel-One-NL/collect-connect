<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->decimal('weight_grams', 10, 4)->nullable()->after('name');
        });

        Schema::table('minifigs', function (Blueprint $table) {
            $table->decimal('weight_grams', 10, 4)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('weight_grams');
        });

        Schema::table('minifigs', function (Blueprint $table) {
            $table->dropColumn('weight_grams');
        });
    }
};
