<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Seed one Brand row per distinct brand used across the product catalog.
     */
    public function run(): void
    {
        $brandNames = collect(ProductSeeder::CATALOG)
            ->pluck('products')
            ->flatten(1)
            ->pluck('brand')
            ->unique();

        foreach ($brandNames as $name) {
            Brand::query()->firstOrCreate(['name' => $name]);
        }
    }
}
