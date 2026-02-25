<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Sale;
use App\Models\SaleItem;

// ─── Checkout / Sale tests ────────────────────────────────────────────────────

beforeEach(function () {
    activeLicense();

    ensurePermission('pos.view',        'POS', 'View');
    ensurePermission('checkout.create', 'POS', 'Create');
    ensurePermission('receipt.export',  'POS', 'Export');

    roleWith('Editor', ['pos.view', 'checkout.create', 'receipt.export']);
    roleWith('Viewer', ['pos.view']);
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeProduct(string $barcode = 'PROD001', int $stock = 100, float $price = 15.0): Product
{
    $category = Category::query()->firstOrCreate(['name' => 'Test Category'], ['description' => '']);
    return Product::query()->create([
        'name'           => "Product {$barcode}",
        'barcode'        => $barcode,
        'category_id'    => $category->id,
        'stock_quantity' => $stock,
        'purchase_price' => $price * 0.7,
        'sale_price'     => $price,
        'unit'           => 'pcs',
    ]);
}

function makeCustomer(): Customer
{
    return Customer::query()->firstOrCreate(
        ['phone' => '0000000001'],
        ['name' => 'Test Customer', 'loyalty_points' => 0]
    );
}

// ─── Barcode scan ─────────────────────────────────────────────────────────────

test('scan returns product json for known barcode', function () {
    $admin   = userWithRole('Admin');
    $product = makeProduct('SCAN001', 50, 20.0);

    $this->actingAs($admin)->getJson(route('pos.scan', ['barcode' => 'SCAN001']))
         ->assertOk()
         ->assertJsonFragment(['barcode' => 'SCAN001'])
         ->assertJsonStructure(['id', 'name', 'barcode', 'price', 'stock_quantity']);
});

test('scan returns 404 for unknown barcode', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->getJson(route('pos.scan', ['barcode' => 'NOTEXIST']))
         ->assertNotFound();
});

test('viewer without pos.view permission cannot scan', function () {
    roleWith('Viewer', []); // override — no pos.view
    $viewer = userWithRole('Viewer');

    $this->actingAs($viewer)->getJson(route('pos.scan', ['barcode' => 'ANYTHING']))
         ->assertStatus(403);
});

// ─── Checkout: success ────────────────────────────────────────────────────────

test('checkout creates a sale, sale items and a receipt', function () {
    $admin    = userWithRole('Admin');
    $product  = makeProduct('CHK001', 100, 15.0);
    $customer = makeCustomer();

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items'          => [['barcode' => 'CHK001', 'quantity' => 2]],
        'customer_id'    => $customer->id,
        'payment_method' => 'cash',
    ])->assertRedirect();   // redirects to receipt PDF route

    expect(Sale::query()->count())->toBe(1);
    expect(SaleItem::query()->count())->toBe(1);
    expect(Receipt::query()->count())->toBe(1);
});

test('checkout decrements product stock', function () {
    $admin    = userWithRole('Admin');
    $product  = makeProduct('CHK002', 50, 10.0);
    $customer = makeCustomer();

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items'       => [['barcode' => 'CHK002', 'quantity' => 5]],
        'customer_id' => $customer->id,
    ]);

    expect($product->fresh()->stock_quantity)->toBe(45);
});

test('checkout calculates total amount correctly', function () {
    $admin    = userWithRole('Admin');
    $product  = makeProduct('CHK003', 100, 20.0);
    $customer = makeCustomer();

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items'       => [['barcode' => 'CHK003', 'quantity' => 3]],
        'customer_id' => $customer->id,
    ]);

    $sale = Sale::query()->latest()->first();
    expect((float) $sale->total_amount)->toBe(60.0);
});

test('checkout assigns payment method', function () {
    $admin    = userWithRole('Admin');
    $product  = makeProduct('CHK004', 10, 5.0);
    $customer = makeCustomer();

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items'          => [['barcode' => 'CHK004', 'quantity' => 1]],
        'customer_id'    => $customer->id,
        'payment_method' => 'card',
    ]);

    expect(Sale::query()->latest()->first()->payment_method)->toBe('card');
});

test('checkout picks the default customer when none specified', function () {
    $admin    = userWithRole('Admin');
    $product  = makeProduct('CHK005', 10, 5.0);
    makeCustomer(); // ensures at least one customer exists

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items' => [['barcode' => 'CHK005', 'quantity' => 1]],
        // no customer_id
    ])->assertRedirect();

    expect(Sale::query()->count())->toBe(1);
});

test('checkout creates multi-item sale correctly', function () {
    $admin    = userWithRole('Admin');
    $p1       = makeProduct('MULTI01', 100, 10.0);
    $p2       = makeProduct('MULTI02', 100, 20.0);
    $customer = makeCustomer();

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items' => [
            ['barcode' => 'MULTI01', 'quantity' => 2],
            ['barcode' => 'MULTI02', 'quantity' => 1],
        ],
        'customer_id' => $customer->id,
    ]);

    expect(SaleItem::query()->count())->toBe(2);
    $sale = Sale::query()->latest()->first();
    expect((float) $sale->total_amount)->toBe(40.0); // 2×10 + 1×20
});

// ─── Checkout: error cases ────────────────────────────────────────────────────

test('checkout with unknown barcode aborts with 422', function () {
    $admin    = userWithRole('Admin');
    $customer = makeCustomer();

    $response = $this->actingAs($admin)->post(route('pos.checkout'), [
        'items'       => [['barcode' => 'GHOST999', 'quantity' => 1]],
        'customer_id' => $customer->id,
    ]);

    $response->assertStatus(422);
    expect(Sale::query()->count())->toBe(0);
});

test('checkout with insufficient stock aborts with 422', function () {
    $admin    = userWithRole('Admin');
    $product  = makeProduct('LOW001', 3, 5.0); // only 3 in stock
    $customer = makeCustomer();

    $response = $this->actingAs($admin)->post(route('pos.checkout'), [
        'items'       => [['barcode' => 'LOW001', 'quantity' => 10]],
        'customer_id' => $customer->id,
    ]);

    $response->assertStatus(422);
    expect($product->fresh()->stock_quantity)->toBe(3); // untouched
});

test('checkout fails validation when items array is empty', function () {
    $admin    = userWithRole('Admin');
    $customer = makeCustomer();

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items'       => [],
        'customer_id' => $customer->id,
    ])->assertSessionHasErrors('items');
});

test('checkout fails validation when items is missing', function () {
    $admin    = userWithRole('Admin');
    $customer = makeCustomer();

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'customer_id' => $customer->id,
    ])->assertSessionHasErrors('items');
});

test('checkout fails when no customer exists and none specified', function () {
    $admin   = userWithRole('Admin');
    makeProduct('NOCUST', 10, 5.0);
    // No customer is created

    $this->actingAs($admin)->post(route('pos.checkout'), [
        'items' => [['barcode' => 'NOCUST', 'quantity' => 1]],
    ])->assertSessionHasErrors('customer_id');
});

// ─── Checkout: permission enforcement ────────────────────────────────────────

test('user without checkout.create permission cannot checkout', function () {
    roleWith('Viewer', ['pos.view']); // no checkout.create
    $viewer   = userWithRole('Viewer');
    $product  = makeProduct('PERM01', 10, 5.0);
    $customer = makeCustomer();

    $this->actingAs($viewer)->post(route('pos.checkout'), [
        'items'       => [['barcode' => 'PERM01', 'quantity' => 1]],
        'customer_id' => $customer->id,
    ])->assertStatus(403);

    expect(Sale::query()->count())->toBe(0);
});
