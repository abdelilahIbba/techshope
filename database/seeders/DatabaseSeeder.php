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

        // 2. Addition of Categories (French)
        $catPeinture = Category::firstOrCreate(['name' => 'Peinture & Revêtement'], ['description' => 'Peintures, vernis, enduits et accessoires']);
        $catVisserie = Category::firstOrCreate(['name' => 'Visserie & Boulonnerie'], ['description' => 'Vis, clous, boulons et chevilles']);
        $catOutils = Category::firstOrCreate(['name' => 'Outillage à main'], ['description' => 'Marteaux, tournevis, pinces et clés']);
        $catElec = Category::firstOrCreate(['name' => 'Électricité'], ['description' => 'Câbles, prises, interrupteurs et ampoules']);
        $catPlomberie = Category::firstOrCreate(['name' => 'Plomberie'], ['description' => 'Tuyaux, raccords, robinets et joints']);

        // 3. Addition of Suppliers (Fournisseurs)
        $supPeint = Supplier::firstOrCreate(['name' => 'Sarl Peintures Nationales'], ['contact_info' => '0550 11 22 33', 'notes' => 'Fournisseur principal de peinture']);
        $supQuinc = Supplier::firstOrCreate(['name' => 'Quincaillerie Centrale'], ['contact_info' => '0560 44 55 66', 'notes' => 'Visserie et outillage']);
        $supElec = Supplier::firstOrCreate(['name' => 'Distributeur Électro-Pro'], ['contact_info' => '0540 77 88 99', 'notes' => 'Tout pour l\'électricité']);

        // 4. Addition of Products (Rich Data)
        // Peinture
        Product::firstOrCreate(['barcode' => '7700123'], [
            'category_id' => $catPeinture->id,
            'name' => 'Peinture Blanche Satiné 5L',
            'description' => 'Peinture acrylique de haute qualité',
            'purchase_price' => 2500.00,
            'sale_price' => 3200.00,
            'stock_quantity' => 25,
            'unit' => 'Pot'
        ]);
        Product::firstOrCreate(['barcode' => '7700124'], [
            'category_id' => $catPeinture->id,
            'name' => 'Rouleau Peinture 200mm',
            'description' => 'Rouleau anti-goutte professionnel',
            'purchase_price' => 450.00,
            'sale_price' => 650.00,
            'stock_quantity' => 50,
            'unit' => 'Pièce'
        ]);

        // Visserie
        Product::firstOrCreate(['barcode' => '8800551'], [
            'category_id' => $catVisserie->id,
            'name' => 'Vis à Bois 4x40 (Boite de 100)',
            'description' => 'Vis acier zingué',
            'purchase_price' => 350.00,
            'sale_price' => 550.00,
            'stock_quantity' => 100,
            'unit' => 'Boite'
        ]);
        Product::firstOrCreate(['barcode' => '8800552'], [
            'category_id' => $catVisserie->id,
            'name' => 'Chevilles Nylon 8mm (Sachet 50)',
            'description' => 'Fixation universelle forte',
            'purchase_price' => 180.00,
            'sale_price' => 300.00,
            'stock_quantity' => 80,
            'unit' => 'Sachet'
        ]);

        // Outillage
        Product::firstOrCreate(['barcode' => '9900991'], [
            'category_id' => $catOutils->id,
            'name' => 'Marteau de menuisier 500g',
            'description' => 'Marteau manche ergonomique',
            'purchase_price' => 850.00,
            'sale_price' => 1200.00,
            'stock_quantity' => 15,
            'unit' => 'Pièce'
        ]);
        Product::firstOrCreate(['barcode' => '9900992'], [
            'category_id' => $catOutils->id,
            'name' => 'Jeu de 6 Tournevis',
            'description' => 'Plats et Cruciformes isolés',
            'purchase_price' => 1400.00,
            'sale_price' => 1950.00,
            'stock_quantity' => 10,
            'unit' => 'Set'
        ]);

        // Électricité
        Product::firstOrCreate(['barcode' => '2200221'], [
            'category_id' => $catElec->id,
            'name' => 'Prise de courant double blanche',
            'description' => 'Prise encastrée Legrand',
            'purchase_price' => 420.00,
            'sale_price' => 600.00,
            'stock_quantity' => 40,
            'unit' => 'Pièce'
        ]);

        // Plomberie
        Product::firstOrCreate(['barcode' => '3300331'], [
            'category_id' => $catPlomberie->id,
            'name' => 'Robinet mitigeur Evier',
            'description' => 'Finition chromée, flexible inclus',
            'purchase_price' => 3800.00,
            'sale_price' => 4900.00,
            'stock_quantity' => 8,
            'unit' => 'Pièce'
        ]);

        // 5. Addition of default Customer
        Customer::firstOrCreate(['phone' => '0000000000'], ['name' => 'Client Comptoir (Passager)', 'loyalty_points' => 0]);
    }
}