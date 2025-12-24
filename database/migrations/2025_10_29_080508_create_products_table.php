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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->json('name');
            $table->json('description');
            $table->json('short_description');
            $table->string('sku')->unique()->nullable();//only for simple products if variableproducts sku saved in product_variants table
            // $table->enum('type', ['simple', 'variable'])->default('simple');
            $table->string('thumbnail')->nullable();
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('meta_keywords')->nullable();
            $table->json('meta_description')->nullable();
            $table->index(['is_active', 'category_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
