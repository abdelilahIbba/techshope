<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds "sell by length" support to products:
     * - sell_by_length: boolean flag (true = sold by meter/cm)
     * - length_unit: 'm' or 'cm'
     * - stock_quantity: changed from integer to decimal(10,3) to support e.g. 50.500 m
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('sell_by_length')->default(false)->after('unit');
            $table->string('length_unit', 10)->nullable()->after('sell_by_length'); // 'm' or 'cm'
        });

        // Change stock_quantity from integer to decimal(10,3)
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock_quantity', 10, 3)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sell_by_length', 'length_unit']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
        });
    }
};
