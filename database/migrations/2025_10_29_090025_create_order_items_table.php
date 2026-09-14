<?php

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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            // Product variants table is created in a later migration timestamp,
            // so we add this FK in a dedicated follow-up migration.
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->json('name');
            $table->integer('qty');
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
