<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('cloudflare_image_id')->nullable()->unique();
            $table->timestamp('cloudflare_image_failed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropUnique('articles_cloudflare_image_id_unique');
            $table->dropColumn(['cloudflare_image_id', 'cloudflare_image_failed_at']);
        });
    }
};
