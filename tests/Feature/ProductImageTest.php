<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ─── Product image CRUD (upload or URL) ──────────────────────────────────────

beforeEach(function () {
    activeLicense();

    ensurePermission('stock.view',   'Stock', 'View');
    ensurePermission('stock.create', 'Stock', 'Create');
    ensurePermission('stock.edit',   'Stock', 'Edit');
    ensurePermission('stock.delete', 'Stock', 'Delete');

    roleWith('Editor', ['stock.view', 'stock.create', 'stock.edit', 'stock.delete']);

    Storage::fake('public');
});

function fakeImage(string $name = 'photo.jpg'): UploadedFile
{
    // ->image() needs the GD extension (not available in every PHP CLI);
    // ->create() with an explicit mime satisfies the 'image' validation
    // rule without requiring real image binary data.
    return UploadedFile::fake()->create($name, 50, 'image/jpeg');
}

function imageTestCategory(): Category
{
    return Category::query()->firstOrCreate(['name' => 'Image Test Category'], ['description' => '']);
}

// ─── Create ─────────────────────────────────────────────────────────────────────

test('admin can add a product with an uploaded image', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $file     = fakeImage('cover.jpg');

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Photo Upload Product',
        'barcode'        => 'IMG001',
        'category_id'    => $category->id,
        'stock_quantity' => 5,
        'purchase_price' => 10,
        'sale_price'     => 15,
        'image_file'     => $file,
    ])->assertRedirect(route('pos.stock'));

    $product = Product::query()->where('barcode', 'IMG001')->first();
    expect($product)->not->toBeNull();
    expect($product->image)->not->toBeNull();
    expect($product->image)->toStartWith('products/');
    Storage::disk('public')->assertExists($product->image);
    expect($product->image_url)->toBe(Storage::disk('public')->url($product->image));
});

test('admin can add a product with an image URL', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $url      = 'https://example.com/photos/phone.jpg';

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Photo URL Product',
        'barcode'        => 'IMG002',
        'category_id'    => $category->id,
        'stock_quantity' => 5,
        'purchase_price' => 10,
        'sale_price'     => 15,
        'image_url'      => $url,
    ])->assertRedirect(route('pos.stock'));

    $product = Product::query()->where('barcode', 'IMG002')->first();
    expect($product->image)->toBe($url);
    expect($product->image_url)->toBe($url); // external URLs pass through unchanged
});

test('admin can add a product without any image', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'No Photo Product',
        'barcode'        => 'IMG003',
        'category_id'    => $category->id,
        'stock_quantity' => 5,
        'purchase_price' => 10,
        'sale_price'     => 15,
    ])->assertRedirect(route('pos.stock'));

    $product = Product::query()->where('barcode', 'IMG003')->first();
    expect($product->image)->toBeNull();
    expect($product->image_url)->toBeNull();
});

test('uploading a non-image file is rejected', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $file     = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Bad File Product',
        'barcode'        => 'IMG004',
        'category_id'    => $category->id,
        'stock_quantity' => 5,
        'purchase_price' => 10,
        'sale_price'     => 15,
        'image_file'     => $file,
    ])->assertSessionHasErrors('image_file');

    expect(Product::query()->where('barcode', 'IMG004')->exists())->toBeFalse();
});

test('an invalid image URL is rejected', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();

    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Bad URL Product',
        'barcode'        => 'IMG005',
        'category_id'    => $category->id,
        'stock_quantity' => 5,
        'purchase_price' => 10,
        'sale_price'     => 15,
        'image_url'      => 'not-a-url',
    ])->assertSessionHasErrors('image_url');

    expect(Product::query()->where('barcode', 'IMG005')->exists())->toBeFalse();
});

test('a well-formed URL that does not point at an image file is rejected', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();

    // A real, valid URL — just not one that resolves to an image asset.
    // This is exactly what silently produced a broken thumbnail before the
    // regex guard was added: the URL passed the plain 'url' rule fine.
    $this->actingAs($admin)->post(route('pos.stock.add-product'), [
        'name'           => 'Webpage URL Product',
        'barcode'        => 'IMG006',
        'category_id'    => $category->id,
        'stock_quantity' => 5,
        'purchase_price' => 10,
        'sale_price'     => 15,
        'image_url'      => 'https://example.com/reviews/some-product',
    ])->assertSessionHasErrors('image_url');

    expect(Product::query()->where('barcode', 'IMG006')->exists())->toBeFalse();
});

// ─── Update ─────────────────────────────────────────────────────────────────────

test('admin can add an image to a product that had none', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $product  = Product::query()->create([
        'name' => 'Plain', 'barcode' => 'IMG010', 'category_id' => $category->id,
        'stock_quantity' => 5, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);
    $file = fakeImage('new.jpg');

    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name' => 'Plain', 'barcode' => 'IMG010', 'category_id' => $category->id,
        'purchase_price' => 10, 'sale_price' => 15, 'image_file' => $file,
    ])->assertRedirect(route('pos.stock'));

    $product->refresh();
    expect($product->image)->not->toBeNull();
    Storage::disk('public')->assertExists($product->image);
});

