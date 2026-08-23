<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * The 4 shopping categories for the tech shop concept.
     */
    public const CATEGORIES = [
        [
            'name' => 'Smartphones & Téléphonie',
            'description' => 'Téléphones intelligents et accessoires mobiles',
        ],
        [
            'name' => 'Ordinateurs & Informatique',
            'description' => 'Laptops, ordinateurs et accessoires informatiques',
        ],
        [
            'name' => 'Audio & Casques',
            'description' => 'Écouteurs, casques et enceintes',
        ],
        [
            'name' => 'Gaming & Consoles',
            'description' => 'Consoles, périphériques et accessoires gaming',
        ],
    ];

    /**
     * Seed the shopping categories.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
