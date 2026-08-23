<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categories for Women's Clothing & Pajamas Store.
     */
    public const CATEGORIES = [
        [
            'name' => 'بيجامات شتوية',
            'description' => 'بيجامات نسائية شتوية قطيفة وصوف دافئة ومريحة',
        ],
        [
            'name' => 'بيجامات صيفية',
            'description' => 'بيجامات صيفية قطنية وساتان خفيفة وعصرية',
        ],
        [
            'name' => 'ملابس منزلية',
            'description' => 'أرواب، كيمونو، فساتين منزلية وجلابيات مريحة',
        ],
        [
            'name' => 'ملابس داخلية',
            'description' => 'ملابس داخلية نسائية راقية، أطقم دانتيل وساتان',
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
