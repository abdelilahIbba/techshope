<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;

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

    Route::get('/pos/dashboard', [PosController::class, 'dashboard'])->name('pos.dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/stock', [PosController::class, 'stock'])->name('pos.stock');
    Route::post('/pos/stock/add-product', [PosController::class, 'addProduct'])->name('pos.stock.add-product');
    Route::put('/pos/stock/edit-product/{product}', [PosController::class, 'editProduct'])->name('pos.stock.edit-product');
    Route::delete('/pos/stock/delete-product/{product}', [PosController::class, 'deleteProduct'])->name('pos.stock.delete-product');
    Route::post('/pos/stock/adjust-stock/{product}', [PosController::class, 'adjustStock'])->name('pos.stock.adjust-stock');
    Route::post('/pos/stock/add-supplier', [PosController::class, 'addSupplier'])->name('pos.stock.add-supplier');
    Route::put('/pos/stock/edit-supplier/{supplier}', [PosController::class, 'editSupplier'])->name('pos.stock.edit-supplier');
    Route::delete('/pos/stock/delete-supplier/{supplier}', [PosController::class, 'deleteSupplier'])->name('pos.stock.delete-supplier');
    Route::post('/pos/stock/add-category', [PosController::class, 'addCategory'])->name('pos.stock.add-category');
    Route::put('/pos/stock/edit-category/{category}', [PosController::class, 'editCategory'])->name('pos.stock.edit-category');
    Route::delete('/pos/stock/delete-category/{category}', [PosController::class, 'deleteCategory'])->name('pos.stock.delete-category');
    Route::get('/pos/notifications', [PosController::class, 'notifications'])->name('pos.notifications');
    Route::get('/pos/settings', [PosController::class, 'settings'])->name('pos.settings');
    Route::post('/pos/settings/user/add', [PosController::class, 'addUser'])->name('pos.settings.user.add');
    Route::post('/pos/settings/user/edit/{user}', [PosController::class, 'editUser'])->name('pos.settings.user.edit');
    Route::delete('/pos/settings/user/delete/{user}', [PosController::class, 'deleteUser'])->name('pos.settings.user.delete');
    Route::post('/pos/settings/system/update', [PosController::class, 'updateSystemSettings'])->name('pos.settings.system.update');
    Route::get('/pos/scan/{barcode}', [PosController::class, 'scan'])->name('pos.scan');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{receipt}/pdf', [PosController::class, 'receiptPdf'])->name('pos.receipt.pdf');
});
