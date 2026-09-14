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
        Schema::create('product_tags', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            // product_variants is created later, so its FK is added in a follow-up migration.
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->unique(['product_id', 'product_variant_id', 'tag_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tags');
    }
};
