<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * For databases that already ran the original wishlist_items migration
     * without wishlist_id.
     */
    public function up(): void
    {
        if (! Schema::hasTable('wishlist_items')) {
            return;
        }

        if (! Schema::hasColumn('wishlist_items', 'wishlist_id')) {
            Schema::table('wishlist_items', function (Blueprint $table) {
                $table->foreignId('wishlist_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        Schema::table('wishlist_items', function (Blueprint $table) {
            if (! Schema::hasIndex('wishlist_items', 'wishlist_items_wishlist_id_product_id_unique')) {
                $table->unique(['wishlist_id', 'product_id']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('wishlist_items') || ! Schema::hasColumn('wishlist_items', 'wishlist_id')) {
            return;
        }

        Schema::table('wishlist_items', function (Blueprint $table) {
            if (Schema::hasIndex('wishlist_items', 'wishlist_items_wishlist_id_product_id_unique')) {
                $table->dropUnique(['wishlist_id', 'product_id']);
            }

            $table->dropConstrainedForeignId('wishlist_id');
        });
    }
};
