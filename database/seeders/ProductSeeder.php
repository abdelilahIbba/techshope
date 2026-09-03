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
     * 10 simple single-variant products per category (40 total).
     * Each product has no sizes/colors array so a single default variant
     * matching the product's own barcode and stock_quantity is created.
     * unit must be one of: unité, metre, cm  (validated by the stock forms).
     */
    public const CATALOG = [
        'بيجامات شتوية' => [
            'supplier' => 'Textile & Mode Casablanca',
            'products' => [
                [
                    'barcode'        => 'PJ1001',
                    'brand'          => 'Oysho',
                    'name'           => 'بيجامة قطيفة شتوية 3 قطع',
                    'description'    => 'بيجامة نسائية فاخرة من المخمل الناعم (جاكيت + توب + بنطلون) دافئة ومريحة',
                    'purchase_price' => 160.00,
                    'sale_price'     => 280.00,
                    'stock_quantity' => 30,
                    'image'          => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1002',
                    'brand'          => 'Etam',
                    'name'           => 'بيجامة صوف دافئة مع قبوط',
                    'description'    => 'بيجامة شتوية بقماش البولار الصوفي فائق النعومة مع غطاء رأس مطرز',
                    'purchase_price' => 190.00,
                    'sale_price'     => 320.00,
                    'stock_quantity' => 24,
                    'image'          => 'https://images.unsplash.com/photo-1584273143981-41c073dfe8f8?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1003',
                    'brand'          => "Women'Secret",
                    'name'           => 'بيجامة قطيفة مضلعة مريحة',
                    'description'    => 'بيجامة مضلعة شتوية عصرية وأنيقة للمنزل مع حزام خصر مرن',
                    'purchase_price' => 140.00,
                    'sale_price'     => 240.00,
                    'stock_quantity' => 28,
                    'image'          => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1004',
                    'brand'          => 'Intimissimi',
                    'name'           => 'بيجامة تيفاني فلانيلة ناعمة',
                    'description'    => 'بيجامة من الفلانيلة الناعمة بنقشات هندسية كلاسيكية وأزرار أمامية',
                    'purchase_price' => 155.00,
                    'sale_price'     => 270.00,
                    'stock_quantity' => 20,
                    'image'          => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1005',
                    'brand'          => 'La Vie en Rose',
                    'name'           => 'بيجامة قطن هيرفي دافئة',
                    'description'    => 'بيجامة قطن دافئة بطباعة جميلة وقصة مريحة للنوم الشتوي',
                    'purchase_price' => 120.00,
                    'sale_price'     => 210.00,
                    'stock_quantity' => 35,
                    'image'          => 'https://images.unsplash.com/photo-1584273143981-41c073dfe8f8?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1006',
                    'brand'          => 'Yamamay',
                    'name'           => 'بيجامة شتوية بنقش نجوم',
                    'description'    => 'بيجامة نجوم دافئة للشتاء بقماش ناعم ومريح مثالي للنوم',
                    'purchase_price' => 130.00,
                    'sale_price'     => 225.00,
                    'stock_quantity' => 25,
                    'image'          => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1007',
                    'brand'          => 'Calvin Klein Underwear',
                    'name'           => 'بيجامة ساتان شتوي فاخر',
                    'description'    => 'بيجامة ساتان دافئة بألوان هادئة وقصة أنيقة لليالي الشتاء الباردة',
                    'purchase_price' => 175.00,
                    'sale_price'     => 300.00,
                    'stock_quantity' => 18,
                    'image'          => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1008',
                    'brand'          => "Victoria's Secret",
                    'name'           => 'بيجامة مخمل مبطن مريح',
                    'description'    => 'بيجامة مخمل مبطن بقطن لدفء إضافي في الشتاء البارد',
                    'purchase_price' => 165.00,
                    'sale_price'     => 285.00,
                    'stock_quantity' => 22,
                    'image'          => 'https://images.unsplash.com/photo-1584273143981-41c073dfe8f8?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1009',
                    'brand'          => 'Hanro',
                    'name'           => 'بيجامة حرير صناعي شتوي',
                    'description'    => 'بيجامة حرير صناعي ناعم ودافئ بتصميم كلاسيكي أنيق',
                    'purchase_price' => 145.00,
                    'sale_price'     => 250.00,
                    'stock_quantity' => 20,
                    'image'          => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ1010',
                    'brand'          => 'DKNY',
                    'name'           => 'طقم بيجامة شتوي بنقش كاروه',
                    'description'    => 'طقم بيجامة بنقش كاروه كلاسيكي دافئ ومريح لليالي الشتاء',
                    'purchase_price' => 150.00,
                    'sale_price'     => 260.00,
                    'stock_quantity' => 30,
                    'image'          => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
        'بيجامات صيفية' => [
            'supplier' => 'Lingerie & Pyjama Import Fès',
            'products' => [
                [
                    'barcode'        => 'PJ2001',
                    'brand'          => "Victoria's Secret",
                    'name'           => 'بيجامة ساتان حريري أنيقة',
                    'description'    => 'بيجامة ساتان كلاسيكية ناعمة بأكمام قصيرة وشورت خفيف ومريح',
                    'purchase_price' => 150.00,
                    'sale_price'     => 260.00,
                    'stock_quantity' => 40,
                    'image'          => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2002',
                    'brand'          => 'Oysho',
                    'name'           => 'بيجامة قطن 100% شورت وتيشيرت',
                    'description'    => 'بيجامة صيفية مصنوعة من القطن الطبيعي 100% لتنفس مثالي ومقاومة للحرارة',
                    'purchase_price' => 90.00,
                    'sale_price'     => 160.00,
                    'stock_quantity' => 48,
                    'image'          => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2003',
                    'brand'          => 'Etam',
                    'name'           => 'بيجامة صيفية بنمط زهور منعش',
                    'description'    => 'بيجامة فيسكوز صيفية ناعمة ومنسدلة بنقشات أزهار ربيعية جذابة',
                    'purchase_price' => 110.00,
                    'sale_price'     => 195.00,
                    'stock_quantity' => 30,
                    'image'          => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2004',
                    'brand'          => 'La Vie en Rose',
                    'name'           => 'قميص نوم ساتان ناعم مع دانتيل',
                    'description'    => 'قميص نوم (Nuisette) من الساتان الفاخر بحواف دانتيل فرنسي راقٍ',
                    'purchase_price' => 120.00,
                    'sale_price'     => 220.00,
                    'stock_quantity' => 24,
                    'image'          => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2005',
                    'brand'          => "Women'Secret",
                    'name'           => 'طقم بيجامة قطن سترابلس خفيف',
                    'description'    => 'طقم توب بحمالات رفيعة وشورت مريح جداً لنوم هادئ في ليالي الصيف',
                    'purchase_price' => 85.00,
                    'sale_price'     => 150.00,
                    'stock_quantity' => 35,
                    'image'          => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2006',
                    'brand'          => 'Intimissimi',
                    'name'           => 'بيجامة صيفية مطبوعة بزهور استوائية',
                    'description'    => 'بيجامة خفيفة بنقشة استوائية ملونة مثالية للصيف الحار',
                    'purchase_price' => 95.00,
                    'sale_price'     => 170.00,
                    'stock_quantity' => 32,
                    'image'          => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2007',
                    'brand'          => 'Yamamay',
                    'name'           => 'شورت وكامي ساتان أنيق',
                    'description'    => 'طقم كامي وشورت ساتان خفيف وأنيق بتصميم بسيط ومريح',
                    'purchase_price' => 80.00,
                    'sale_price'     => 145.00,
                    'stock_quantity' => 40,
                    'image'          => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2008',
                    'brand'          => 'Calvin Klein Underwear',
                    'name'           => 'نايتي قطن مزخرف خفيف',
                    'description'    => 'قميص نوم قصير من القطن الخفيف بزخارف رقيقة مناسب للصيف',
                    'purchase_price' => 75.00,
                    'sale_price'     => 135.00,
                    'stock_quantity' => 38,
                    'image'          => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2009',
                    'brand'          => 'Hanro',
                    'name'           => 'بيجامة موسلين صيفية فاخرة',
                    'description'    => 'بيجامة من قماش الموسلين الفاخر خفيفة كالريشة ومريحة طوال الليل',
                    'purchase_price' => 130.00,
                    'sale_price'     => 230.00,
                    'stock_quantity' => 20,
                    'image'          => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'PJ2010',
                    'brand'          => 'DKNY',
                    'name'           => 'طقم قصير قطني مطبوع كاجوال',
                    'description'    => 'طقم توب وشورت قطن ناعم بطباعة جميلة للاسترخاء في المنزل',
                    'purchase_price' => 70.00,
                    'sale_price'     => 125.00,
                    'stock_quantity' => 45,
                    'image'          => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
        'ملابس منزلية' => [
            'supplier' => 'Confection Atlas Marrakech',
            'products' => [
                [
                    'barcode'        => 'HW3001',
                    'brand'          => 'La Vie en Rose',
                    'name'           => 'روب دو شومبر قطيفة فاخر',
                    'description'    => 'روب حمام ومنزل طويل من المخمل السميك والناعم بجيوب وحزام خصر',
                    'purchase_price' => 220.00,
                    'sale_price'     => 380.00,
                    'stock_quantity' => 16,
                    'image'          => 'https://images.unsplash.com/photo-1618932260643-eee4a2f652a6?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3002',
                    'brand'          => "Victoria's Secret",
                    'name'           => 'كيمونو ساتان ياباني مطرز',
                    'description'    => 'كيمونو طويل من الساتان البراق مطرز بتفاصيل زهرية راقية على الأكمام والظهر',
                    'purchase_price' => 180.00,
                    'sale_price'     => 310.00,
                    'stock_quantity' => 20,
                    'image'          => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3003',
                    'brand'          => "Women'Secret",
                    'name'           => 'كسوة منزلية قطنية كاجوال',
                    'description'    => 'فستان منزلي قطني خفيف وعملي ومريح طوال اليوم بقصة مريحة',
                    'purchase_price' => 100.00,
                    'sale_price'     => 175.00,
                    'stock_quantity' => 40,
                    'image'          => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3004',
                    'brand'          => 'Oysho',
                    'name'           => 'طقم منزلي هودي وبنطلون صوف',
                    'description'    => 'طقم مريح قطعتين هودي وبنطلون مناسب للبيت والطلعات الخفيفة',
                    'purchase_price' => 195.00,
                    'sale_price'     => 340.00,
                    'stock_quantity' => 24,
                    'image'          => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3005',
                    'brand'          => 'Etam',
                    'name'           => 'كارديجان منزلي طويل ودافئ',
                    'description'    => 'جاكيت منزلي مفتوح طويل من الصوف الناعم يوضع فوق البيجامات',
                    'purchase_price' => 130.00,
                    'sale_price'     => 230.00,
                    'stock_quantity' => 22,
                    'image'          => 'https://images.unsplash.com/photo-1508427953056-b00b8d78ebf5?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3006',
                    'brand'          => 'Intimissimi',
                    'name'           => 'جلابية منزلية مطرزة مغربية',
                    'description'    => 'جلابية منزلية أنيقة مطرزة بزخارف مغربية تقليدية من القطن المريح',
                    'purchase_price' => 110.00,
                    'sale_price'     => 195.00,
                    'stock_quantity' => 30,
                    'image'          => 'https://images.unsplash.com/photo-1618932260643-eee4a2f652a6?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3007',
                    'brand'          => 'Yamamay',
                    'name'           => 'روب قطيفة قصير مريح',
                    'description'    => 'روب قصير من القطيفة الناعمة مثالي للحمام والاسترخاء في البيت',
                    'purchase_price' => 150.00,
                    'sale_price'     => 260.00,
                    'stock_quantity' => 25,
                    'image'          => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3008',
                    'brand'          => 'Calvin Klein Underwear',
                    'name'           => 'طقم منزلي كاشيميرة دافئ',
                    'description'    => 'طقم منزلي من الكاشيميرة الدافئة بتصميم أنيق وعملي',
                    'purchase_price' => 200.00,
                    'sale_price'     => 350.00,
                    'stock_quantity' => 15,
                    'image'          => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3009',
                    'brand'          => 'Hanro',
                    'name'           => 'فستان منزلي واسع مريح',
                    'description'    => 'فستان منزلي قصير واسع بقماش ناعم مريح للاسترخاء والتنقل في المنزل',
                    'purchase_price' => 90.00,
                    'sale_price'     => 160.00,
                    'stock_quantity' => 35,
                    'image'          => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'HW3010',
                    'brand'          => 'DKNY',
                    'name'           => 'طقم ترنينج منزلي ناعم',
                    'description'    => 'طقم ترنينج ناعم للمنزل بقماش مريح مناسب للاسترخاء وممارسة اليوغا',
                    'purchase_price' => 120.00,
                    'sale_price'     => 210.00,
                    'stock_quantity' => 28,
                    'image'          => 'https://images.unsplash.com/photo-1508427953056-b00b8d78ebf5?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
        'ملابس داخلية' => [
            'supplier' => 'Distribution Élégance Tanger',
            'products' => [
                [
                    'barcode'        => 'LG4001',
                    'brand'          => 'Etam',
                    'name'           => 'طقم لانجري دانتيل فرنسي 2 قطع',
                    'description'    => 'طقم دانتيل فرنسي راقٍ وأنيق بحمالات قابلة للتعديل وتصميم جذاب',
                    'purchase_price' => 95.00,
                    'sale_price'     => 180.00,
                    'stock_quantity' => 36,
                    'image'          => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4002',
                    'brand'          => 'Intimissimi',
                    'name'           => 'سوتين بدون حشوة كومفورت ناعم',
                    'description'    => 'حمالة صدر بدون أسلاك مريحة جداً للاستخدام اليومي بقماش ناعم وخفي تحت الملابس',
                    'purchase_price' => 75.00,
                    'sale_price'     => 140.00,
                    'stock_quantity' => 45,
                    'image'          => 'https://images.unsplash.com/photo-1508427953056-b00b8d78ebf5?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4003',
                    'brand'          => 'Calvin Klein Underwear',
                    'name'           => 'باك 3 كلوت قطن مريح أورجينال',
                    'description'    => 'علبة تحتوي على 3 قطع قطن مريح بحزام مطاطي مميز بجودة عالية',
                    'purchase_price' => 60.00,
                    'sale_price'     => 110.00,
                    'stock_quantity' => 60,
                    'image'          => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4004',
                    'brand'          => 'Yamamay',
                    'name'           => 'بودي سوت دانتيل وساتان سهرة',
                    'description'    => 'بودي سوت فاخر من الدانتيل والساتان بظهر مفتوح وقصة أنثوية جذابة',
                    'purchase_price' => 130.00,
                    'sale_price'     => 230.00,
                    'stock_quantity' => 24,
                    'image'          => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4005',
                    'brand'          => 'La Vie en Rose',
                    'name'           => 'حمالة صدر رياضية داعمة',
                    'description'    => 'برا رياضي قطني مرن يوفر دعماً مثالياً وحرية كاملة للحركة',
                    'purchase_price' => 80.00,
                    'sale_price'     => 150.00,
                    'stock_quantity' => 40,
                    'image'          => 'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4006',
                    'brand'          => 'Oysho',
                    'name'           => 'طقم دانتيل شفاف أنيق',
                    'description'    => 'طقم دانتيل شفاف أنيق بتصميم راقٍ مناسب للسهرات الرومانسية',
                    'purchase_price' => 110.00,
                    'sale_price'     => 195.00,
                    'stock_quantity' => 28,
                    'image'          => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4007',
                    'brand'          => "Women'Secret",
                    'name'           => 'سوتين مبطن دفعة واحدة',
                    'description'    => 'حمالة صدر مبطنة بتبطين خفيف ومريح مناسبة للاستخدام اليومي',
                    'purchase_price' => 85.00,
                    'sale_price'     => 155.00,
                    'stock_quantity' => 42,
                    'image'          => 'https://images.unsplash.com/photo-1508427953056-b00b8d78ebf5?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4008',
                    'brand'          => "Victoria's Secret",
                    'name'           => 'بيكيني بحري أنيق',
                    'description'    => 'مايوه بيكيني بتصميم أنيق مناسب للشاطئ والسباحة',
                    'purchase_price' => 90.00,
                    'sale_price'     => 165.00,
                    'stock_quantity' => 30,
                    'image'          => 'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4009',
                    'brand'          => 'Hanro',
                    'name'           => 'شورت داخلي كوتون كلاسيكي',
                    'description'    => 'شورت داخلي قطني كلاسيكي مريح للاستخدام اليومي',
                    'purchase_price' => 55.00,
                    'sale_price'     => 100.00,
                    'stock_quantity' => 55,
                    'image'          => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'barcode'        => 'LG4010',
                    'brand'          => 'DKNY',
                    'name'           => 'طقم سباغيتي وشورت قطن',
                    'description'    => 'طقم داخلي خفيف بحمالات سباغيتي وشورت قطن ناعم مريح',
                    'purchase_price' => 65.00,
                    'sale_price'     => 120.00,
                    'stock_quantity' => 48,
                    'image'          => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ],
    ];

    /**
     * Seed the products for each women's fashion category.
     * Creates exactly 1 default variant per product matching the product barcode
     * and stock_quantity (no size/color breakdown — single-variant model).
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
                        'category_id'    => $category?->id,
                        'supplier_id'    => $supplier?->id,
                        'brand_id'       => $brand?->id,
                        'name'           => $product['name'],
                        'description'    => $product['description'],
                        'barcode_type'   => 'internal',
                        'image'          => $product['image'] ?? null,
                        'purchase_price' => $product['purchase_price'],
                        'sale_price'     => $product['sale_price'],
                        'stock_quantity' => $product['stock_quantity'],
                        'unit'           => 'unité',
                    ]
                );

                // Single default variant (no size/color breakdown).
                // ensureDefaultVariant creates one variant with is_default=1,
                // barcode = product barcode, stock_quantity = product stock.
                $variantStock->ensureDefaultVariant($productModel, (float) $product['stock_quantity']);
            }
        }
    }
}
