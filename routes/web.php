<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PosController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('pos.dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('/pos/dashboard', [PosController::class, 'dashboard'])->name('pos.dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/stock', [PosController::class, 'stock'])->name('pos.stock');
    Route::post('/pos/stock/add-product', [PosController::class, 'addProduct'])->name('pos.stock.add-product');
    Route::post('/pos/stock/add-supplier', [PosController::class, 'addSupplier'])->name('pos.stock.add-supplier');
    Route::get('/pos/notifications', [PosController::class, 'notifications'])->name('pos.notifications');
    Route::get('/pos/settings', [PosController::class, 'settings'])->name('pos.settings');
    Route::get('/pos/scan/{barcode}', [PosController::class, 'scan'])->name('pos.scan');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{receipt}/pdf', [PosController::class, 'receiptPdf'])->name('pos.receipt.pdf');
});