test('replacing an uploaded image deletes the old file and stores the new one', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $oldPath  = fakeImage('old.jpg')->store('products', 'public');
    $product  = Product::query()->create([
        'name' => 'Replace Me', 'barcode' => 'IMG011', 'category_id' => $category->id,
        'image' => $oldPath, 'stock_quantity' => 5, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);
    Storage::disk('public')->assertExists($oldPath);

    $newFile = fakeImage('new.jpg');
    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name' => 'Replace Me', 'barcode' => 'IMG011', 'category_id' => $category->id,
        'purchase_price' => 10, 'sale_price' => 15, 'image_file' => $newFile,
    ]);

    $product->refresh();
    expect($product->image)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($product->image);
});

test('switching from an uploaded image to a URL deletes the old local file', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $oldPath  = fakeImage('old.jpg')->store('products', 'public');
    $product  = Product::query()->create([
        'name' => 'Switch Me', 'barcode' => 'IMG012', 'category_id' => $category->id,
        'image' => $oldPath, 'stock_quantity' => 5, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);

    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name' => 'Switch Me', 'barcode' => 'IMG012', 'category_id' => $category->id,
        'purchase_price' => 10, 'sale_price' => 15, 'image_url' => 'https://example.com/new.jpg',
    ]);

    $product->refresh();
    expect($product->image)->toBe('https://example.com/new.jpg');
    Storage::disk('public')->assertMissing($oldPath);
});

test('editing a product without touching image fields leaves the existing image unchanged', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $path     = fakeImage('keep.jpg')->store('products', 'public');
    $product  = Product::query()->create([
        'name' => 'Keep Image', 'barcode' => 'IMG013', 'category_id' => $category->id,
        'image' => $path, 'stock_quantity' => 5, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);

    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name' => 'Keep Image Renamed', 'barcode' => 'IMG013', 'category_id' => $category->id,
        'purchase_price' => 12, 'sale_price' => 18,
    ]);

    $product->refresh();
    expect($product->image)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('admin can remove a product image, deleting the stored file', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $path     = fakeImage('remove-me.jpg')->store('products', 'public');
    $product  = Product::query()->create([
        'name' => 'Remove Image', 'barcode' => 'IMG014', 'category_id' => $category->id,
        'image' => $path, 'stock_quantity' => 5, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);

    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name' => 'Remove Image', 'barcode' => 'IMG014', 'category_id' => $category->id,
        'purchase_price' => 10, 'sale_price' => 15, 'remove_image' => 1,
    ]);

    $product->refresh();
    expect($product->image)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('removing a URL-based image does not attempt to delete any local file', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $product  = Product::query()->create([
        'name' => 'Remove URL Image', 'barcode' => 'IMG015', 'category_id' => $category->id,
        'image' => 'https://example.com/photo.jpg', 'stock_quantity' => 5,
        'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);

    $this->actingAs($admin)->put(route('pos.stock.edit-product', $product), [
        'name' => 'Remove URL Image', 'barcode' => 'IMG015', 'category_id' => $category->id,
        'purchase_price' => 10, 'sale_price' => 15, 'remove_image' => 1,
    ])->assertRedirect(route('pos.stock'));

    expect($product->fresh()->image)->toBeNull();
});

// ─── Delete ─────────────────────────────────────────────────────────────────────

test('deleting a product deletes its stored uploaded image file', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $path     = fakeImage('gone.jpg')->store('products', 'public');
    $product  = Product::query()->create([
        'name' => 'To Delete', 'barcode' => 'IMG020', 'category_id' => $category->id,
        'image' => $path, 'stock_quantity' => 5, 'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);

    $this->actingAs($admin)->delete(route('pos.stock.delete-product', $product))
        ->assertRedirect(route('pos.stock'));

    Storage::disk('public')->assertMissing($path);
    expect(Product::query()->find($product->id))->toBeNull();
});

test('deleting a product with a URL-based image does not error', function () {
    $admin    = userWithRole('Admin');
    $category = imageTestCategory();
    $product  = Product::query()->create([
        'name' => 'To Delete URL', 'barcode' => 'IMG021', 'category_id' => $category->id,
        'image' => 'https://example.com/photo.jpg', 'stock_quantity' => 5,
        'purchase_price' => 10, 'sale_price' => 15, 'unit' => 'unité',
    ]);

    $this->actingAs($admin)->delete(route('pos.stock.delete-product', $product))
        ->assertRedirect(route('pos.stock'));

    expect(Product::query()->find($product->id))->toBeNull();
});
