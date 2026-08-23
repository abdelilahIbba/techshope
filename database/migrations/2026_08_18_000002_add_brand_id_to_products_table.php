<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('brand')->constrained('brands')->nullOnDelete();
        });

        // Backfill: turn the legacy free-text `brand` column into real Brand rows.
        // Older rows could hold a comma-joined list from the previous tag UI
        // (e.g. "Nike, Zara"); only the first name is kept as the product's brand.
        $products = DB::table('products')->select('id', 'brand')->whereNotNull('brand')->where('brand', '!=', '')->get();
        $brandIds = [];

        foreach ($products as $product) {
            $name = trim(explode(',', $product->brand)[0]);
            if ($name === '') {
                continue;
            }

            if (! isset($brandIds[$name])) {
                $brandIds[$name] = DB::table('brands')->insertGetId([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('products')->where('id', $product->id)->update(['brand_id' => $brandIds[$name]]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('name');
        });

        $products = DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->select('products.id', 'brands.name')
            ->get();

        foreach ($products as $product) {
            DB::table('products')->where('id', $product->id)->update(['brand' => $product->name]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });
    }
};
