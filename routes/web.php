<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
use App\Http\Controllers\LicenseController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;

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

    // Verify email notification endpoint used by auth/settings React pages.
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // ── POS routes — protected by license check for non-admin users ──
    Route::middleware(['check.license'])->group(function () {
        Route::get('/pos/dashboard', [PosController::class, 'dashboard'])->middleware('permission:dashboard.view')->name('pos.dashboard');
        Route::get('/pos/dashboard/print', [PosController::class, 'dashboardPrint'])->middleware('permission:dashboard.view')->name('pos.dashboard.print');
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/stock', [PosController::class, 'stock'])->middleware('permission:stock.view')->name('pos.stock');
        // [MODIV] Route AJAX pour sauvegarder une couleur personnalisée créée par l'utilisateur.
        // تُستخدم هذه الـ Route لحفظ الألوان المخصصة التي ينشئها المستخدم من نافذة
        // "Créer une couleur" في صفحة المخزون. تُرجع JSON مع success/color/message.
        Route::post('/pos/stock/save-color', [PosController::class, 'saveColor'])->middleware('permission:stock.create')->name('pos.stock.save-color');
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
        Route::get('/pos/settings/printer', [PosController::class, 'settingsPrinter'])->middleware('permission:settings.view')->name('pos.settings.printer');
        Route::get('/pos/settings/barcode', [PosController::class, 'settingsBarcode'])->middleware('permission:settings.view')->name('pos.settings.barcode');
        Route::get('/pos/settings/ticket-format', [PosController::class, 'settingsTicketFormat'])->middleware('permission:settings.view')->name('pos.settings.ticket-format');
        Route::get('/pos/settings/auto-print', [PosController::class, 'settingsAutoPrint'])->middleware('permission:settings.view')->name('pos.settings.auto-print');
        Route::get('/pos/settings/input-mode', [PosController::class, 'settingsInputMode'])->middleware('permission:settings.view')->name('pos.settings.input-mode');
        Route::get('/pos/settings/sound', [PosController::class, 'settingsSound'])->middleware('permission:settings.view')->name('pos.settings.sound');
        Route::get('/pos/settings/license', [PosController::class, 'settingsLicense'])->middleware('permission:settings.view')->name('pos.settings.license');
        Route::get('/pos/settings/stock', [PosController::class, 'settingsStock'])->middleware('permission:settings.view')->name('pos.settings.stock');
        Route::get('/pos/settings/users', [PosController::class, 'settingsUsers'])->middleware('permission:settings.view')->name('pos.settings.users');
        Route::get('/pos/settings/taxes', [PosController::class, 'settingsTaxes'])->middleware('permission:settings.view')->name('pos.settings.taxes');
        Route::post('/pos/settings/user/add', [PosController::class, 'addUser'])->middleware('permission:users.manage')->name('pos.settings.user.add');
        Route::post('/pos/settings/user/edit/{user}', [PosController::class, 'editUser'])->middleware('permission:users.manage')->name('pos.settings.user.edit');
        Route::delete('/pos/settings/user/delete/{user}', [PosController::class, 'deleteUser'])->middleware('permission:users.manage')->name('pos.settings.user.delete');
        Route::post('/pos/settings/user/{user}/assign-role', [PosController::class, 'assignUserRole'])->middleware('permission:users.manage')->name('pos.settings.user.assign-role');
        Route::post('/pos/settings/roles', [PosController::class, 'createRole'])->middleware('permission:roles.manage')->name('pos.settings.roles.create');
        Route::put('/pos/settings/roles/{role}', [PosController::class, 'updateRole'])->middleware('permission:roles.manage')->name('pos.settings.roles.update');
        Route::delete('/pos/settings/roles/{role}', [PosController::class, 'deleteRole'])->middleware('permission:roles.manage')->name('pos.settings.roles.delete');
        Route::post('/pos/settings/system/update', [PosController::class, 'updateSystemSettings'])->middleware('permission:settings.update')->name('pos.settings.system.update');
        Route::get('/pos/scan', [PosController::class, 'scan'])->middleware('permission:pos.view')->name('pos.scan');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->middleware('permission:checkout.create')->name('pos.checkout');
        Route::get('/pos/receipt/{receipt}/print', [PosController::class, 'receiptPrint'])->middleware('permission:receipt.export')->name('pos.receipt.print');
        Route::get('/pos/barcode-print', [PosController::class, 'barcodePrintPage'])->middleware('permission:barcode.print')->name('pos.barcode.print');
        Route::get('/pos/product/{product}/barcode.png', [PosController::class, 'productBarcodeImage'])->middleware('permission:barcode.print')->name('pos.product.barcode.png');
        
        // ── Rapports & Historique ──
        Route::get('/pos/reports', [PosController::class, 'reports'])->middleware('permission:reports.view')->name('pos.reports');
        Route::get('/pos/reports/sales-by-product', [PosController::class, 'salesByProduct'])->middleware('permission:reports.view')->name('pos.reports.sales-by-product');
        Route::get('/pos/reports/sales-by-cashier', [PosController::class, 'salesByCashier'])->middleware('permission:reports.view')->name('pos.reports.sales-by-cashier');
        Route::get('/pos/reports/cancellations', [PosController::class, 'cancellations'])->middleware('permission:reports.view')->name('pos.reports.cancellations');
        
        // ── Annulations ──
        Route::post('/pos/sales/{sale}/cancel', [PosController::class, 'cancelSale'])->middleware('permission:sale.cancel')->name('pos.sales.cancel');
        Route::post('/pos/sales/{sale}/cancel-item/{item}', [PosController::class, 'cancelSaleItem'])->middleware('permission:sale.cancel')->name('pos.sales.cancel-item');
        Route::post('/pos/sales/{sale}/exchange-item/{item}', [PosController::class, 'exchangeSaleItem'])->middleware('permission:sale.cancel')->name('pos.sales.exchange-item');
        Route::get('/pos/variants/options', [PosController::class, 'variantOptions'])->middleware('permission:pos.view')->name('pos.variants.options');
        Route::post('/pos/sales/{sale}/authorize-cancellation', [PosController::class, 'authorizeCancellation'])->middleware('permission:sale.cancel.authorize')->name('pos.sales.authorize-cancellation');
        
        // ── Dépenses ──
        Route::get('/pos/expenses', [PosController::class, 'expenses'])->middleware('permission:expenses.view')->name('pos.expenses');
        Route::post('/pos/expenses', [PosController::class, 'createExpense'])->middleware('permission:expenses.create')->name('pos.expenses.create');
        Route::put('/pos/expenses/{expense}', [PosController::class, 'updateExpense'])->middleware('permission:expenses.edit')->name('pos.expenses.update');
        Route::delete('/pos/expenses/{expense}', [PosController::class, 'deleteExpense'])->middleware('permission:expenses.delete')->name('pos.expenses.delete');
        Route::post('/pos/expenses/{expense}/approve', [PosController::class, 'approveExpense'])->middleware('permission:expenses.approve')->name('pos.expenses.approve');
        
        // ── Export ──
        Route::get('/pos/reports/export/sales-by-product', [PosController::class, 'exportSalesByProduct'])->middleware('permission:reports.export')->name('pos.reports.export.sales-by-product');
        Route::get('/pos/reports/export/sales-by-cashier', [PosController::class, 'exportSalesByCashier'])->middleware('permission:reports.export')->name('pos.reports.export.sales-by-cashier');
        Route::get('/pos/reports/export/expenses', [PosController::class, 'exportExpenses'])->middleware('permission:reports.export')->name('pos.reports.export.expenses');
    });
});

require __DIR__.'/settings.php';
