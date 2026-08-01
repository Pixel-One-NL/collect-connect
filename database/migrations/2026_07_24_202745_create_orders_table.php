<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number')->unique();
            $table->string('status')->default('pending_payment');
            $table->string('email');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('shipping_line1');
            $table->string('shipping_line2')->nullable();
            $table->string('shipping_postal_code');
            $table->string('shipping_city');
            $table->string('shipping_country_code', 2)->default('NL');
            $table->unsignedInteger('subtotal_cents');
            $table->unsignedInteger('shipping_cents')->default(0);
            $table->unsignedInteger('total_cents');
            $table->string('shipping_method_name')->nullable();
            $table->unsignedBigInteger('shipping_method_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_provider')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('bricqer_order_id')->nullable();
            $table->string('bricqer_status')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
