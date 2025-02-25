<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('status');
            $table->json('shipping_address');
            $table->string('shipping_option');
            $table->decimal('shipping_fee', 10, 2);
            $table->string('payment_method');
            $table->decimal('payment_fee', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->string('voucher_code')->nullable();
            $table->decimal('voucher_discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
