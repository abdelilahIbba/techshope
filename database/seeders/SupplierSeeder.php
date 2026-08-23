<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Moroccan suppliers for textile and fashion.
     */
    public const SUPPLIERS = [
        [
            'name' => 'Textile & Mode Casablanca',
            'contact_info' => '+212 522 30 40 50',
            'notes' => 'Fournisseur principal de pyjamas et homewear (Casablanca)',
        ],
        [
            'name' => 'Lingerie & Pyjama Import Fès',
            'contact_info' => '+212 535 60 70 80',
            'notes' => 'Importateur de lingerie fine et satin de qualité (Fès)',
        ],
        [
            'name' => 'Confection Atlas Marrakech',
            'contact_info' => '+212 524 40 50 60',
            'notes' => 'Atelier de confection textile coton et velours (Marrakech)',
        ],
        [
            'name' => 'Distribution Élégance Tanger',
            'contact_info' => '+212 539 90 10 20',
            'notes' => 'Distributeur de marques internationales de lingerie (Tanger)',
        ],
    ];

    /**
     * Seed the suppliers.
     */
    public function run(): void
    {
        foreach (self::SUPPLIERS as $supplier) {
            Supplier::query()->updateOrCreate(
                ['name' => $supplier['name']],
                ['contact_info' => $supplier['contact_info'], 'notes' => $supplier['notes']]
            );
        }
    }
}
