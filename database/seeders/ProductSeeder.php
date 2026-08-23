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
     * 10 products per category, each from a different brand.
     * Keyed by category name -> ['supplier' => ..., 'products' => [...]]
     */
    public const CATALOG = [
        'Smartphones & Téléphonie' => [
            'supplier' => 'TechImport Maroc',
            'products' => [
                ['barcode' => 'SM1001', 'brand' => 'Apple', 'name' => 'iPhone 15', 'description' => 'Smartphone 128 Go, écran 6.1"', 'purchase_price' => 9500, 'sale_price' => 11500, 'stock_quantity' => 20],
                ['barcode' => 'SM1002', 'brand' => 'Samsung', 'name' => 'Galaxy S24', 'description' => 'Smartphone 256 Go, écran AMOLED 6.2"', 'purchase_price' => 8800, 'sale_price' => 10800, 'stock_quantity' => 25],
                ['barcode' => 'SM1003', 'brand' => 'Xiaomi', 'name' => 'Redmi Note 13', 'description' => 'Smartphone 128 Go, triple caméra', 'purchase_price' => 2200, 'sale_price' => 2800, 'stock_quantity' => 40],
                ['barcode' => 'SM1004', 'brand' => 'Huawei', 'name' => 'P60', 'description' => 'Smartphone 256 Go, photo pro', 'purchase_price' => 7200, 'sale_price' => 8900, 'stock_quantity' => 15],
                ['barcode' => 'SM1005', 'brand' => 'OnePlus', 'name' => '12', 'description' => 'Smartphone 256 Go, charge rapide 100W', 'purchase_price' => 7800, 'sale_price' => 9600, 'stock_quantity' => 18],
                ['barcode' => 'SM1006', 'brand' => 'Google', 'name' => 'Pixel 8', 'description' => 'Smartphone 128 Go, IA intégrée', 'purchase_price' => 7000, 'sale_price' => 8700, 'stock_quantity' => 12],
                ['barcode' => 'SM1007', 'brand' => 'Oppo', 'name' => 'Reno 11', 'description' => 'Smartphone 256 Go, écran incurvé', 'purchase_price' => 4200, 'sale_price' => 5300, 'stock_quantity' => 22],
                ['barcode' => 'SM1008', 'brand' => 'Realme', 'name' => '12 Pro', 'description' => 'Smartphone 128 Go, zoom périscope', 'purchase_price' => 3600, 'sale_price' => 4600, 'stock_quantity' => 28],
                ['barcode' => 'SM1009', 'brand' => 'Nokia', 'name' => 'G42', 'description' => 'Smartphone 128 Go, robuste', 'purchase_price' => 1800, 'sale_price' => 2400, 'stock_quantity' => 30],
                ['barcode' => 'SM1010', 'brand' => 'Motorola', 'name' => 'Edge 40', 'description' => 'Smartphone 256 Go, design incurvé', 'purchase_price' => 3900, 'sale_price' => 4900, 'stock_quantity' => 20],
            ],
        ],
        'Ordinateurs & Informatique' => [
            'supplier' => 'Global Computers Distribution',
            'products' => [
                ['barcode' => 'LP2001', 'brand' => 'Apple', 'name' => 'MacBook Air M2', 'description' => 'Laptop 13.6", 8 Go RAM, 256 Go SSD', 'purchase_price' => 13500, 'sale_price' => 16000, 'stock_quantity' => 10],
                ['barcode' => 'LP2002', 'brand' => 'Dell', 'name' => 'XPS 13', 'description' => 'Laptop 13.4", Intel Core i7, 512 Go SSD', 'purchase_price' => 12000, 'sale_price' => 14500, 'stock_quantity' => 12],
                ['barcode' => 'LP2003', 'brand' => 'HP', 'name' => 'Pavilion 15', 'description' => 'Laptop 15.6", Intel Core i5, 512 Go SSD', 'purchase_price' => 6500, 'sale_price' => 8200, 'stock_quantity' => 18],
                ['barcode' => 'LP2004', 'brand' => 'Lenovo', 'name' => 'ThinkPad E14', 'description' => 'Laptop 14", Intel Core i5, 256 Go SSD', 'purchase_price' => 7200, 'sale_price' => 8900, 'stock_quantity' => 15],
                ['barcode' => 'LP2005', 'brand' => 'Asus', 'name' => 'VivoBook 15', 'description' => 'Laptop 15.6", AMD Ryzen 5, 512 Go SSD', 'purchase_price' => 5800, 'sale_price' => 7200, 'stock_quantity' => 20],
                ['barcode' => 'LP2006', 'brand' => 'Acer', 'name' => 'Aspire 5', 'description' => 'Laptop 15.6", Intel Core i3, 256 Go SSD', 'purchase_price' => 5200, 'sale_price' => 6600, 'stock_quantity' => 22],
                ['barcode' => 'LP2007', 'brand' => 'MSI', 'name' => 'Modern 14', 'description' => 'Laptop 14", Intel Core i5, 512 Go SSD', 'purchase_price' => 6800, 'sale_price' => 8400, 'stock_quantity' => 14],
                ['barcode' => 'LP2008', 'brand' => 'Microsoft', 'name' => 'Surface Laptop 5', 'description' => 'Laptop 13.5" tactile, 8 Go RAM, 256 Go SSD', 'purchase_price' => 11500, 'sale_price' => 14000, 'stock_quantity' => 8],
                ['barcode' => 'LP2009', 'brand' => 'Samsung', 'name' => 'Galaxy Book3', 'description' => 'Laptop 15.6", Intel Core i7, 512 Go SSD', 'purchase_price' => 9800, 'sale_price' => 12000, 'stock_quantity' => 10],
                ['barcode' => 'LP2010', 'brand' => 'LG', 'name' => 'Gram 14', 'description' => 'Laptop 14" ultraléger, 16 Go RAM', 'purchase_price' => 10500, 'sale_price' => 12800, 'stock_quantity' => 9],
            ],
        ],
        'Audio & Casques' => [
            'supplier' => 'AudioPro Wholesale',
            'products' => [
                ['barcode' => 'AU3001', 'brand' => 'Sony', 'name' => 'WH-1000XM5', 'description' => 'Casque sans fil à réduction de bruit', 'purchase_price' => 3200, 'sale_price' => 4200, 'stock_quantity' => 15],
                ['barcode' => 'AU3002', 'brand' => 'Bose', 'name' => 'QuietComfort 45', 'description' => 'Casque sans fil confort premium', 'purchase_price' => 2900, 'sale_price' => 3800, 'stock_quantity' => 14],
                ['barcode' => 'AU3003', 'brand' => 'JBL', 'name' => 'Tune 510BT', 'description' => 'Casque sans fil léger, autonomie 40h', 'purchase_price' => 350, 'sale_price' => 500, 'stock_quantity' => 50],
                ['barcode' => 'AU3004', 'brand' => 'Beats', 'name' => 'Studio Buds', 'description' => 'Écouteurs sans fil, réduction de bruit active', 'purchase_price' => 900, 'sale_price' => 1300, 'stock_quantity' => 30],
                ['barcode' => 'AU3005', 'brand' => 'Sennheiser', 'name' => 'HD 450BT', 'description' => 'Casque sans fil, son Hi-Fi', 'purchase_price' => 700, 'sale_price' => 1000, 'stock_quantity' => 25],
                ['barcode' => 'AU3006', 'brand' => 'Apple', 'name' => 'AirPods Pro 2', 'description' => 'Écouteurs sans fil, réduction de bruit active', 'purchase_price' => 2300, 'sale_price' => 2900, 'stock_quantity' => 22],
                ['barcode' => 'AU3007', 'brand' => 'Samsung', 'name' => 'Galaxy Buds2 Pro', 'description' => 'Écouteurs sans fil, son haute résolution', 'purchase_price' => 1800, 'sale_price' => 2400, 'stock_quantity' => 20],
                ['barcode' => 'AU3008', 'brand' => 'Anker', 'name' => 'Soundcore Life Q35', 'description' => 'Casque sans fil, réduction de bruit adaptative', 'purchase_price' => 600, 'sale_price' => 850, 'stock_quantity' => 35],
                ['barcode' => 'AU3009', 'brand' => 'Marshall', 'name' => 'Major IV', 'description' => 'Casque sans fil, charge sans fil intégrée', 'purchase_price' => 1100, 'sale_price' => 1500, 'stock_quantity' => 18],
                ['barcode' => 'AU3010', 'brand' => 'Skullcandy', 'name' => 'Crusher Evo', 'description' => 'Casque sans fil, basses ajustables', 'purchase_price' => 800, 'sale_price' => 1150, 'stock_quantity' => 24],
            ],
        ],
        'Gaming & Consoles' => [
            'supplier' => 'GameZone Distribution',
            'products' => [
                ['barcode' => 'GM4001', 'brand' => 'Sony', 'name' => 'PlayStation 5', 'description' => 'Console de jeu nouvelle génération', 'purchase_price' => 5800, 'sale_price' => 6900, 'stock_quantity' => 10],
                ['barcode' => 'GM4002', 'brand' => 'Microsoft', 'name' => 'Xbox Series X', 'description' => 'Console de jeu 1 To, 4K natif', 'purchase_price' => 5600, 'sale_price' => 6700, 'stock_quantity' => 10],
                ['barcode' => 'GM4003', 'brand' => 'Nintendo', 'name' => 'Switch OLED', 'description' => 'Console hybride, écran OLED 7"', 'purchase_price' => 3200, 'sale_price' => 3900, 'stock_quantity' => 16],
                ['barcode' => 'GM4004', 'brand' => 'Logitech', 'name' => 'G502 Hero', 'description' => 'Souris gaming filaire, capteur 25K DPI', 'purchase_price' => 400, 'sale_price' => 580, 'stock_quantity' => 40],
                ['barcode' => 'GM4005', 'brand' => 'Razer', 'name' => 'DeathAdder V3', 'description' => 'Souris gaming ergonomique, sans fil', 'purchase_price' => 450, 'sale_price' => 650, 'stock_quantity' => 35],
                ['barcode' => 'GM4006', 'brand' => 'Corsair', 'name' => 'K70 RGB', 'description' => 'Clavier mécanique gaming, rétroéclairage RGB', 'purchase_price' => 900, 'sale_price' => 1250, 'stock_quantity' => 20],
                ['barcode' => 'GM4007', 'brand' => 'SteelSeries', 'name' => 'Arctis 7', 'description' => 'Casque gaming sans fil, son surround', 'purchase_price' => 1200, 'sale_price' => 1600, 'stock_quantity' => 18],
                ['barcode' => 'GM4008', 'brand' => 'HyperX', 'name' => 'Cloud II', 'description' => 'Casque gaming filaire, son 7.1 virtuel', 'purchase_price' => 500, 'sale_price' => 700, 'stock_quantity' => 30],
                ['barcode' => 'GM4009', 'brand' => 'Asus', 'name' => 'ROG Strix G16', 'description' => 'Laptop gaming 16", RTX, 16 Go RAM', 'purchase_price' => 14500, 'sale_price' => 17500, 'stock_quantity' => 6],
                ['barcode' => 'GM4010', 'brand' => 'MSI', 'name' => 'Katana 15', 'description' => 'Laptop gaming 15.6", RTX, 16 Go RAM', 'purchase_price' => 9800, 'sale_price' => 12000, 'stock_quantity' => 8],
            ],
        ],
    ];

    /**
     * Seed the products for each shopping category.
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
                        'purchase_price' => $product['purchase_price'],
                        'sale_price' => $product['sale_price'],
                        'stock_quantity' => $product['stock_quantity'],
                        'unit' => 'unité',
                    ]
                );

                // Every product needs a product_variants row — scan, checkout
                // and the POS grid all sell against variants, not the product
                // row directly. ensureDefaultVariant() is a no-op if one
                // already exists, so re-seeding stays idempotent.
                $variantStock->ensureDefaultVariant($productModel, (float) $product['stock_quantity']);
            }
        }
    }
}
