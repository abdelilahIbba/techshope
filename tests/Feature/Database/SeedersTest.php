<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SupplierSeeder;

// ─── Category seeding ──────────────────────────────────────────────────────────

test('category seeder creates exactly the 4 shopping categories', function () {
    $this->seed(CategorySeeder::class);

    expect(Category::query()->count())->toBe(4);

    $names = Category::query()->pluck('name')->all();
    foreach (array_column(CategorySeeder::CATEGORIES, 'name') as $expectedName) {
        expect($names)->toContain($expectedName);
    }
});

test('category seeder is idempotent', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);

    expect(Category::query()->count())->toBe(4);
});

// ─── Product seeding ────────────────────────────────────────────────────────────

test('product seeder creates 10 products for each of the 4 categories', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    expect(Product::query()->count())->toBe(40);

    foreach (Category::query()->get() as $category) {
        expect($category->products()->count())->toBe(10);
    }
});

test('product seeder is idempotent', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);

    expect(Product::query()->count())->toBe(40);
});

test('every seeded product has a barcode that is unique', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    $barcodes = Product::query()->pluck('barcode');

    expect($barcodes->count())->toBe($barcodes->unique()->count());
});

test('every seeded product has a positive sale price greater than its purchase price', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    foreach (Product::query()->get() as $product) {
        expect((float) $product->purchase_price)->toBeGreaterThan(0);
        expect((float) $product->sale_price)->toBeGreaterThan((float) $product->purchase_price);
    }
});

test('every seeded product has exactly one product_variants row matching its barcode and stock', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    // Scan and checkout sell against product_variants, not the products row
    // directly — a product with zero variants is unsellable ("Variant not
    // found for barcode: X" on checkout).
    foreach (Product::query()->with('variants')->get() as $product) {
        expect($product->variants)->toHaveCount(1);

        $variant = $product->variants->first();
        expect($variant->barcode)->toBe($product->barcode);
        expect((float) $variant->stock_quantity)->toBe((float) $product->stock_quantity);
        expect($variant->is_default)->toBeTrue();
    }
});

test('a seeded product can actually be checked out through the POS', function () {
    activeLicense();
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    ensurePermission('checkout.create', 'POS', 'Create');
    roleWith('Editor', ['checkout.create']);
    $cashier = userWithRole('Editor');
    $customer = \App\Models\Customer::query()->create(['name' => 'Client Test', 'phone' => '0611111111']);

    $product = Product::query()->first();

    $response = $this->actingAs($cashier)->post(route('pos.checkout'), [
        'items' => [['barcode' => $product->barcode, 'quantity' => 1, 'price' => (float) $product->sale_price]],
        'customer_id' => $customer->id,
    ]);

    $receipt = \App\Models\Receipt::query()->latest()->first();
    $response->assertRedirectToRoute('pos.receipt.print', ['receipt' => $receipt?->id]);
    expect(\App\Models\Sale::query()->count())->toBe(1);
});

test('every seeded product has a unit value accepted by the stock forms', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    // Must match the <select> options in the Add/Edit product forms and the
    // 'in:unité,metre,cm' validation rule — anything else leaves the Edit
    // form's unit <select> unselected and blocks submission.
    foreach (Product::query()->get() as $product) {
        expect($product->unit)->toBeIn(['unité', 'metre', 'cm']);
    }
});

// ─── Brand association ──────────────────────────────────────────────────────────

test('every seeded product is associated with a brand', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    foreach (Product::query()->with('brand')->get() as $product) {
        expect($product->brand_id)->not->toBeNull();
        expect($product->brand)->not->toBeNull();
        expect($product->brand->name)->not->toBe('');
    }
});

test('each category contains 10 products from 10 different brands', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    foreach (Category::query()->get() as $category) {
        $brandIds = $category->products()->pluck('brand_id');

        expect($brandIds->count())->toBe(10);
        expect($brandIds->unique()->count())->toBe(10);
    }
});

test('brand seeder creates exactly one row per distinct brand in the catalog', function () {
    $this->seed(BrandSeeder::class);

    $expectedBrandNames = collect(ProductSeeder::CATALOG)
        ->pluck('products')
        ->flatten(1)
        ->pluck('brand')
        ->unique();

    expect(Brand::query()->count())->toBe($expectedBrandNames->count());

    foreach ($expectedBrandNames as $name) {
        expect(Brand::query()->where('name', $name)->exists())->toBeTrue();
    }
});

test('brand seeder is idempotent', function () {
    $this->seed(BrandSeeder::class);
    $count = Brand::query()->count();

    $this->seed(BrandSeeder::class);

    expect(Brand::query()->count())->toBe($count);
});

test('products are linked to a supplier consistent with their category catalog', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SupplierSeeder::class);
    $this->seed(BrandSeeder::class);
    $this->seed(ProductSeeder::class);

    foreach (ProductSeeder::CATALOG as $categoryName => $data) {
        $category = Category::query()->where('name', $categoryName)->first();
        $supplier = Supplier::query()->where('name', $data['supplier'])->first();

        foreach ($category->products as $product) {
            expect($product->supplier_id)->toBe($supplier->id);
        }
    }
});

// ─── Database consistency after removing outdated seeders ──────────────────────

test('full database seeder no longer creates the old hardware-store categories or products', function () {
    $this->seed(DatabaseSeeder::class);

    $oldCategoryNames = [
        'Peinture & Revêtement',
        'Visserie & Boulonnerie',
        'Outillage à main',
        'Électricité',
        'Plomberie',
    ];
    foreach ($oldCategoryNames as $name) {
        expect(Category::query()->where('name', $name)->exists())->toBeFalse();
    }

    $oldBarcodes = ['7700123', '7700124', '8800551', '8800552', '9900991', '9900992', '2200221', '3300331'];
    foreach ($oldBarcodes as $barcode) {
        expect(Product::query()->where('barcode', $barcode)->exists())->toBeFalse();
    }

    $oldSupplierNames = ['Sarl Peintures Nationales', 'Quincaillerie Centrale', 'Distributeur Électro-Pro'];
    foreach ($oldSupplierNames as $name) {
        expect(Supplier::query()->where('name', $name)->exists())->toBeFalse();
    }
});

test('database is consistent after full seeding: no orphaned products', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBe(4);
    expect(Product::query()->count())->toBe(40);

    $categoryIds = Category::query()->pluck('id');
    $supplierIds = Supplier::query()->pluck('id');
    $brandIds = Brand::query()->pluck('id');

    foreach (Product::query()->get() as $product) {
        expect($categoryIds)->toContain($product->category_id);

        if ($product->supplier_id !== null) {
            expect($supplierIds)->toContain($product->supplier_id);
        }

        if ($product->brand_id !== null) {
            expect($brandIds)->toContain($product->brand_id);
        }
    }
});

test('running the full database seeder twice keeps the catalog stable', function () {
    $this->seed(DatabaseSeeder::class);
    $brandCount = Brand::query()->count();

    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBe(4);
    expect(Supplier::query()->count())->toBe(4);
    expect(Brand::query()->count())->toBe($brandCount);
    expect(Product::query()->count())->toBe(40);
});
