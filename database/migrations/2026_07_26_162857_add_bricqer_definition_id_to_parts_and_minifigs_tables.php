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
            $table->string('bricqer_definition_id')->nullable()->after('weight_grams')->index();
        });

        Schema::table('minifigs', function (Blueprint $table) {
            $table->string('bricqer_definition_id')->nullable()->after('weight_grams')->index();
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('bricqer_definition_id');
        });

        Schema::table('minifigs', function (Blueprint $table) {
            $table->dropColumn('bricqer_definition_id');
        });
    }
};
