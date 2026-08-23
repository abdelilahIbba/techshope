<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

// ─── Brand CRUD tests ───────────────────────────────────────────────────────────

beforeEach(function () {
    activeLicense();

    ensurePermission('stock.view',    'Stock', 'View');
    ensurePermission('stock.create',  'Stock', 'Create');
    ensurePermission('stock.edit',    'Stock', 'Edit');
    ensurePermission('brand.manage',  'Stock', 'Edit');

    roleWith('Viewer', ['stock.view']);
    roleWith('Editor', ['stock.view', 'stock.create', 'stock.edit', 'brand.manage']);
});

function makeBrand(string $name = 'Apple'): Brand
{
    return Brand::query()->create(['name' => $name]);
}

function makeCategoryForBrandTest(): Category
{
    return Category::query()->firstOrCreate(['name' => 'Smartphones'], ['description' => '']);
}

// ─── Create ─────────────────────────────────────────────────────────────────────

test('admin can add a brand', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.stock.add-brand'), [
        'name' => 'Apple',
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    expect(Brand::query()->where('name', 'Apple')->exists())->toBeTrue();
});

test('adding a brand with a duplicate name fails', function () {
    $admin = userWithRole('Admin');
    makeBrand('Samsung');

    $this->actingAs($admin)->post(route('pos.stock.add-brand'), [
        'name' => 'Samsung',
    ])->assertSessionHasErrors('name');

    expect(Brand::query()->where('name', 'Samsung')->count())->toBe(1);
});

test('adding a brand requires a name', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.stock.add-brand'), [
        'name' => '',
    ])->assertSessionHasErrors('name');
});

// ─── Update ─────────────────────────────────────────────────────────────────────

test('admin can edit a brand', function () {
    $admin = userWithRole('Admin');
    $brand = makeBrand('OldBrandName');

    $this->actingAs($admin)->put(route('pos.stock.edit-brand', $brand), [
        'name' => 'NewBrandName',
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    expect($brand->fresh()->name)->toBe('NewBrandName');
});

test('editing a brand to a name used by another brand fails', function () {
    $admin = userWithRole('Admin');
    makeBrand('Sony');
    $brand = makeBrand('Bose');

    $this->actingAs($admin)->put(route('pos.stock.edit-brand', $brand), [
        'name' => 'Sony',
    ])->assertSessionHasErrors('name');

    expect($brand->fresh()->name)->toBe('Bose');
});

// ─── Delete ─────────────────────────────────────────────────────────────────────

test('admin can delete a brand with no linked products', function () {
    $admin = userWithRole('Admin');
    $brand = makeBrand('ToDelete');

    $this->actingAs($admin)->delete(route('pos.stock.delete-brand', $brand))
         ->assertRedirect(route('pos.stock'))
         ->assertSessionHas('success');

    expect(Brand::query()->find($brand->id))->toBeNull();
});

test('deleting a brand linked to products is blocked', function () {
    $admin    = userWithRole('Admin');
    $brand    = makeBrand('Nike');
    $category = makeCategoryForBrandTest();
    Product::query()->create([
        'name' => 'Air Max', 'barcode' => 'BR001', 'category_id' => $category->id,
        'brand_id' => $brand->id, 'stock_quantity' => 5, 'purchase_price' => 10,
        'sale_price' => 20, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->delete(route('pos.stock.delete-brand', $brand))
         ->assertRedirect(route('pos.stock'))
         ->assertSessionHas('error');

    expect(Brand::query()->find($brand->id))->not->toBeNull();
});

// ─── Permission enforcement ─────────────────────────────────────────────────────

test('viewer cannot add a brand', function () {
    $viewer = userWithRole('Viewer');

    $this->actingAs($viewer)->post(route('pos.stock.add-brand'), [
        'name' => 'Blocked',
    ])->assertStatus(403);

    expect(Brand::query()->where('name', 'Blocked')->exists())->toBeFalse();
});

test('viewer cannot edit a brand', function () {
    $viewer = userWithRole('Viewer');
    $brand  = makeBrand('Protected');

    $this->actingAs($viewer)->put(route('pos.stock.edit-brand', $brand), [
        'name' => 'Hacked',
    ])->assertStatus(403);

    expect($brand->fresh()->name)->toBe('Protected');
});

test('viewer cannot delete a brand', function () {
    $viewer = userWithRole('Viewer');
    $brand  = makeBrand('Protected');

    $this->actingAs($viewer)->delete(route('pos.stock.delete-brand', $brand))
         ->assertStatus(403);

    expect(Brand::query()->find($brand->id))->not->toBeNull();
});

// ─── Product <-> Brand association ──────────────────────────────────────────────

test('admin can add a product associated with a brand', function () {
    $admin    = userWithRole('Admin');
    $brand    = makeBrand('Xiaomi');
    $category = makeCategoryForBrandTest();

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Redmi Note 13',
        'barcode'        => 'BRP001',
        'category_id'    => $category->id,
        'brand_id'       => $brand->id,
        'stock_quantity' => 10,
        'purchase_price' => 100.00,
        'sale_price'     => 150.00,
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    $product = Product::query()->where('barcode', 'BRP001')->first();
    expect($product)->not->toBeNull();
    expect($product->brand_id)->toBe($brand->id);
    expect($product->brand->name)->toBe('Xiaomi');
});

test('adding a product with a non-existent brand_id fails validation', function () {
    $admin    = userWithRole('Admin');
    $category = makeCategoryForBrandTest();

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Ghost Product',
        'barcode'        => 'BRP002',
        'category_id'    => $category->id,
        'brand_id'       => 999999,
        'stock_quantity' => 10,
        'purchase_price' => 100.00,
        'sale_price'     => 150.00,
    ])->assertSessionHasErrors('brand_id');

    expect(Product::query()->where('barcode', 'BRP002')->exists())->toBeFalse();
});

test('admin can add a product without a brand', function () {
    $admin    = userWithRole('Admin');
    $category = makeCategoryForBrandTest();

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'No Brand Product',
        'barcode'        => 'BRP003',
        'category_id'    => $category->id,
        'stock_quantity' => 10,
        'purchase_price' => 100.00,
        'sale_price'     => 150.00,
    ])->assertRedirect(route('pos.stock'));

    $product = Product::query()->where('barcode', 'BRP003')->first();
    expect($product->brand_id)->toBeNull();
});

