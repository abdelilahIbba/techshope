<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PosController extends Controller
{
    public function dashboard(Request $request): View
    {
        $from = $request->input('from', now()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $sales = Sale::query()
            ->with('customer')
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderBy('date', 'desc')
            ->get();

        $totalRevenue = $sales->sum('total_amount');
        $salesCount = $sales->count();

        $productsSold = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select(
                'products.name as product_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                $item->seller_name = 'Commercial';
                return $item;
            });

        $productsCount = $productsSold->count();
        $lowStockCount = Product::query()->where('stock_quantity', '<=', 10)->count();

        return view('pos.dashboard', [
            'sales' => $sales,
            'totalRevenue' => $totalRevenue,
            'salesCount' => $salesCount,
            'productsCount' => $productsCount,
            'lowStockCount' => $lowStockCount,
            'productsSold' => $productsSold,
        ]);
    }

    public function index(): View
    {
        $categories = Category::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->with([
                'products' => function ($query) {
                    $query
                        ->select('id', 'name', 'barcode', 'sale_price', 'stock_quantity', 'category_id')
                        ->orderBy('name');
                },
            ])
            ->get();

        return view('pos.index', [
            'categories' => $categories,
        ]);
    }

    public function stock(): View
    {
        $products = Product::query()
            ->with(['category:id,name', 'supplier:id,name'])
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('pos.stock', [
            'products' => $products,
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    public function addProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'string', 'unique:products,barcode'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['unit'] = 'unité';

        Product::query()->create($validated);

        return redirect()->route('pos.stock')->with('success', 'Produit ajouté avec succès!');
    }

    public function addSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        Supplier::query()->create($validated);

        return redirect()->route('pos.stock')->with('success', 'Fournisseur ajouté avec succès!');
    }

    public function editProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'string', 'unique:products,barcode,' . $product->id],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
        ]);

        $product->update($validated);

        return redirect()->route('pos.stock')->with('success', 'Produit modifié avec succès!');
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();

        return redirect()->route('pos.stock')->with('success', 'Produit supprimé avec succès!');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'adjustment_type' => ['required', 'in:add,remove,set'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $currentStock = $product->stock_quantity;
        $newStock = $currentStock;

        switch ($validated['adjustment_type']) {
            case 'add':
                $newStock = $currentStock + $validated['quantity'];
                break;
            case 'remove':
                $newStock = max(0, $currentStock - $validated['quantity']);
                break;
            case 'set':
                $newStock = $validated['quantity'];
                break;
        }

        $product->update(['stock_quantity' => $newStock]);

        return redirect()->route('pos.stock')->with('success', 'Stock ajusté avec succès! Nouveau stock: ' . $newStock . ' unités');
    }

    public function editSupplier(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return redirect()->route('pos.stock')->with('success', 'Fournisseur modifié avec succès!');
    }

    public function deleteSupplier(Supplier $supplier)
    {
        $productsCount = $supplier->products()->count();

        if ($productsCount > 0) {
            return redirect()->route('pos.stock')->with('error', 'Impossible de supprimer ce fournisseur car il est lié à ' . $productsCount . ' produit(s).');
        }

        $supplier->delete();

        return redirect()->route('pos.stock')->with('success', 'Fournisseur supprimé avec succès!');
    }

    public function addCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::query()->create($validated);

        return redirect()->route('pos.stock')->with('success', 'Catégorie ajoutée avec succès!');
    }

    public function editCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
        ]);

        $category->update($validated);

        return redirect()->route('pos.stock')->with('success', 'Catégorie modifiée avec succès!');
    }

    public function deleteCategory(Category $category)
    {
        $productsCount = $category->products()->count();

        if ($productsCount > 0) {
            return redirect()->route('pos.stock')->with('error', 'Impossible de supprimer cette catégorie car elle contient ' . $productsCount . ' produit(s).');
        }

        $category->delete();

        return redirect()->route('pos.stock')->with('success', 'Catégorie supprimée avec succès!');
    }

    public function notifications(): View
    {
        $lowStockProducts = Product::query()
            ->with('category:id,name')
            ->where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity')
            ->get();

        return view('pos.notifications', [
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    public function settings(): View
    {
        $users = User::orderBy('name')->get();
        $printers = $this->getAvailablePrinters();
        
        $settings = [
            'printer_name' => Setting::get('printer_name', 'Ticket POS Printer (USB)'),
            'ticket_format' => Setting::get('ticket_format', '80mm thermal'),
            'auto_print' => Setting::get('auto_print', 'true'),
            'scanner_mode' => Setting::get('scanner_mode', 'Scan direct vers champ barcode'),
            'scanner_beep' => Setting::get('scanner_beep', 'true'),
            'currency' => Setting::get('currency', 'DZD'),
            'low_stock_threshold' => Setting::get('low_stock_threshold', '10'),
            'default_customer' => Setting::get('default_customer', 'Zbon عام (Cash)'),
            'notification_email' => Setting::get('notification_email', 'admin@droguerie.com'),
            'stock_check_frequency' => Setting::get('stock_check_frequency', 'Chaque heure'),
        ];
        
        return view('pos.settings', compact('users', 'settings', 'printers'));
    }

    private function getAvailablePrinters(): array
    {
        $printers = [];
        
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Use PowerShell to get printers
                $command = 'powershell -Command "Get-Printer | Select-Object -ExpandProperty Name"';
                $output = shell_exec($command);
                
                if ($output) {
                    $printersList = explode("\n", trim($output));
                    foreach ($printersList as $printer) {
                        $printer = trim($printer);
                        if (!empty($printer)) {
                            $printers[] = $printer;
                        }
                    }
                }
            } else {
                // For Linux/Unix systems
                $output = shell_exec('lpstat -p -d 2>/dev/null | grep "^printer" | cut -d" " -f2');
                if ($output) {
                    $printersList = explode("\n", trim($output));
                    foreach ($printersList as $printer) {
                        $printer = trim($printer);
                        if (!empty($printer)) {
                            $printers[] = $printer;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // If we can't detect printers, return empty array
            $printers = [];
        }
        
        return $printers;
    }

    public function addUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4',
            'role' => 'required|in:admin,cashier,commercial',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('pos.settings')->with('success', 'Utilisateur ajouté avec succès');
    }

    public function editUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:4',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        return redirect()->route('pos.settings')->with('success', 'Utilisateur modifié avec succès');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('pos.settings')->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        $user->delete();

        return redirect()->route('pos.settings')->with('success', 'Utilisateur supprimé avec succès');
    }

    public function updateSystemSettings(Request $request)
    {
        $settings = $request->validate([
            'printer_name' => 'nullable|string',
            'ticket_format' => 'nullable|string',  
            'auto_print' => 'nullable|string',
            'scanner_mode' => 'nullable|string',
            'scanner_beep' => 'nullable|string',
            'currency' => 'nullable|string',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'default_customer' => 'nullable|string',
            'notification_email' => 'nullable|email',
            'stock_check_frequency' => 'nullable|string',
        ]);

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                $type = is_numeric($value) ? 'integer' : 'string';
                Setting::set($key, $value, $type);
            }
        }

        return redirect()->route('pos.settings')->with('success', 'Paramètres mis à jour avec succès');
    }

    public function scan(string $barcode): JsonResponse
    {
        $product = Product::query()
            ->where('barcode', $barcode)
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Product not found for this barcode.',
            ], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'category_id' => $product->category_id,
            'price' => (float) ($product->sale_price ?? $product->price ?? 0),
            'stock_quantity' => $product->stock_quantity,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.barcode' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customerId = $validated['customer_id'] ?? Customer::query()->value('id');

        if (! $customerId) {
            return back()->withErrors([
                'customer_id' => 'No customer found. Create at least one customer before checkout.',
            ]);
        }

        $result = DB::transaction(function () use ($validated, $customerId) {
            $resolvedItems = [];
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::query()
                    ->where('barcode', $item['barcode'])
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    abort(422, 'Product not found for barcode: '.$item['barcode']);
                }

                $quantity = (int) $item['quantity'];

                if ($product->stock_quantity < $quantity) {
                    abort(422, 'Insufficient stock for: '.$product->name);
                }

                $unitPrice = (float) ($product->sale_price ?? $product->price ?? 0);
                $subtotal = $unitPrice * $quantity;

                $product->decrement('stock_quantity', $quantity);

                $resolvedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                $totalAmount += $subtotal;
            }

            $sale = Sale::query()->create([
                'customer_id' => $customerId,
                'date' => now(),
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'] ?? 'cash',
            ]);

            foreach ($resolvedItems as $row) {
                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $row['product']->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);
            }

            $receipt = Receipt::query()->create([
                'sale_id' => $sale->id,
                'receipt_number' => 'RCP-'.now()->format('Ymd').'-'.$sale->id,
                'printed_at' => now(),
            ]);

            return [$sale->id, $receipt->id];
        });

        return redirect()->route('pos.receipt.pdf', ['receipt' => $result[1]]);
    }

    public function receiptPdf(Receipt $receipt)
    {
        $receipt->load('sale.items.product');

        $generator = new BarcodeGeneratorSVG();
        $barcodeImages = [];

        foreach ($receipt->sale->items as $item) {
            $barcodeImages[$item->product_id] = base64_encode(
                $generator->getBarcode($item->product->barcode, $generator::TYPE_CODE_128)
            );
        }

        $pdf = Pdf::loadView('pos.receipt-pdf', [
            'receipt' => $receipt,
            'barcodeImages' => $barcodeImages,
        ])->setPaper('a5', 'portrait');

        return $pdf->download('receipt-'.$receipt->receipt_number.'.pdf');
    }
}