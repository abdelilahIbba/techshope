<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PosController extends Controller
{
    public function dashboard(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
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

        $categories = Category::query()->orderBy('name')->get();

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
        return view('pos.settings', compact('users'));
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