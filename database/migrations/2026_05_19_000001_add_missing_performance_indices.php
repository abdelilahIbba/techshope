<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing database indices to resolve slow queries and 502 timeouts.
 *
 * PostgreSQL does NOT auto-create indices for FK columns, unlike MySQL.
 * All FK columns used in JOINs or WHERE clauses need explicit indices.
 */
return new class extends Migration
{
    public function up(): void
    {
        // sale_items: heavily joined in reports and dashboard queries
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index('product_id');
            $table->index(['product_id', 'sale_id']); // covers JOIN + GROUP BY in salesByProduct
        });

        // purchase_items: joined in purchase history queries
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index('purchase_id');
            $table->index('product_id');
        });

        // products: sorted/filtered on every stock page load
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('supplier_id');
            $table->index('name');                         // ORDER BY name on stock/pos pages
            $table->index('stock_quantity');               // WHERE stock_quantity <= threshold
        });

        // audit_logs: queried with latest() (ORDER BY created_at DESC)
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('actor_user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['product_id', 'sale_id']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropIndex(['purchase_id']);
            $table->dropIndex(['product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['stock_quantity']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['actor_user_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
