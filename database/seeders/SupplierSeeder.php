<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Suppliers aligned with the tech shop categories.
     */
    public const SUPPLIERS = [
        [
            'name' => 'TechImport Maroc',
            'contact_info' => '0522 11 22 33',
            'notes' => 'Distributeur de smartphones et téléphonie',
        ],
        [
            'name' => 'Global Computers Distribution',
            'contact_info' => '0522 44 55 66',
            'notes' => 'Distributeur d\'ordinateurs et informatique',
        ],
        [
            'name' => 'AudioPro Wholesale',
            'contact_info' => '0522 77 88 99',
            'notes' => 'Distributeur audio et casques',
        ],
        [
            'name' => 'GameZone Distribution',
            'contact_info' => '0522 10 20 30',
            'notes' => 'Distributeur gaming et consoles',
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
