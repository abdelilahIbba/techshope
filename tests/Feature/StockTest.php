<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;

// ─── Stock / Inventory tests ──────────────────────────────────────────────────

beforeEach(function () {
    activeLicense();

    // Ensure all stock-related permissions exist
    ensurePermission('stock.view',       'Stock', 'View');
    ensurePermission('stock.create',     'Stock', 'Create');
    ensurePermission('stock.edit',       'Stock', 'Edit');
    ensurePermission('stock.delete',     'Stock', 'Delete');
    ensurePermission('stock.adjust',     'Stock', 'Edit');
    ensurePermission('supplier.manage',  'Stock', 'Edit');
    ensurePermission('category.manage',  'Stock', 'Edit');
    ensurePermission('settings.view',    'Settings', 'View');

    roleWith('Viewer', ['stock.view']);
    roleWith('Editor', ['stock.view', 'stock.create', 'stock.edit', 'stock.delete',
                        'stock.adjust', 'supplier.manage', 'category.manage']);
});

// ─── Products ─────────────────────────────────────────────────────────────────

test('admin can add a product', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Paint', 'description' => '']);

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'White Paint 5L',
        'barcode'        => '555001',
        'category_id'    => $category->id,
        'stock_quantity' => 50,
        'purchase_price' => 100.00,
        'sale_price'     => 130.00,
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    expect(Product::query()->where('barcode', '555001')->exists())->toBeTrue();
});

test('add product auto-generates barcode when omitted', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Paint', 'description' => '']);

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Blue Paint',
        'category_id'    => $category->id,
        'stock_quantity' => 10,
        'purchase_price' => 50.00,
        'sale_price'     => 70.00,
    ])->assertRedirect(route('pos.stock'));

    $product = Product::query()->where('name', 'Blue Paint')->first();
    expect($product)->not->toBeNull();
    expect($product->barcode)->not->toBeEmpty();
});

test('add product with duplicate barcode fails', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Paint', 'description' => '']);
    Product::query()->create(['name' => 'Existing', 'barcode' => 'DUP001',
        'category_id' => $category->id, 'stock_quantity' => 1,
        'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'pcs']);

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name' => 'NewProduct', 'barcode' => 'DUP001',
        'category_id' => $category->id, 'stock_quantity' => 5,
        'purchase_price' => 10, 'sale_price' => 15,
    ])->assertSessionHasErrors('barcode');
});

