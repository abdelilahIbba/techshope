<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Permission;
use App\Models\Role;
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
        $permissionCatalog = [
            ['key' => 'dashboard.view', 'label' => 'Voir Dashboard', 'page' => 'Dashboard', 'action' => 'View'],
            ['key' => 'pos.view', 'label' => 'Voir POS', 'page' => 'POS', 'action' => 'View'],
            ['key' => 'stock.view', 'label' => 'Voir stock', 'page' => 'Stock', 'action' => 'View'],
            ['key' => 'stock.create', 'label' => 'Créer produit', 'page' => 'Stock', 'action' => 'Create'],
            ['key' => 'stock.edit', 'label' => 'Modifier produit', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'stock.delete', 'label' => 'Supprimer produit', 'page' => 'Stock', 'action' => 'Delete'],
            ['key' => 'stock.adjust', 'label' => 'Ajuster stock', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'supplier.manage', 'label' => 'Gérer fournisseurs', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'category.manage', 'label' => 'Gérer catégories', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'notifications.view', 'label' => 'Voir notifications', 'page' => 'Notifications', 'action' => 'View'],
            ['key' => 'settings.view', 'label' => 'Voir settings', 'page' => 'Settings', 'action' => 'View'],
            ['key' => 'settings.update', 'label' => 'Modifier settings', 'page' => 'Settings', 'action' => 'Edit'],
            ['key' => 'users.manage', 'label' => 'Gérer utilisateurs', 'page' => 'Settings', 'action' => 'Edit'],
            ['key' => 'roles.manage', 'label' => 'Gérer rôles', 'page' => 'Settings', 'action' => 'Edit'],
            ['key' => 'checkout.create', 'label' => 'Valider vente', 'page' => 'POS', 'action' => 'Create'],
            ['key' => 'receipt.export', 'label' => 'Exporter ticket', 'page' => 'POS', 'action' => 'Export'],
            ['key' => 'barcode.print', 'label' => 'Imprimer code-barres', 'page' => 'Stock', 'action' => 'Export'],
            ['key' => 'reports.view', 'label' => 'Voir rapports', 'page' => 'Reports', 'action' => 'View'],
            ['key' => 'reports.export', 'label' => 'Exporter rapports', 'page' => 'Reports', 'action' => 'Export'],
        ];

        foreach ($permissionCatalog as $permission) {
            Permission::query()->updateOrCreate(['key' => $permission['key']], $permission);
        }

        $adminRole = Role::query()->firstOrCreate(['name' => 'Admin'], ['description' => 'Accès complet']);
        $superUserRole = Role::query()->firstOrCreate(['name' => 'SuperUser'], ['description' => 'Gestion RBAC — ne peut pas modifier le rôle Admin ni générer des licences']);
        $editorRole = Role::query()->firstOrCreate(['name' => 'Editor'], ['description' => 'Gestion opérationnelle']);
        $viewerRole = Role::query()->firstOrCreate(['name' => 'Viewer'], ['description' => 'Lecture seule']);

        $adminRole->permissions()->sync(Permission::query()->pluck('id'));
        $superUserRole->permissions()->sync(Permission::query()->pluck('id'));
        $editorRole->permissions()->sync(Permission::query()->whereIn('key', [
            'dashboard.view', 'pos.view', 'stock.view', 'stock.create', 'stock.edit', 'stock.adjust',
            'supplier.manage', 'category.manage', 'notifications.view', 'checkout.create', 'receipt.export',
            'barcode.print',
        ])->pluck('id'));
        $viewerRole->permissions()->sync(Permission::query()->whereIn('key', [
            'dashboard.view', 'stock.view', 'notifications.view',
        ])->pluck('id'));

        // 1. إضافة حساب مدير النظام
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@local.pos'],
            [
                'name' => 'Abdelilah',
                'role' => 'Admin',
                'password' => Hash::make('18082001'), 
            ]
        );

        $cashierUser = User::updateOrCreate(
            ['email' => 'cashier@local.pos'],
            [
                'name' => 'Caissier',
                'role' => 'Editor',
                'password' => Hash::make('1234'), 
            ]
        );

        $commercialUser = User::updateOrCreate(
            ['email' => 'commercial@local.pos'],
            [
                'name' => 'Commercial',
                'role' => 'Editor',
                'password' => Hash::make('5678'), 
            ]
        );

        $superUserUser = User::updateOrCreate(
            ['email' => 'superuser@local.pos'],
            [
                'name' => 'superUser',
                'role' => 'SuperUser',
                'password' => Hash::make('0099'),
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