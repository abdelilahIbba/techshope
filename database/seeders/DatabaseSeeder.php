<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users, Roles and RBAC Permissions
        $this->call(UserSeeder::class);

        // 2. Women's Clothing & Pajamas catalog: categories, suppliers, brands and products
        $this->call([
            CategorySeeder::class,
            SupplierSeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
        ]);

        // 3. Default Walk-in Customer
        Customer::firstOrCreate(
            ['phone' => '0000000000'],
            ['name' => 'Client Comptoir (Passager)', 'loyalty_points' => 0]
        );
    }
}