test('admin can edit a product', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Paint', 'description' => '']);
    $product  = Product::query()->create([
        'name' => 'Old Name', 'barcode' => 'EDIT001', 'category_id' => $category->id,
        'stock_quantity' => 10, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name'           => 'New Name',
        'barcode'        => 'EDIT001',
        'category_id'    => $category->id,
        'purchase_price' => 12.00,
        'sale_price'     => 18.00,
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    expect($product->fresh()->name)->toBe('New Name');
    expect((float) $product->fresh()->sale_price)->toBe(18.0);
});

test('admin can delete a product', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Paint', 'description' => '']);
    $product  = Product::query()->create([
        'name' => 'To Delete', 'barcode' => 'DEL001', 'category_id' => $category->id,
        'stock_quantity' => 5, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->delete(route('pos.stock.delete-product', $product))
         ->assertRedirect(route('pos.stock'))
         ->assertSessionHas('success');

    expect(Product::query()->find($product->id))->toBeNull();
});

// ─── Stock adjustment ─────────────────────────────────────────────────────────

test('admin can add to stock quantity', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Tools', 'description' => '']);
    $product  = Product::query()->create([
        'name' => 'Hammer', 'barcode' => 'ADJ001', 'category_id' => $category->id,
        'stock_quantity' => 10, 'purchase_price' => 5, 'sale_price' => 8, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->post(route('pos.stock.adjust-stock', $product), [
        'adjustment_type' => 'add',
        'quantity'        => 20,
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    expect($product->fresh()->stock_quantity)->toBe(30);
});

test('admin can remove from stock quantity', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Tools', 'description' => '']);
    $product  = Product::query()->create([
        'name' => 'Chisel', 'barcode' => 'ADJ002', 'category_id' => $category->id,
        'stock_quantity' => 20, 'purchase_price' => 5, 'sale_price' => 8, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->post(route('pos.stock.adjust-stock', $product), [
        'adjustment_type' => 'remove',
        'quantity'        => 5,
    ]);

    expect($product->fresh()->stock_quantity)->toBe(15);
});

test('removing more than available stock sets quantity to zero', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Tools', 'description' => '']);
    $product  = Product::query()->create([
        'name' => 'Nail', 'barcode' => 'ADJ003', 'category_id' => $category->id,
        'stock_quantity' => 5, 'purchase_price' => 1, 'sale_price' => 2, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->post(route('pos.stock.adjust-stock', $product), [
        'adjustment_type' => 'remove',
        'quantity'        => 100,
    ]);

    expect($product->fresh()->stock_quantity)->toBe(0);
});

test('admin can set stock to an absolute value', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'Tools', 'description' => '']);
    $product  = Product::query()->create([
        'name' => 'Wrench', 'barcode' => 'ADJ004', 'category_id' => $category->id,
        'stock_quantity' => 99, 'purchase_price' => 5, 'sale_price' => 8, 'unit' => 'pcs',
    ]);

    $this->actingAs($admin)->post(route('pos.stock.adjust-stock', $product), [
        'adjustment_type' => 'set',
        'quantity'        => 42,
    ]);

    expect($product->fresh()->stock_quantity)->toBe(42);
});

// ─── Suppliers ────────────────────────────────────────────────────────────────

test('admin can add a supplier', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.stock.add-supplier'), [
        'name'         => 'Astra Paints',
        'contact_info' => '0500000000',
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    expect(Supplier::query()->where('name', 'Astra Paints')->exists())->toBeTrue();
});

test('admin can edit a supplier', function () {
    $admin    = userWithRole('Admin');
    $supplier = Supplier::query()->create(['name' => 'SupOld', 'contact_info' => '']);

    $this->actingAs($admin)->put(route('pos.stock.edit-supplier', $supplier), [
        'name' => 'SupNew',
    ])->assertRedirect(route('pos.stock'));

    expect($supplier->fresh()->name)->toBe('SupNew');
});

test('admin can delete a supplier', function () {
    $admin    = userWithRole('Admin');
    $supplier = Supplier::query()->create(['name' => 'ToDelete', 'contact_info' => '']);

    $this->actingAs($admin)->delete(route('pos.stock.delete-supplier', $supplier))
         ->assertRedirect(route('pos.stock'));

    expect(Supplier::query()->find($supplier->id))->toBeNull();
});

// ─── Categories ───────────────────────────────────────────────────────────────

test('admin can add a category', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post(route('pos.stock.add-category'), [
        'name'        => 'Nails & Screws',
        'description' => 'Fasteners',
    ])->assertRedirect(route('pos.stock'))
      ->assertSessionHas('success');

    expect(Category::query()->where('name', 'Nails & Screws')->exists())->toBeTrue();
});

test('admin can edit a category', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'OldCat', 'description' => '']);

    $this->actingAs($admin)->put(route('pos.stock.edit-category', $category), [
        'name'        => 'NewCat',
        'description' => 'Updated',
    ])->assertRedirect(route('pos.stock'));

    expect($category->fresh()->name)->toBe('NewCat');
});

test('admin can delete a category', function () {
    $admin    = userWithRole('Admin');
    $category = Category::query()->create(['name' => 'DelCat', 'description' => '']);

    $this->actingAs($admin)->delete(route('pos.stock.delete-category', $category))
         ->assertRedirect(route('pos.stock'));

    expect(Category::query()->find($category->id))->toBeNull();
});

// ─── Permission enforcement ───────────────────────────────────────────────────

test('viewer can view stock page', function () {
    $viewer = userWithRole('Viewer');

    $this->actingAs($viewer)->get(route('pos.stock'))
         ->assertOk();
});

test('viewer cannot add a product', function () {
    $viewer   = userWithRole('Viewer');
    $category = Category::query()->create(['name' => 'Cat', 'description' => '']);

    $this->actingAs($viewer)->post(route('pos.stock.add-product'), [
        'name' => 'Blocked', 'barcode' => 'BLK001',
        'category_id' => $category->id,
        'stock_quantity' => 1, 'purchase_price' => 1, 'sale_price' => 2,
    ])->assertStatus(403);
});

test('viewer cannot delete a product', function () {
    $viewer   = userWithRole('Viewer');
    $category = Category::query()->create(['name' => 'Cat', 'description' => '']);
    $product  = Product::query()->create([
        'name' => 'Protected', 'barcode' => 'PROT01', 'category_id' => $category->id,
        'stock_quantity' => 5, 'purchase_price' => 1, 'sale_price' => 2, 'unit' => 'pcs',
    ]);

    $this->actingAs($viewer)->delete(route('pos.stock.delete-product', $product))
         ->assertStatus(403);
});
