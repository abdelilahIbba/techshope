<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure all system permissions exist
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
            ['key' => 'brand.manage', 'label' => 'Gérer marques', 'page' => 'Stock', 'action' => 'Edit'],
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

        $allPermissionIds = Permission::query()->pluck('id');

        // 2. Roles Creation
        $superAdminRole = Role::query()->firstOrCreate(
            ['name' => 'Super Admin'],
            ['description' => 'Accès absolu et contrôle total du système']
        );
        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrateur avec accès complet']
        );
        $caissierRole = Role::query()->firstOrCreate(
            ['name' => 'Caissier'],
            ['description' => 'Gestion de la caisse, ventes et encaissement']
        );
        $employeRole = Role::query()->firstOrCreate(
            ['name' => 'Employé'],
            ['description' => 'Gestion des stocks, produits et inventaire']
        );

        // Also ensure legacy roles exist if needed
        Role::query()->firstOrCreate(['name' => 'SuperUser'], ['description' => 'Gestion RBAC']);
        Role::query()->firstOrCreate(['name' => 'Editor'], ['description' => 'Gestion opérationnelle']);
        Role::query()->firstOrCreate(['name' => 'Viewer'], ['description' => 'Lecture seule']);

        // Sync permissions
        $superAdminRole->permissions()->sync($allPermissionIds);
        $adminRole->permissions()->sync($allPermissionIds);

        $caissierRole->permissions()->sync(
            Permission::query()->whereIn('key', [
                'pos.view',
                'checkout.create',
                'receipt.export',
                'dashboard.view',
                'notifications.view',
            ])->pluck('id')
        );

        $employeRole->permissions()->sync(
            Permission::query()->whereIn('key', [
                'dashboard.view',
                'pos.view',
                'stock.view',
                'stock.create',
                'stock.edit',
                'stock.adjust',
                'supplier.manage',
                'category.manage',
                'brand.manage',
                'barcode.print',
                'notifications.view',
            ])->pluck('id')
        );

        // 3. Create Default Users for the 4 Roles
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@techshope.ma',
                'role' => 'Super Admin',
                'password' => Hash::make('18082001'),
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@techshope.ma',
                'role' => 'Admin',
                'password' => Hash::make('18082001'),
            ],
            [
                'name' => 'Caissier',
                'email' => 'caissier@techshope.ma',
                'role' => 'Caissier',
                'password' => Hash::make('1234'),
            ],
            [
                'name' => 'Employé',
                'email' => 'employe@techshope.ma',
                'role' => 'Employé',
                'password' => Hash::make('5678'),
            ],
            // Compatibility accounts
            [
                'name' => 'Abdelilah (Admin)',
                'email' => 'admin@local.pos',
                'role' => 'Admin',
                'password' => Hash::make('18082001'),
            ],
            [
                'name' => 'Caissier POS',
                'email' => 'cashier@local.pos',
                'role' => 'Caissier',
                'password' => Hash::make('1234'),
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => $user['password'],
                ]
            );
        }
    }
}
