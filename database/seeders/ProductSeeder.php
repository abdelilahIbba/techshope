<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\VariantStockService;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Women's Clothing & Pajamas Catalog
     */
    public const CATALOG = [
        'بيجامات شتوية' => [
            'supplier' => 'Textile & Mode Casablanca',
            'products' => [
                [
                    'barcode' => 'PJ1001',
                    'brand' => 'Oysho',
                    'name' => 'بيجامة قطيفة شتوية 3 قطع',
                    'description' => 'بيجامة نسائية فاخرة من المخمل الناعم (جاكيت + توب + بنطلون) دافئة ومريحة',
                    'purchase_price' => 160.00,
                    'sale_price' => 280.00,
                    'stock_quantity' => 30,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Rose Poudré', 'Gris Perle', 'Bordeaux'],
                    'image' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ1002',
                    'brand' => 'Etam',
                    'name' => 'بيجامة صوف دافئة مع قبوط',
                    'description' => 'بيجامة شتوية بقماش البولار الصوفي فائق النعومة مع غطاء رأس مطرز',
                    'purchase_price' => 190.00,
                    'sale_price' => 320.00,
                    'stock_quantity' => 24,
                    'sizes' => ['M', 'L', 'XL'],
                    'colors' => ['Blanc Cassé', 'Camel', 'Bleu Nuit'],
                    'image' => 'https://images.unsplash.com/photo-1584273143981-41c073dfe8f8?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ1003',
                    'brand' => "Women'Secret",
                    'name' => 'بيجامة قطيفة مضلعة مريحة',
                    'description' => 'بيجامة مضلعة شتوية عصرية وأنيقة للمنزل مع حزام خصر مرن',
                    'purchase_price' => 140.00,
                    'sale_price' => 240.00,
                    'stock_quantity' => 36,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Vert Sauge', 'Mauve', 'Noir'],
                    'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ1004',
                    'brand' => 'La Vie en Rose',
                    'name' => 'طقم بيجامة تريكو شتوي',
                    'description' => 'طقم تريكو ناعم ودافئ مثالي للأيام الباردة والاسترخاء المنزلي',
                    'purchase_price' => 175.00,
                    'sale_price' => 299.00,
                    'stock_quantity' => 20,
                    'sizes' => ['S', 'M', 'L'],
                    'colors' => ['Beige', 'Terracotta'],
                    'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ1005',
                    'brand' => "Victoria's Secret",
                    'name' => 'طقم بيجامة شتوية مخملية ملكية',
                    'description' => 'طقم مخمل ملكي فاخر بأزرار أمامية ولمسات ستان براقة',
                    'purchase_price' => 220.00,
                    'sale_price' => 380.00,
                    'stock_quantity' => 18,
                    'sizes' => ['M', 'L', 'XL'],
                    'colors' => ['Bleu Marine', 'Bordeaux'],
                    'image' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
        'بيجامات صيفية' => [
            'supplier' => 'Lingerie & Pyjama Import Fès',
            'products' => [
                [
                    'barcode' => 'PJ2001',
                    'brand' => "Victoria's Secret",
                    'name' => 'بيجامة ساتان حريري أنيقة',
                    'description' => 'بيجامة ساتان كلاسيكية ناعمة بأكمام قصيرة وشورت خفيف ومريح',
                    'purchase_price' => 150.00,
                    'sale_price' => 260.00,
                    'stock_quantity' => 40,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Rose Satin', 'Noir Ébène', 'Émeraude', 'Champagne'],
                    'image' => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ2002',
                    'brand' => 'Oysho',
                    'name' => 'بيجامة قطن 100% شورت وتيشيرت',
                    'description' => 'بيجامة صيفية مصنوعة من القطن الطبيعي 100% لتنفس مثالي ومقاومة للحرارة',
                    'purchase_price' => 90.00,
                    'sale_price' => 160.00,
                    'stock_quantity' => 48,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Blanc Imprimé', 'Bleu Ciel', 'Lilas'],
                    'image' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ2003',
                    'brand' => 'Etam',
                    'name' => 'بيجامة صيفية بنمط زهور منعش',
                    'description' => 'بيجامة فيسكوز صيفية ناعمة ومنسدلة بنقشات أزهار ربيعية جذابة',
                    'purchase_price' => 110.00,
                    'sale_price' => 195.00,
                    'stock_quantity' => 30,
                    'sizes' => ['M', 'L', 'XL'],
                    'colors' => ['Floral Rose', 'Floral Jaune'],
                    'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ2004',
                    'brand' => 'Intimissimi',
                    'name' => 'قميص نوم ساتان ناعم مع دانتيل',
                    'description' => 'قميص نوم (Nuisette) من الساتان الفاخر بحواف دانتيل فرنسي راقٍ',
                    'purchase_price' => 120.00,
                    'sale_price' => 220.00,
                    'stock_quantity' => 24,
                    'sizes' => ['S', 'M', 'L'],
                    'colors' => ['Rouge Passion', 'Noir', 'Bleu Nuit'],
                    'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'PJ2005',
                    'brand' => "Women'Secret",
                    'name' => 'طقم بيجامة قطن سترابلس خفيف',
                    'description' => 'طقم توب بحمالات رفيعة وشورت مريح جداً لنوم هادئ في ليالي الصيف',
                    'purchase_price' => 85.00,
                    'sale_price' => 150.00,
                    'stock_quantity' => 35,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Corail', 'Blanc', 'Gris'],
                    'image' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
        'ملابس منزلية' => [
            'supplier' => 'Confection Atlas Marrakech',
            'products' => [
                [
                    'barcode' => 'HW3001',
                    'brand' => 'La Vie en Rose',
                    'name' => 'روب دو شومبر قطيفة فاخر',
                    'description' => 'روب حمام ومنزل طويل من المخمل السميك والناعم بجيوب وحزام خصر',
                    'purchase_price' => 220.00,
                    'sale_price' => 380.00,
                    'stock_quantity' => 16,
                    'sizes' => ['M', 'L', 'XL'],
                    'colors' => ['Bleu Royal', 'Bordeaux', 'Gris Anthracite'],
                    'image' => 'https://images.unsplash.com/photo-1618932260643-eee4a2f652a6?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'HW3002',
                    'brand' => "Victoria's Secret",
                    'name' => 'كيمونو ساتان ياباني مطرز',
                    'description' => 'كيمونو طويل من الساتان البراق مطرز بتفاصيل زهرية راقية على الأكمام والظهر',
                    'purchase_price' => 180.00,
                    'sale_price' => 310.00,
                    'stock_quantity' => 20,
                    'sizes' => ['S/M', 'L/XL'],
                    'colors' => ['Blanc Soie', 'Noir Floral', 'Rose Gold'],
                    'image' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'HW3003',
                    'brand' => "Women'Secret",
                    'name' => 'كسوة منزلية قطنية كاجوال',
                    'description' => 'فستان منزلي قطني خفيف وعملي ومريح طوال اليوم بقصة مريحة',
                    'purchase_price' => 100.00,
                    'sale_price' => 175.00,
                    'stock_quantity' => 40,
                    'sizes' => ['M', 'L', 'XL', 'XXL'],
                    'colors' => ['Gris Chiné', 'Kaki', 'Marine'],
                    'image' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'HW3004',
                    'brand' => 'Oysho',
                    'name' => 'طقم منزلي هودي وبنطلون صوف',
                    'description' => 'طقم مريح قطعتين هودي وبنطلون مناسب للبيت والطلعات الخفيفة',
                    'purchase_price' => 195.00,
                    'sale_price' => 340.00,
                    'stock_quantity' => 24,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Nude', 'Gris', 'Marron Glacé'],
                    'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'HW3005',
                    'brand' => 'Etam',
                    'name' => 'كارديجان منزلي طويل ودافئ',
                    'description' => 'جاكيت منزلي مفتوح طويل من الصوف الناعم يوضع فوق البيجامات',
                    'purchase_price' => 130.00,
                    'sale_price' => 230.00,
                    'stock_quantity' => 22,
                    'sizes' => ['M', 'L', 'XL'],
                    'colors' => ['Beige Clair', 'Gris Perle'],
                    'image' => 'https://images.unsplash.com/photo-1508427953056-b00b8d78ebf5?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
        'ملابس داخلية' => [
            'supplier' => 'Distribution Élégance Tanger',
            'products' => [
                [
                    'barcode' => 'LG4001',
                    'brand' => 'Etam',
                    'name' => 'طقم لانجري دانتيل فرنسي 2 قطع',
                    'description' => 'طقم دانتيل فرنسي راقٍ وأنيق بحمالات قابلة للتعديل وتصميم جذاب',
                    'purchase_price' => 95.00,
                    'sale_price' => 180.00,
                    'stock_quantity' => 36,
                    'sizes' => ['85B', '90B', '95B', '90C'],
                    'colors' => ['Noir', 'Blanc', 'Rouge Bordeaux'],
                    'image' => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'LG4002',
                    'brand' => 'Intimissimi',
                    'name' => 'سوتين بدون حشوة كومفورت ناعم',
                    'description' => 'حمالة صدر بدون أسلاك مريحة جداً للاستخدام اليومي بقماش ناعم وخفي تحت الملابس',
                    'purchase_price' => 75.00,
                    'sale_price' => 140.00,
                    'stock_quantity' => 45,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Nude/Chair', 'Noir', 'Blanc'],
                    'image' => 'https://images.unsplash.com/photo-1508427953056-b00b8d78ebf5?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'LG4003',
                    'brand' => 'Calvin Klein Underwear',
                    'name' => 'باك 3 كلوت قطن مريح أورجينال',
                    'description' => 'علبة تحتوي على 3 قطع قطن مريح بحزام مطاطي مميز بجودة عالية',
                    'purchase_price' => 60.00,
                    'sale_price' => 110.00,
                    'stock_quantity' => 60,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Trio Noir/Blanc/Gris', 'Pastel'],
                    'image' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'LG4004',
                    'brand' => 'Yamamay',
                    'name' => 'بودي سوت دانتيل وساتان سهرة',
                    'description' => 'بودي سوت فاخر من الدانتيل والساتان بظهر مفتوح وقصة أنثوية جذابة',
                    'purchase_price' => 130.00,
                    'sale_price' => 230.00,
                    'stock_quantity' => 24,
                    'sizes' => ['S', 'M', 'L'],
                    'colors' => ['Noir Intense', 'Bordeaux'],
                    'image' => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode' => 'LG4005',
                    'brand' => 'Calvin Klein Underwear',
                    'name' => 'حمالة صدر رياضية داعمة',
                    'description' => 'برا رياضي قطني مرن يوفر دعماً مثالياً وحرية كاملة للحركة',
                    'purchase_price' => 80.00,
                    'sale_price' => 150.00,
                    'stock_quantity' => 40,
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'colors' => ['Noir', 'Gris', 'Rose'],
                    'image' => 'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
    ];

    /**
     * Seed the products for each women's fashion category.
     */
    public function run(): void
    {
        $variantStock = app(VariantStockService::class);

        foreach (self::CATALOG as $categoryName => $data) {
            $category = Category::query()->where('name', $categoryName)->first();
            $supplier = Supplier::query()->where('name', $data['supplier'])->first();

            foreach ($data['products'] as $product) {
                $brand = Brand::query()->where('name', $product['brand'])->first();

                $productModel = Product::query()->updateOrCreate(
                    ['barcode' => $product['barcode']],
                    [
                        'category_id' => $category?->id,
                        'supplier_id' => $supplier?->id,
                        'brand_id' => $brand?->id,
                        'name' => $product['name'],
                        'description' => $product['description'],
                        'barcode_type' => 'internal',
                        'image' => $product['image'] ?? null,
                        'purchase_price' => $product['purchase_price'],
                        'sale_price' => $product['sale_price'],
                        'stock_quantity' => $product['stock_quantity'],
                        'unit' => 'pièce',
                    ]
                );

                // Sync sizes and colors as variants
                $sizes = $product['sizes'] ?? [];
                $colors = $product['colors'] ?? [];

                $variantCount = max(1, count($sizes)) * max(1, count($colors));
                $stockPerVariant = round($product['stock_quantity'] / $variantCount, 1);

                if (! empty($sizes) || ! empty($colors)) {
                    $variantStock->syncVariants(
                        $productModel,
                        $sizes,
                        $colors,
                        $stockPerVariant,
                        $product['barcode']
                    );
                } else {
                    $variantStock->ensureDefaultVariant($productModel, (float) $product['stock_quantity']);
                }
            }
        }
    }
}