test('admin can change a product brand via edit', function () {
    $admin      = userWithRole('Admin');
    $category   = makeCategoryForBrandTest();
    $oldBrand   = makeBrand('Huawei');
    $newBrand   = makeBrand('OnePlus');
    $product    = Product::query()->create([
        'name' => 'P60', 'barcode' => 'BRP004', 'category_id' => $category->id,
        'brand_id' => $oldBrand->id, 'stock_quantity' => 5, 'purchase_price' => 10,
        'sale_price' => 20, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name'           => 'P60',
        'barcode'        => 'BRP004',
        'category_id'    => $category->id,
        'brand_id'       => $newBrand->id,
        'purchase_price' => 10,
        'sale_price'     => 20,
    ])->assertRedirect(route('pos.stock'));

    expect($product->fresh()->brand_id)->toBe($newBrand->id);
});

// ─── Add/Edit form consistency ───────────────────────────────────────────────────

test('the stock page exposes the same brand list to add and edit product forms', function () {
    $admin = userWithRole('Admin');
    makeBrand('Apple');
    makeBrand('Samsung');
    makeBrand('Sony');

    $response = $this->actingAs($admin)->get(route('pos.stock'));

    $response->assertOk();
    $html = $response->getContent();

    // Both the "Ajouter un produit" and "Modifier le produit" brand <select>
    // are rendered from the same $brands collection, so every brand name
    // appears at least twice: once per <select id="..._brand"> option list.
    foreach (['Apple', 'Samsung', 'Sony'] as $brandName) {
        expect(substr_count($html, $brandName))->toBeGreaterThanOrEqual(2);
    }
});

// ─── Database consistency ────────────────────────────────────────────────────────

test('deleting a product does not delete its brand', function () {
    $category = makeCategoryForBrandTest();
    $brand    = makeBrand('Lenovo');
    $product  = Product::query()->create([
        'name' => 'ThinkPad', 'barcode' => 'BRP005', 'category_id' => $category->id,
        'brand_id' => $brand->id, 'stock_quantity' => 5, 'purchase_price' => 10,
        'sale_price' => 20, 'unit' => 'pcs',
    ]);

    $admin = userWithRole('Admin');
    $this->actingAs($admin)->delete(route('pos.stock.delete-product', $product));

    expect(Brand::query()->find($brand->id))->not->toBeNull();
});
