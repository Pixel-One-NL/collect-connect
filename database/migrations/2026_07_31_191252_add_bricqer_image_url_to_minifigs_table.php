<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('minifigs', function (Blueprint $table) {
            $table->string('bricqer_image_url')->nullable()->after('bricqer_definition_id');
        });
    }

    public function down(): void
    {
        Schema::table('minifigs', function (Blueprint $table) {
            $table->dropColumn('bricqer_image_url');
        });
    }
};
