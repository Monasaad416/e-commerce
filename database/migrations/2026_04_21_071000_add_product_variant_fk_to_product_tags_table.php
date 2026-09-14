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
        if (!Schema::hasTable('product_tags') || !Schema::hasTable('product_variants')) {
            return;
        }

        Schema::table('product_tags', function (Blueprint $table) {
            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('product_tags')) {
            return;
        }

        Schema::table('product_tags', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
        });
    }
};

