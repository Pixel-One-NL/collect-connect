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
        foreach (['sets', 'part_colors', 'minifigs'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->string('cloudflare_image_id')->nullable()->unique();
                $table->timestamp('cloudflare_image_failed_at')->nullable();
            });
        }

        foreach (['part_colors', 'minifigs'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn('bricqer_image_url');
            });
        }

        Schema::dropIfExists('media');
    }

    /**
     * Reverse the migrations. The media table is not restored: its files and
     * the media library package are gone.
     */
    public function down(): void
    {
        foreach (['part_colors', 'minifigs'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->string('bricqer_image_url')->nullable();
            });
        }

        foreach (['sets', 'part_colors', 'minifigs'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropUnique("{$tableName}_cloudflare_image_id_unique");
                $table->dropColumn(['cloudflare_image_id', 'cloudflare_image_failed_at']);
            });
        }
    }
};
