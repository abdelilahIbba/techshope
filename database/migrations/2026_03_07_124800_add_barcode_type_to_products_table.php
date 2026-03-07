<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 'scanned'  = barcode copied from the physical product (via scanner)
            // 'internal' = auto-generated internal barcode (for products without a barcode)
            $table->string('barcode_type')->default('internal')->after('barcode');
        });

        // Mark existing products that have a 13-digit or 12-digit standard barcode as 'scanned'
        // Others stay as 'internal'
        DB::statement("UPDATE products SET barcode_type = 'scanned' WHERE LENGTH(barcode) >= 8 AND barcode NOT LIKE 'INT-%'");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('barcode_type');
        });
    }
};
