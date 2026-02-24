<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. إضافة حساب مدير النظام
        User::updateOrCreate(
            ['email' => 'admin@droguerie.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('2001'), 
            ]
        );

        User::updateOrCreate(
            ['email' => 'cashier@droguerie.com'],
            [
                'name' => 'Caissier',
                'password' => Hash::make('1234'), 
            ]
        );

        User::updateOrCreate(
            ['email' => 'commercial@droguerie.com'],
            [
                'name' => 'Commercial',
                'password' => Hash::make('5678'), 
            ]
        );

        // 2. إضافة الأقسام (Categories)
        $category1 = Category::firstOrCreate(['name' => 'صباغة وطلاء', 'description' => 'جميع أنواع الصباغة المائية والزيتية']);
        $category2 = Category::firstOrCreate(['name' => 'مسامير وبراغي', 'description' => 'أدوات التثبيت بمختلف المقاسات']);
        $category3 = Category::firstOrCreate(['name' => 'أدوات يدوية', 'description' => 'مطارق، مفكات، ومعدات بناء']);

        // 3. إضافة منتجات للتجربة (Products)
        Product::firstOrCreate(
            ['barcode' => '123456789'], // باركود وهمي لنجرب به جهاز السكانر لاحقاً
            [
                'category_id' => $category1->id,
                'name' => 'صباغة بيضاء 5 لتر',
                'description' => 'صباغة مائية ممتازة',
                'purchase_price' => 100.00,
                'sale_price' => 130.00,
                'stock_quantity' => 50,
                'unit' => 'سطل',
            ]
        );

        Product::firstOrCreate(
            ['barcode' => '987654321'],
            [
                'category_id' => $category2->id,
                'name' => 'مسامير صلب 5 سم',
                'description' => 'علبة مسامير صلبة',
                'purchase_price' => 15.00,
                'sale_price' => 25.00,
                'stock_quantity' => 100,
                'unit' => 'علبة',
            ]
        );

        // 4. إضافة زبون افتراضي (للبيع السريع) ومورد
        Customer::firstOrCreate(['phone' => '0000000000'], ['name' => 'زبون عام (Cash)', 'loyalty_points' => 0]);
        Supplier::firstOrCreate(['name' => 'شركة أسترا للصباغة'], ['contact_info' => '0500000000', 'notes' => 'مورد الصباغة الرئيسي']);
    }
}