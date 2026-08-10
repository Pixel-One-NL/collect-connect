<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('company')->nullable()->after('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_company')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('company');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_company');
        });
    }
};
