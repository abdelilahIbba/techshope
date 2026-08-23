<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;

// ─── Cancellation <-> Dashboard consistency ──────────────────────────────────
//
// Sales are created with status='completed'. Cancelling one (fully via
// cancelSale, or item-by-item via cancelSaleItem) must immediately stop it
// from counting toward CA/revenue, sales count, and the transaction list —
// everywhere that number is shown (dashboard, printable dashboard).

beforeEach(function () {
    activeLicense();
});

function makeCompletedSale(string $barcode, float $price, float $qty = 1): Sale
{
    $category = Category::query()->firstOrCreate(['name' => 'Cancellation Test Category'], ['description' => '']);
    $product = Product::query()->create([
        'name' => "Product {$barcode}",
        'barcode' => $barcode,
        'category_id' => $category->id,
        'stock_quantity' => 50,
        'purchase_price' => $price * 0.6,
        'sale_price' => $price,
        'unit' => 'unité',
    ]);
    $variant = ProductVariant::query()->create([
        'product_id' => $product->id,
        'barcode' => $barcode,
        'stock_quantity' => 50,
        'is_default' => true,
    ]);
    $customer = Customer::query()->create(['name' => 'Client Test', 'phone' => '06' . random_int(10000000, 99999999)]);

    $sale = Sale::query()->create([
        'customer_id' => $customer->id,
        'date' => now(),
        'total_amount' => $price * $qty,
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);

    SaleItem::query()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => $qty,
        'unit_price' => $price,
        'subtotal' => $price * $qty,
    ]);

    return $sale->fresh();
}

test('a fully cancelled sale disappears from dashboard revenue, sales count and the transaction list', function () {
    $admin = userWithRole('Admin');
    $kept = makeCompletedSale('CNCL-KEEP-01', 200.0);
    $toCancel = makeCompletedSale('CNCL-CANC-01', 300.0);

    $before = $this->actingAs($admin)->get(route('pos.dashboard'));
    expect((float) $before->viewData('totalRevenue'))->toBe(500.0);
    expect($before->viewData('salesCount'))->toBe(2);

    $this->actingAs($admin)->post(route('pos.sales.cancel', $toCancel), [
        'reason' => 'client_request',
        'refund_amount' => 300.0,
        'refund_method' => 'cash',
    ])->assertRedirect();

    expect($toCancel->fresh()->status)->toBe('cancelled');

    $after = $this->actingAs($admin)->get(route('pos.dashboard'));
    expect((float) $after->viewData('totalRevenue'))->toBe(200.0);
    expect($after->viewData('salesCount'))->toBe(1);

    $saleIds = $after->viewData('sales')->pluck('id');
    expect($saleIds)->toContain($kept->id);
    expect($saleIds)->not->toContain($toCancel->id);
});

test('a fully cancelled sale is excluded from the printable dashboard totals', function () {
    $admin = userWithRole('Admin');
    $kept = makeCompletedSale('CNCL-KEEP-02', 150.0);
    $toCancel = makeCompletedSale('CNCL-CANC-02', 250.0);

    $this->actingAs($admin)->post(route('pos.sales.cancel', $toCancel), [
        'reason' => 'error',
        'refund_amount' => 250.0,
        'refund_method' => 'cash',
    ]);

    $print = $this->actingAs($admin)->get(route('pos.dashboard.print'));
    expect((float) $print->viewData('totalRevenue'))->toBe(150.0);
    expect($print->viewData('salesCount'))->toBe(1);
});

test('cancelling one item reduces the sale total and dashboard revenue without dropping the whole sale', function () {
    $admin = userWithRole('Admin');
    $sale = makeCompletedSale('CNCL-ITEM-01', 100.0, 3); // total 300
    $item = $sale->items()->first();

    $this->actingAs($admin)->post(route('pos.sales.cancel-item', [$sale, $item]), [
        'qty_to_cancel' => 1,
        'reason' => 'client_request',
        'refund_method' => 'cash',
    ])->assertRedirect();

    $sale->refresh();
    expect($sale->status)->toBe('completed'); // 2 of 3 units remain — sale stays active
    expect((float) $sale->total_amount)->toBe(200.0);

    $dashboard = $this->actingAs($admin)->get(route('pos.dashboard'));
    expect((float) $dashboard->viewData('totalRevenue'))->toBe(200.0);
    expect($dashboard->viewData('salesCount'))->toBe(1);
});

test('cancelling the last remaining item cancels the whole sale and removes it from the dashboard', function () {
    $admin = userWithRole('Admin');
    $sale = makeCompletedSale('CNCL-ITEM-02', 80.0, 1);
    $item = $sale->items()->first();

    $this->actingAs($admin)->post(route('pos.sales.cancel-item', [$sale, $item]), [
        'qty_to_cancel' => 1,
        'reason' => 'return',
        'refund_method' => 'cash',
    ]);

    expect($sale->fresh()->status)->toBe('cancelled');

    $dashboard = $this->actingAs($admin)->get(route('pos.dashboard'));
    expect((float) $dashboard->viewData('totalRevenue'))->toBe(0.0);
    expect($dashboard->viewData('salesCount'))->toBe(0);
});

test('cancelling a sale restores stock at the variant level', function () {
    $admin = userWithRole('Admin');
    $sale = makeCompletedSale('CNCL-STOCK-01', 50.0, 5);
    $variant = ProductVariant::query()->where('barcode', 'CNCL-STOCK-01')->first();

    // Stock wasn't decremented by makeCompletedSale (it bypasses checkout),
    // so simulate the pre-sale stock state directly for a clean before/after.
    $variant->update(['stock_quantity' => 45]);

    $this->actingAs($admin)->post(route('pos.sales.cancel', $sale), [
        'reason' => 'return',
        'refund_amount' => 250.0,
        'refund_method' => 'cash',
    ]);

    expect((float) $variant->fresh()->stock_quantity)->toBe(50.0);
});
