<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('name');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('barcode')->unique();
            $table->decimal('stock_quantity', 10, 3)->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'size', 'color']);
            $table->index('stock_quantity');
            $table->index(['color', 'size']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32); // sale, return, exchange_in, exchange_out, adjust, initial
            $table->decimal('quantity_delta', 10, 3);
            $table->decimal('quantity_after', 10, 3);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['product_variant_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('sale_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            $table->foreignId('returned_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('exchanged_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('quantity', 10, 3);
            $table->decimal('price_difference', 10, 2)->default(0);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Backfill: one default variant per existing product (same barcode + stock).
        $products = DB::table('products')->select('id', 'barcode', 'stock_quantity')->get();
        foreach ($products as $product) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $product->id,
                'size' => null,
                'color' => null,
                'barcode' => $product->barcode,
                'stock_quantity' => $product->stock_quantity,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('sale_items')
                ->where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->update(['product_variant_id' => $variantId]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_exchanges');
        Schema::dropIfExists('stock_movements');

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });

        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
