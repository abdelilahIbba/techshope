<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
use App\Http\Controllers\LicenseController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('pos.dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('pos.dashboard');
    })->name('dashboard');

    // ── License activation (no license check here — it IS the activation page) ──
    Route::get('/license/activate', [LicenseController::class, 'activate'])->name('license.activate');
    Route::post('/license/activate', [LicenseController::class, 'store'])->name('license.store');

    // ── License key generation (Admin only, AJAX) ──
    Route::post('/license/generate', [LicenseController::class, 'generate'])->name('license.generate');

    // ── POS routes — protected by license check for non-admin users ──
    Route::middleware(['check.license'])->group(function () {
        Route::get('/pos/dashboard', [PosController::class, 'dashboard'])->middleware('permission:dashboard.view')->name('pos.dashboard');
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/stock', [PosController::class, 'stock'])->middleware('permission:stock.view')->name('pos.stock');
        Route::post('/pos/stock/add-product', [PosController::class, 'addProduct'])->middleware('permission:stock.create')->name('pos.stock.add-product');
        Route::put('/pos/stock/edit-product/{product}', [PosController::class, 'editProduct'])->middleware('permission:stock.edit')->name('pos.stock.edit-product');
        Route::delete('/pos/stock/delete-product/{product}', [PosController::class, 'deleteProduct'])->middleware('permission:stock.delete')->name('pos.stock.delete-product');
        Route::post('/pos/stock/adjust-stock/{product}', [PosController::class, 'adjustStock'])->middleware('permission:stock.adjust')->name('pos.stock.adjust-stock');
        Route::post('/pos/stock/add-supplier', [PosController::class, 'addSupplier'])->middleware('permission:supplier.manage')->name('pos.stock.add-supplier');
        Route::put('/pos/stock/edit-supplier/{supplier}', [PosController::class, 'editSupplier'])->middleware('permission:supplier.manage')->name('pos.stock.edit-supplier');
        Route::delete('/pos/stock/delete-supplier/{supplier}', [PosController::class, 'deleteSupplier'])->middleware('permission:supplier.manage')->name('pos.stock.delete-supplier');
        Route::post('/pos/stock/add-category', [PosController::class, 'addCategory'])->middleware('permission:category.manage')->name('pos.stock.add-category');
        Route::put('/pos/stock/edit-category/{category}', [PosController::class, 'editCategory'])->middleware('permission:category.manage')->name('pos.stock.edit-category');
        Route::delete('/pos/stock/delete-category/{category}', [PosController::class, 'deleteCategory'])->middleware('permission:category.manage')->name('pos.stock.delete-category');
        Route::get('/pos/notifications', [PosController::class, 'notifications'])->middleware('permission:notifications.view')->name('pos.notifications');
        Route::get('/pos/settings', [PosController::class, 'settings'])->middleware('permission:settings.view')->name('pos.settings');
        Route::post('/pos/settings/user/add', [PosController::class, 'addUser'])->middleware('permission:users.manage')->name('pos.settings.user.add');
        Route::post('/pos/settings/user/edit/{user}', [PosController::class, 'editUser'])->middleware('permission:users.manage')->name('pos.settings.user.edit');
        Route::delete('/pos/settings/user/delete/{user}', [PosController::class, 'deleteUser'])->middleware('permission:users.manage')->name('pos.settings.user.delete');
        Route::post('/pos/settings/user/{user}/assign-role', [PosController::class, 'assignUserRole'])->middleware('permission:users.manage')->name('pos.settings.user.assign-role');
        Route::post('/pos/settings/roles', [PosController::class, 'createRole'])->middleware('permission:roles.manage')->name('pos.settings.roles.create');
        Route::put('/pos/settings/roles/{role}', [PosController::class, 'updateRole'])->middleware('permission:roles.manage')->name('pos.settings.roles.update');
        Route::delete('/pos/settings/roles/{role}', [PosController::class, 'deleteRole'])->middleware('permission:roles.manage')->name('pos.settings.roles.delete');
        Route::post('/pos/settings/system/update', [PosController::class, 'updateSystemSettings'])->middleware('permission:settings.update')->name('pos.settings.system.update');
        Route::get('/pos/scan/{barcode}', [PosController::class, 'scan'])->middleware('permission:pos.view')->name('pos.scan');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->middleware('permission:checkout.create')->name('pos.checkout');
        Route::get('/pos/receipt/{receipt}/print', [PosController::class, 'receiptPrint'])->middleware('permission:receipt.export')->name('pos.receipt.print');
        Route::get('/pos/barcode-print', [PosController::class, 'barcodePrintPage'])->middleware('permission:barcode.print')->name('pos.barcode.print');
        Route::get('/pos/product/{product}/barcode.png', [PosController::class, 'productBarcodeImage'])->middleware('permission:barcode.print')->name('pos.product.barcode.png');
    });
});
