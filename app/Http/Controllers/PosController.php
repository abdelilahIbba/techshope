<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExternalOrder;
use App\Models\License;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Receipt;
use App\Models\Sale;
use App\Models\SaleCancellation;
use App\Models\SaleExchange;
use App\Models\SaleItem;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\VariantStockService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PosController extends Controller
{
    /** A product image_url must point directly at an image file, not a webpage. */
    private const IMAGE_URL_REGEX = '/\.(jpe?g|png|gif|webp|svg|avif|bmp)(\?.*)?$/i';

    public function __construct(private readonly VariantStockService $variantStock)
    {
    }
    private function permissionCatalog(): array
    {
        return [
            ['key' => 'dashboard.view', 'label' => 'Voir Dashboard', 'page' => 'Dashboard', 'action' => 'View'],
            ['key' => 'pos.view', 'label' => 'Voir POS', 'page' => 'POS', 'action' => 'View'],
            ['key' => 'stock.view', 'label' => 'Voir stock', 'page' => 'Stock', 'action' => 'View'],
            ['key' => 'stock.create', 'label' => 'Créer produit', 'page' => 'Stock', 'action' => 'Create'],
            ['key' => 'stock.edit', 'label' => 'Modifier produit', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'stock.delete', 'label' => 'Supprimer produit', 'page' => 'Stock', 'action' => 'Delete'],
            ['key' => 'stock.adjust', 'label' => 'Ajuster stock', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'supplier.manage', 'label' => 'Gérer fournisseurs', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'category.manage', 'label' => 'Gérer catégories', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'brand.manage', 'label' => 'Gérer marques', 'page' => 'Stock', 'action' => 'Edit'],
            ['key' => 'notifications.view', 'label' => 'Voir notifications', 'page' => 'Notifications', 'action' => 'View'],
            ['key' => 'settings.view', 'label' => 'Voir settings', 'page' => 'Settings', 'action' => 'View'],
            ['key' => 'settings.update', 'label' => 'Modifier settings', 'page' => 'Settings', 'action' => 'Edit'],
            ['key' => 'users.manage', 'label' => 'Gérer utilisateurs', 'page' => 'Settings', 'action' => 'Edit'],
            ['key' => 'roles.manage', 'label' => 'Gérer rôles', 'page' => 'Settings', 'action' => 'Edit'],
            ['key' => 'checkout.create', 'label' => 'Valider vente', 'page' => 'POS', 'action' => 'Create'],
            ['key' => 'receipt.export', 'label' => 'Exporter ticket', 'page' => 'POS', 'action' => 'Export'],
            ['key' => 'barcode.print', 'label' => 'Imprimer code-barres', 'page' => 'Stock', 'action' => 'Export'],
            ['key' => 'reports.view', 'label' => 'Voir rapports', 'page' => 'Reports', 'action' => 'View'],
            ['key' => 'reports.export', 'label' => 'Exporter rapports', 'page' => 'Reports', 'action' => 'Export'],
            // Annulations
            ['key' => 'sale.cancel', 'label' => 'Annuler vente', 'page' => 'POS', 'action' => 'Delete'],
            ['key' => 'sale.cancel.authorize', 'label' => 'Autoriser annulation', 'page' => 'POS', 'action' => 'Approve'],
            // Dépenses
            ['key' => 'expenses.view', 'label' => 'Voir dépenses', 'page' => 'Expenses', 'action' => 'View'],
            ['key' => 'expenses.create', 'label' => 'Créer dépense', 'page' => 'Expenses', 'action' => 'Create'],
            ['key' => 'expenses.edit', 'label' => 'Modifier dépense', 'page' => 'Expenses', 'action' => 'Edit'],
            ['key' => 'expenses.delete', 'label' => 'Supprimer dépense', 'page' => 'Expenses', 'action' => 'Delete'],
            ['key' => 'expenses.approve', 'label' => 'Approuver dépense', 'page' => 'Expenses', 'action' => 'Approve'],
            // Promotions
            ['key' => 'promos.manage', 'label' => 'Gérer les promotions', 'page' => 'Promotions', 'action' => 'Edit'],
            // Commandes Externes
            ['key' => 'external_orders.view',   'label' => 'Voir commandes externes',     'page' => 'ExternalOrders', 'action' => 'View'],
            ['key' => 'external_orders.create', 'label' => 'Créer commande externe',       'page' => 'ExternalOrders', 'action' => 'Create'],
            ['key' => 'external_orders.edit',   'label' => 'Modifier commande externe',    'page' => 'ExternalOrders', 'action' => 'Edit'],
            ['key' => 'external_orders.delete', 'label' => 'Supprimer commande externe',   'page' => 'ExternalOrders', 'action' => 'Delete'],
        ];
    }

    private function ensureDefaultAccessRights(): void
    {
        // Guard: only run the expensive bulk writes when the permission count is
        // out of sync. This replaces a Cache::remember() guard which was useless
        // because CACHE_STORE=array resets after every request.
        $expectedCount = count($this->permissionCatalog());
        if (Permission::query()->count() >= $expectedCount) {
            return;
        }

        foreach ($this->permissionCatalog() as $permission) {
            Permission::query()->updateOrCreate(
                ['key' => $permission['key']],
                [
                    'label' => $permission['label'],
                    'page' => $permission['page'],
                    'action' => $permission['action'],
                ]
            );
        }

        $admin = Role::query()->firstOrCreate(['name' => 'Admin'], ['description' => 'Accès complet']);
        $superUser = Role::query()->firstOrCreate(['name' => 'SuperUser'], ['description' => 'Gestion RBAC — ne peut pas modifier le rôle Admin ni générer des licences']);
        $editor = Role::query()->firstOrCreate(['name' => 'Editor'], ['description' => 'Gestion opérationnelle']);
        $viewer = Role::query()->firstOrCreate(['name' => 'Viewer'], ['description' => 'Lecture seule']);

        $allPermissionIds = Permission::query()->pluck('id');
        $admin->permissions()->sync($allPermissionIds);
        $superUser->permissions()->sync($allPermissionIds);

        $editorKeys = [
            'dashboard.view',
            'pos.view',
            'stock.view',
            'stock.create',
            'stock.edit',
            'stock.adjust',
            'supplier.manage',
            'category.manage',
            'brand.manage',
            'notifications.view',
            'checkout.create',
            'receipt.export',
            'barcode.print',
            'promos.manage',
        ];
        $editorPermissionIds = Permission::query()->whereIn('key', $editorKeys)->pluck('id');
        $editor->permissions()->sync($editorPermissionIds);

        $viewerKeys = ['dashboard.view', 'stock.view', 'notifications.view'];
        $viewerPermissionIds = Permission::query()->whereIn('key', $viewerKeys)->pluck('id');
        $viewer->permissions()->sync($viewerPermissionIds);
    }

    private function writeAudit(string $event, ?string $targetType = null, ?int $targetId = null, array $meta = []): void
    {
        AuditLog::query()->create([
            'actor_user_id' => Auth::id(),
            'event' => $event,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'meta' => $meta,
        ]);
    }

    private function generateInternalEmail(): string
    {
        do {
            $email = 'user_' . Str::lower(Str::random(10)) . '@local.pos';
        } while (User::query()->where('email', $email)->exists());

        return $email;
    }

    public function dashboard(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
        $date = $request->input('date', now()->format('Y-m-d'));

        // ── Period report (existing "Rapport détaillé" section) ────────────
        $sales = Sale::query()
            ->with('customer')
            ->completed()
            ->dateRange($from, $to)
            ->orderBy('date', 'desc')
            ->get();

        $totalRevenue = $sales->sum('total_amount');
        $salesCount = $sales->count();

        $productsSold = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('sales.status', 'completed')
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

        $lowStockThreshold = (int) Setting::get('low_stock_threshold', 10);
        $criticalThreshold = (int) max(1, intdiv($lowStockThreshold, 2));
        $lowStockCount = ProductVariant::query()->where('stock_quantity', '<=', $lowStockThreshold)->count();
        $criticalStockCount = ProductVariant::query()->where('stock_quantity', '<=', $criticalThreshold)->count();
        $alertStockCount = max(0, $lowStockCount - $criticalStockCount);

        // ── Daily snapshot: "Tickets du jour" (day-filterable, defaults to today) ──
        $dailyTickets = Sale::query()
            ->with(['customer:id,name', 'cashier:id,name', 'cancellation'])
            ->dateRange($date, $date)
            ->orderByDesc('date')
            ->get();

        $dailyCompleted = $dailyTickets->where('status', 'completed');
        $dailyRevenue = $dailyCompleted->sum('total_amount');
        $dailySalesCount = $dailyCompleted->count();
        $dailyCancellations = SaleCancellation::query()
            ->whereBetween('cancelled_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->get();
        $dailyCancellationsCount = $dailyCancellations->count();
        $dailyRefundedAmount = $dailyCancellations->sum('refund_amount');
        $dailyProductsCount = (float) DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.date', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->where('sales.status', 'completed')
            ->sum('sale_items.quantity');

        // ── Chart data ───────────────────────────────────────────────────
        $trendEnd = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        $trendStart = $trendEnd->copy()->subDays(13);

        $revenueRows = Sale::query()
            ->selectRaw('DATE(date) as day, SUM(total_amount) as revenue, COUNT(*) as cnt')
            ->completed()
            ->dateRange($trendStart->format('Y-m-d'), $trendEnd->format('Y-m-d'))
            ->groupBy('day')
            ->get()
            ->keyBy(fn ($row) => substr($row->day, 0, 10));

        $cancelRows = SaleCancellation::query()
            ->selectRaw('DATE(cancelled_at) as day, COUNT(*) as cnt')
            ->whereBetween('cancelled_at', [$trendStart->format('Y-m-d') . ' 00:00:00', $trendEnd->format('Y-m-d') . ' 23:59:59'])
            ->groupBy('day')
            ->get()
            ->keyBy(fn ($row) => substr($row->day, 0, 10));

        $salesTrend = [];
        $cancellationsTrend = [];
        for ($cursor = $trendStart->copy(); $cursor->lte($trendEnd); $cursor->addDay()) {
            $key = $cursor->format('Y-m-d');
            $revenueRow = $revenueRows->get($key);
            $cancelRow = $cancelRows->get($key);

            $salesTrend[] = [
                'label' => $cursor->format('d/m'),
                'revenue' => (float) ($revenueRow->revenue ?? 0),
                'count' => (int) ($revenueRow->cnt ?? 0),
            ];
            $cancellationsTrend[] = [
                'label' => $cursor->format('d/m'),
                'count' => (int) ($cancelRow->cnt ?? 0),
            ];
        }

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->where('sales.status', 'completed')
            ->select(
                'products.name as product_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(6)
            ->get();

        $paymentBreakdownDaily = Sale::query()
            ->completed()
            ->dateRange($date, $date)
            ->select('payment_method', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return view('pos.dashboard', [
            'from' => $from,
            'to' => $to,
            'date' => $date,
            'sales' => $sales,
            'totalRevenue' => $totalRevenue,
            'salesCount' => $salesCount,
            'productsCount' => $productsCount,
            'lowStockCount' => $lowStockCount,
            'criticalStockCount' => $criticalStockCount,
            'alertStockCount' => $alertStockCount,
            'productsSold' => $productsSold,
            'lowStockThreshold' => $lowStockThreshold,
            'dailyTickets' => $dailyTickets,
            'dailyRevenue' => $dailyRevenue,
            'dailySalesCount' => $dailySalesCount,
            'dailyCancellationsCount' => $dailyCancellationsCount,
            'dailyRefundedAmount' => $dailyRefundedAmount,
            'dailyProductsCount' => $dailyProductsCount,
            'salesTrend' => $salesTrend,
            'cancellationsTrend' => $cancellationsTrend,
            'topProducts' => $topProducts,
            'paymentBreakdownDaily' => $paymentBreakdownDaily,
        ]);
    }

    public function dashboardPrint(Request $request): View
    {
        $from = $request->input('from', now()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $baseQuery = Sale::query()
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'completed');

        $totalRevenue = (clone $baseQuery)->sum('total_amount');
        $salesCount = (clone $baseQuery)->count();

        $paymentBreakdown = (clone $baseQuery)
            ->select('payment_method', DB::raw('COUNT(*) as sale_count'), DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        return view('pos.dashboard-print', [
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'salesCount' => $salesCount,
            'paymentBreakdown' => $paymentBreakdown,
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
                        ->select('id', 'name', 'barcode', 'sale_price', 'purchase_price', 'stock_quantity', 'category_id', 'unit', 'image', 'is_promo', 'promo_price', 'promo_start_date', 'promo_end_date', 'promo_title')
                        ->with(['variants' => function ($q) {
                            $q->select('id', 'product_id', 'size', 'color', 'barcode', 'stock_quantity', 'is_default')
                                ->orderBy('size')
                                ->orderBy('color');
                        }])
                        ->orderBy('name');
                },
            ])
            ->get();

        $filterSizes = ProductVariant::query()->whereNotNull('size')->distinct()->orderBy('size')->pluck('size');
        $filterColors = ProductVariant::query()->whereNotNull('color')->distinct()->orderBy('color')->pluck('color');

        return view('pos.index', [
            'categories' => $categories,
            'filterSizes' => $filterSizes,
            'filterColors' => $filterColors,
        ]);
    }

    public function stock(): View
    {
        $products = Product::query()
            ->with(['category:id,name', 'supplier:id,name', 'brand:id,name', 'variants'])
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

        $brands = Brand::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $filterSizes = ProductVariant::query()->whereNotNull('size')->distinct()->orderBy('size')->pluck('size');
        $filterColors = ProductVariant::query()->whereNotNull('color')->distinct()->orderBy('color')->pluck('color');

        $stockHistory = StockMovement::query()
            ->with(['variant.product:id,name,brand_id', 'user:id,name'])
            ->latest()
            ->limit(50)
            ->get();

        // [MODIV] ── Couleurs personnalisées ────────────────────────────────────
        // Récupère les couleurs créées manuellement par l'utilisateur depuis la table
        // `settings` (clé: custom_product_colors, type: json).
        // Ces couleurs s'ajoutent aux couleurs prédéfinies dans la vue stock.blade.php
        // et restent disponibles pour tous les produits futurs.
        // تُجلب الألوان المخصصة المحفوظة في جدول settings وتُمرَّر للـ View لعرضها
        // في لوحة اختيار الألوان السريع عند إضافة منتج جديد.
        $customColors = Setting::get('custom_product_colors', []);
        if (! is_array($customColors)) {
            $customColors = [];
        }

        return view('pos.stock', [
            'products' => $products,
            'suppliers' => $suppliers,
            'categories' => $categories,
            'brands' => $brands,
            'filterSizes' => $filterSizes,
            'filterColors' => $filterColors,
            'stockHistory' => $stockHistory,
            'customColors' => $customColors,
        ]);
    }

    public function addProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'barcode' => ['nullable', 'string', 'unique:products,barcode', 'unique:product_variants,barcode'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'in:unité,metre,cm,m'],
            'sell_by_length' => ['nullable', 'boolean'],
            'length_unit' => ['nullable', 'string', 'in:m,cm'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'string', 'max:50'],
            'colors' => ['nullable', 'array'],
            'colors.*' => ['nullable', 'string', 'max:50'],
            'sizes_text' => ['nullable', 'string', 'max:500'],
            'colors_text' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:2048', 'regex:'.self::IMAGE_URL_REGEX],
        ], [
            'image_url.regex' => "L'URL doit pointer directement vers un fichier image (jpg, png, gif, webp...), pas vers une page web.",
        ]);

        $validated['sell_by_length'] = (bool) ($validated['sell_by_length'] ?? false);

        if ($validated['sell_by_length'] && ! empty($validated['length_unit'])) {
            $validated['unit'] = $validated['length_unit'];
        } else {
            $validated['unit'] = $validated['unit'] ?? 'unité';
        }

        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->variantStock->generateUniqueBarcode();
        }

        $sizes = $validated['sizes'] ?? [];
        $colors = $validated['colors'] ?? [];
        if (! empty($validated['sizes_text'])) {
            $sizes[] = $validated['sizes_text'];
        }
        if (! empty($validated['colors_text'])) {
            $colors[] = $validated['colors_text'];
        }

        $validated['image'] = $this->resolveProductImage($request);

        $stockPerVariant = (float) $validated['stock_quantity'];
        $productData = collect($validated)->except([
            'sizes', 'colors', 'sizes_text', 'colors_text', 'stock_quantity',
            'image_file', 'image_url',
        ])->all();
        $productData['stock_quantity'] = 0;

        $product = DB::transaction(function () use ($productData, $sizes, $colors, $stockPerVariant, $validated) {
            $product = Product::query()->create($productData);
            $this->variantStock->syncVariants(
                $product,
                $sizes,
                $colors,
                $stockPerVariant,
                $validated['barcode'] ?? null,
            );

            return $product;
        });

        return redirect()->route('pos.stock')->with([
            'success' => 'Produit ajouté avec succès! Variantes générées automatiquement.',
            'new_product_id' => $product->id,
        ]);
    }

    private function generateUniqueBarcode(): string
    {
        return $this->variantStock->generateUniqueBarcode();
    }

    /**
     * Resolve the product `image` column value from a request: an uploaded
     * file, a pasted URL, an explicit removal, or — when none of those were
     * submitted — the image already on the product (edit) / nothing (create).
     */
    private function resolveProductImage(Request $request, ?string $currentImage = null): ?string
    {
        if ($request->boolean('remove_image')) {
            $this->deleteStoredProductImage($currentImage);

            return null;
        }

        if ($request->hasFile('image_file')) {
            $this->deleteStoredProductImage($currentImage);

            $storedPath = $request->file('image_file')->store('products', 'public');

            if ($storedPath === false) {
                // The 'public' disk is configured with 'throw' => false, so a
                // storage failure (permissions, disk full, ...) returns false
                // instead of throwing — surface it instead of silently
                // corrupting the product's image column.
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'image_file' => "Échec de l'enregistrement de l'image. Réessayez.",
                ]);
            }

            return $storedPath;
        }

        if ($request->filled('image_url')) {
            $this->deleteStoredProductImage($currentImage);

            return $request->input('image_url');
        }

        return $currentImage;
    }

    /**
     * Delete a product's previously stored image file — but only if it's a
     * local upload. External URLs aren't ours to delete.
     */
    private function deleteStoredProductImage(?string $image): void
    {
        if (! $image) {
            return;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($image);
    }

    /**
     * Render a clean browser-printable barcode page (no PDF, no layout).
     * Accepts GET params: product_id, quantity.
     */
    public function barcodePrintPage(Request $request): View
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Generate the barcode SVG once — all labels are identical
        $generator = new BarcodeGeneratorSVG();
        $svg = $generator->getBarcode((string) $product->barcode, $generator::TYPE_CODE_128);

        return view('pos.barcode-print', [
            'product' => $product,
            'svg' => $svg,
            'quantity' => (int) $validated['quantity'],
        ]);
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
            'brand_id' => ['nullable', 'exists:brands,id'],
            'barcode' => ['required', 'string', 'unique:products,barcode,'.$product->id],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'in:unité,metre,cm'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'string', 'max:50'],
            'colors' => ['nullable', 'array'],
            'colors.*' => ['nullable', 'string', 'max:50'],
            'sizes_text' => ['nullable', 'string', 'max:500'],
            'colors_text' => ['nullable', 'string', 'max:500'],
            'stock_per_variant' => ['nullable', 'numeric', 'min:0'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:2048', 'regex:'.self::IMAGE_URL_REGEX],
            'remove_image' => ['nullable', 'boolean'],
        ], [
            'image_url.regex' => "L'URL doit pointer directement vers un fichier image (jpg, png, gif, webp...), pas vers une page web.",
        ]);

        $sizes = $validated['sizes'] ?? [];
        $colors = $validated['colors'] ?? [];
        if (! empty($validated['sizes_text'])) {
            $sizes[] = $validated['sizes_text'];
        }
        if (! empty($validated['colors_text'])) {
            $colors[] = $validated['colors_text'];
        }

        // Preserve existing size/color sets when fields omitted.
        if ($sizes === [] && $colors === [] && $product->variants()->where('is_default', false)->exists()) {
            $sizes = $product->variants()->whereNotNull('size')->pluck('size')->unique()->all();
            $colors = $product->variants()->whereNotNull('color')->pluck('color')->unique()->all();
        }

        $stockPerNew = (float) ($validated['stock_per_variant'] ?? 0);
        $validated['image'] = $this->resolveProductImage($request, $product->image);

        DB::transaction(function () use ($product, $validated, $sizes, $colors, $stockPerNew) {
            $product->update(collect($validated)->only([
                'name', 'brand_id', 'barcode', 'category_id', 'supplier_id', 'purchase_price', 'sale_price', 'unit', 'image',
            ])->all());

            $this->variantStock->syncVariants($product->fresh(), $sizes, $colors, $stockPerNew);
        });

        return redirect()->route('pos.stock')->with('success', 'Produit modifié avec succès!');
    }

    /**
     * [MODIV] Sauvegarde une nouvelle couleur personnalisée dans la base de données.
     *
     * Cette méthode est appelée en AJAX depuis le modal "Créer une couleur"
     * dans la page Stock (pos/stock). Elle stocke la couleur (nom + hex) dans
     * la table `settings` sous la clé `custom_product_colors` (type JSON).
     *
     * Comportement :
     * - Si la couleur (même nom, insensible à la casse) existe déjà → met à jour son hex.
     * - Sinon → ajoute une nouvelle entrée dans le tableau.
     *
     * تحفظ هذه الدالة الألوان المخصصة التي يُنشئها المستخدم من نافذة
     * "Créer une couleur" في صفحة المخزون. يتم تخزينها في جدول settings
     * كـ JSON تحت المفتاح custom_product_colors وتظهر فوراً في لوحة
     * الألوان السريعة دون الحاجة لإعادة تحميل الصفحة.
     *
     * Route : POST /pos/stock/save-color  (name: pos.stock.save-color)
     * Permission : stock.create
     * Response : JSON { success, color: {name, hex}, message }
     */
    public function saveColor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'hex'  => ['required', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        // Charge les couleurs existantes depuis la table settings
        $customColors = Setting::get('custom_product_colors', []);
        if (! is_array($customColors)) {
            $customColors = [];
        }

        $name = trim($validated['name']);
        $hex  = strtolower(trim($validated['hex']));

        // Vérification de doublon (insensible à la casse) → mise à jour si trouvé
        $exists = false;
        foreach ($customColors as &$c) {
            if (isset($c['name']) && strcasecmp($c['name'], $name) === 0) {
                $c['hex'] = $hex;
                $exists = true;
                break;
            }
        }
        unset($c);

        // Nouvelle couleur → ajout au tableau
        if (! $exists) {
            $customColors[] = [
                'name' => $name,
                'hex'  => $hex,
            ];
        }

        // Persistance JSON dans la table settings
        Setting::set('custom_product_colors', $customColors, 'json');

        return response()->json([
            'success' => true,
            'color' => [
                'name' => $name,
                'hex'  => $hex,
            ],
            'message' => 'Couleur enregistrée avec succès!',
        ]);
    }

    public function deleteProduct(Product $product)
    {
        $this->deleteStoredProductImage($product->image);
        $product->delete();

        return redirect()->route('pos.stock')->with('success', 'Produit supprimé avec succès!');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'adjustment_type' => ['required', 'in:add,remove,set'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $variant = null;
        if (! empty($validated['product_variant_id'])) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('id', $validated['product_variant_id'])
                ->firstOrFail();
        } else {
            $variant = $this->variantStock->ensureDefaultVariant($product);
        }

        $variant = $this->variantStock->adjust(
            $variant,
            $validated['adjustment_type'],
            (float) $validated['quantity'],
            $validated['reason'] ?? null,
        );

        $unitLabel = $product->unit ?? 'unités';
        if ($unitLabel === 'metre') {
            $unitLabel = 'mètres';
        }
        if ($unitLabel === 'cm') {
            $unitLabel = 'cm';
        }
        if ($unitLabel === 'unité') {
            $unitLabel = 'pièces';
        }

        return redirect()->route('pos.stock')->with(
            'success',
            'Stock variante ajusté ('.$variant->label().') ! Nouveau stock: '.(float) $variant->stock_quantity.' '.$unitLabel
        );
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

    public function addBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
        ]);

        Brand::query()->create($validated);

        return redirect()->route('pos.stock')->with('success', 'Marque ajoutée avec succès!');
    }

    public function editBrand(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name,' . $brand->id],
        ]);

        $brand->update($validated);

        return redirect()->route('pos.stock')->with('success', 'Marque modifiée avec succès!');
    }

    public function deleteBrand(Brand $brand)
    {
        $productsCount = $brand->products()->count();

        if ($productsCount > 0) {
            return redirect()->route('pos.stock')->with('error', 'Impossible de supprimer cette marque car elle est liée à ' . $productsCount . ' produit(s).');
        }

        $brand->delete();

        return redirect()->route('pos.stock')->with('success', 'Marque supprimée avec succès!');
    }

    public function notifications(): View
    {
        $threshold = (int) Setting::get('low_stock_threshold', 10);
        $criticalThreshold = (int) max(1, intdiv($threshold, 2));

        $lowStockVariants = ProductVariant::query()
            ->with(['product:id,name,brand_id,unit,category_id', 'product.category:id,name', 'product.brand:id,name'])
            ->where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity')
            ->get();

        return view('pos.notifications', [
            'lowStockVariants' => $lowStockVariants,
            'lowStockProducts' => $lowStockVariants,
            'lowStockThreshold' => $threshold,
            'criticalThreshold' => $criticalThreshold,
        ]);
    }

    private function settingsPayload(): array
    {
        $this->ensureDefaultAccessRights();

        $printers = $this->getAvailablePrinters();
        $license = License::current();
        $licensePeriods = LicenseService::PERIODS;

        $settings = [
            'printer_name' => Setting::get('printer_name', 'Ticket POS Printer (USB)'),
            'printer_name_sale' => Setting::get('printer_name_sale', Setting::get('printer_name', 'Ticket POS Printer (USB)')),
            'printer_name_barcode' => Setting::get('printer_name_barcode', 'Barcode Printer (USB)'),
            'ticket_format' => Setting::get('ticket_format', '80mm thermal'),
            'auto_print' => Setting::get('auto_print', 'true'),
            'scanner_mode' => Setting::get('scanner_mode', 'Scan direct vers champ barcode'),
            'scanner_beep' => Setting::get('scanner_beep', 'true'),
            'currency' => Setting::get('currency', 'DHs'),
            'cash_label' => Setting::get('cash_label', 'DHs'),
            'low_stock_threshold' => Setting::get('low_stock_threshold', '10'),
            'default_customer' => Setting::get('default_customer', 'Zbon عام (Cash)'),
            'notification_email' => Setting::get('notification_email', 'admin@droguerie.com'),
            'stock_check_frequency' => Setting::get('stock_check_frequency', 'Chaque heure'),
            'tax_enabled' => Setting::get('tax_enabled', 'false'),
            'tax_rate' => Setting::get('tax_rate', '0'),
            'tax_label' => Setting::get('tax_label', 'VAT'),
            'tax_inclusive' => Setting::get('tax_inclusive', 'false'),
        ];

        return compact('settings', 'printers', 'license', 'licensePeriods');
    }

    private function userManagementPayload(): array
    {
        $users = User::query()->orderBy('name')->get();
        $roles = Role::query()->with('permissions:id,key')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('page')->orderBy('action')->get();
        $permissionsByPage = $permissions->groupBy('page');
        $auditLogs = AuditLog::query()->with('actor:id,name')->latest()->limit(20)->get();

        return compact('users', 'roles', 'permissions', 'permissionsByPage', 'auditLogs');
    }

    public function settings(): View
    {
        return view('pos.settings.index', $this->settingsPayload());
    }

    public function settingsPrinter(): View
    {
        return view('pos.settings.printer', $this->settingsPayload());
    }

    public function settingsBarcode(): View
    {
        return view('pos.settings.barcode', $this->settingsPayload());
    }

    public function settingsTicketFormat(): View
    {
        return view('pos.settings.ticket-format', $this->settingsPayload());
    }

    public function settingsAutoPrint(): View
    {
        return view('pos.settings.auto-print', $this->settingsPayload());
    }

    public function settingsInputMode(): View
    {
        return view('pos.settings.input-mode', $this->settingsPayload());
    }

    public function settingsSound(): View
    {
        return view('pos.settings.sound', $this->settingsPayload());
    }

    public function settingsLicense(): View
    {
        return view('pos.settings.license', $this->settingsPayload());
    }

    public function settingsStock(): View
    {
        return view('pos.settings.stock', $this->settingsPayload());
    }

    public function settingsUsers(): View
    {
        return view('pos.settings.users', array_merge(
            $this->settingsPayload(),
            $this->userManagementPayload()
        ));
    }

    public function settingsTaxes(): View
    {
        return view('pos.settings.taxes', $this->settingsPayload());
    }

    private function getAvailablePrinters(): array
    {
        // CACHE_STORE=array resets after every request, so Cache::remember() is
        // useless here. Instead, store the printer list in the settings table
        // with a refresh timestamp and only call shell_exec once every 5 minutes.
        $cachedAt   = Setting::get('_printers_cached_at', null);
        $cachedList = Setting::get('_printers_list', null);

        if ($cachedAt && $cachedList && (time() - (int) $cachedAt) < 300) {
            return json_decode($cachedList, true) ?? [];
        }

        $printers = [];

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $command = 'powershell -NonInteractive -Command "Get-Printer | Select-Object -ExpandProperty Name"';
                $output = shell_exec($command);

                if ($output) {
                    foreach (explode("\n", trim($output)) as $printer) {
                        $printer = trim($printer);
                        if ($printer !== '') {
                            $printers[] = $printer;
                        }
                    }
                }
            } else {
                $output = shell_exec('lpstat -p -d 2>/dev/null | grep "^printer" | cut -d" " -f2');
                if ($output) {
                    foreach (explode("\n", trim($output)) as $printer) {
                        $printer = trim($printer);
                        if ($printer !== '') {
                            $printers[] = $printer;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $printers = [];
        }

        Setting::set('_printers_cached_at', (string) time());
        Setting::set('_printers_list', json_encode($printers));

        return $printers;
    }

    public function addUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $this->generateInternalEmail(),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role_name'],
        ]);

        $this->writeAudit('user.role.assigned', User::class, $user->id, ['role_name' => $validated['role_name']]);

        return redirect()->route('pos.settings')->with('success', 'Utilisateur ajouté avec succès');
    }

    public function editUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $user->name = $validated['name'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->role = $validated['role_name'];

        $user->save();
        $this->writeAudit('user.role.updated', User::class, $user->id, ['role_name' => $validated['role_name']]);

        return redirect()->route('pos.settings')->with('success', 'Utilisateur modifié avec succès');
    }

    public function createRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'permission_keys' => 'array',
            'permission_keys.*' => 'string|exists:permissions,key',
        ]);

        $role = Role::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $permissionIds = Permission::query()
            ->whereIn('key', $validated['permission_keys'] ?? [])
            ->pluck('id');
        $role->permissions()->sync($permissionIds);

        $this->writeAudit('role.created', Role::class, $role->id, ['permission_keys' => $validated['permission_keys'] ?? []]);

        return redirect()->route('pos.settings')->with('success', 'Rôle créé avec succès');
    }

    public function updateRole(Request $request, Role $role)
    {
        if ($role->name === 'Admin') {
            return redirect()->route('pos.settings')->with('error', 'Le rôle Admin est protégé et ne peut pas être modifié.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permission_keys' => 'array',
            'permission_keys.*' => 'string|exists:permissions,key',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $permissionIds = Permission::query()
            ->whereIn('key', $validated['permission_keys'] ?? [])
            ->pluck('id');
        $role->permissions()->sync($permissionIds);

        $this->writeAudit('role.updated', Role::class, $role->id, ['permission_keys' => $validated['permission_keys'] ?? []]);

        return redirect()->route('pos.settings')->with('success', 'Rôle mis à jour avec succès');
    }

    public function deleteRole(Role $role)
    {
        if ($role->name === 'Admin') {
            return redirect()->route('pos.settings')->with('error', 'Le rôle Admin ne peut pas être supprimé.');
        }

        $usersCount = User::query()->where('role', $role->name)->count();
        if ($usersCount > 0) {
            return redirect()->route('pos.settings')->with('error', 'Ce rôle est assigné à des utilisateurs.');
        }

        $this->writeAudit('role.deleted', Role::class, $role->id, ['name' => $role->name]);
        $role->delete();

        return redirect()->route('pos.settings')->with('success', 'Rôle supprimé avec succès');
    }

    public function assignUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_name' => 'nullable|string|exists:roles,name',
        ]);

        if (empty($validated['role_name'])) {
            $user->role = null;
            $user->save();
            $this->writeAudit('user.role.removed', User::class, $user->id);

            return redirect()->route('pos.settings')->with('success', 'Rôle retiré avec succès');
        }

        $user->role = $validated['role_name'];
        $user->save();
        $this->writeAudit('user.role.assigned', User::class, $user->id, ['role_name' => $validated['role_name']]);

        return redirect()->route('pos.settings')->with('success', 'Rôle utilisateur mis à jour avec succès');
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
            'printer_name_sale' => 'nullable|string',
            'printer_name_barcode' => 'nullable|string',
            'ticket_format' => 'nullable|string',
            'auto_print' => 'nullable|string',
            'scanner_mode' => 'nullable|string',
            'scanner_beep' => 'nullable|string',
            'currency' => 'nullable|string',
            'cash_label' => 'nullable|string',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'default_customer' => 'nullable|string',
            'notification_email' => 'nullable|email',
            'stock_check_frequency' => 'nullable|string',
            'tax_enabled' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_label' => 'nullable|string',
            'tax_inclusive' => 'nullable|string',
        ]);

        $integerKeys = ['low_stock_threshold'];

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                $type = in_array($key, $integerKeys, true) ? 'integer' : 'string';
                Setting::set($key, $value, $type);
            }
        }

        if (array_key_exists('low_stock_threshold', $settings) && $settings['low_stock_threshold'] !== null) {
            // The sidebar badge count is cached for 30s (see ShareNavData) to
            // avoid re-querying on every nav render — bust it here so a
            // changed threshold is reflected immediately instead of after
            // a stale delay.
            Cache::forget('nav:low_stock_count');
        }

        return back()->with('success', 'Paramètres mis à jour avec succès');
    }

    public function scan(Request $request): JsonResponse
    {
        $barcode = $request->query('barcode');
        $size = $request->query('size');
        $color = $request->query('color');
        $brand = $request->query('brand');

        if (! $barcode) {
            return response()->json([
                'message' => 'Barcode is required.',
            ], 400);
        }

        $variant = $this->variantStock->findByBarcode($barcode);

        if ($variant === null) {
            /** @var Product|null $product */
            $product = Product::query()
                ->with(['variants', 'brand'])
                ->where('barcode', $barcode)
                ->orWhere('name', $barcode)
                ->first();

            if ($product === null) {
                return response()->json([
                    'message' => 'Product not found for this barcode.',
                ], 404);
            }

            if ($size !== null || $color !== null || $brand !== null) {
                $variant = $product->variants
                    ->first(function (ProductVariant $v) use ($size, $color, $brand) {
                        return ($size === null || $v->size === $size)
                            && ($color === null || $v->color === $color)
                            && ($brand === null || $v->product?->brand?->name === $brand);
                    });
            }

            if ($variant === null && $product->variants->count() === 1) {
                $variant = $product->variants->first();
            }

            if ($variant === null) {
                return response()->json([
                    'needs_variant' => true,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'brand' => $product->brand?->name,
                        'barcode' => $product->barcode,
                        'category_id' => $product->category_id,
                        'price' => (float) $product->sale_price,
                        'purchase_price' => (float) $product->purchase_price,
                        'unit' => $product->unit ?? 'unité',
                        'sizes' => $product->available_sizes,
                        'colors' => $product->available_colors,
                        'variants' => $product->variants->map(fn (ProductVariant $v) => [
                            'id' => $v->id,
                            'size' => $v->size,
                            'color' => $v->color,
                            'barcode' => $v->barcode,
                            'stock_quantity' => (float) $v->stock_quantity,
                            'label' => $v->label(),
                        ])->values(),
                    ],
                ]);
            }
        }

        $product = $variant->product;

        return response()->json([
            'id' => $product->id,
            'variant_id' => $variant->id,
            'name' => $product->name,
            'brand' => $product->brand?->name,
            'size' => $variant->size,
            'color' => $variant->color,
            'variant_label' => $variant->label(),
            'barcode' => $variant->barcode,
            'category_id' => $product->category_id,
            'price' => (float) ($product->sale_price ?? 0),
            'purchase_price' => (float) ($product->purchase_price ?? 0),
            'stock_quantity' => (float) $variant->stock_quantity,
            'unit' => $product->unit ?? 'unité',
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.barcode' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customerId = $validated['customer_id'] ?? Customer::query()->value('id');

        if (! $customerId) {
            return back()->withErrors([
                'customer_id' => 'No customer found. Create at least one customer before checkout.',
            ]);
        }

        try {
            $result = DB::transaction(function () use ($validated, $customerId) {
                $resolvedItems = [];
                $totalAmount = 0;

                foreach ($validated['items'] as $item) {
                    $variant = null;

                    if (! empty($item['variant_id'])) {
                        $variant = ProductVariant::query()
                            ->with('product')
                            ->where('id', $item['variant_id'])
                            ->lockForUpdate()
                            ->first();
                    }

                    if ($variant === null) {
                        $variant = ProductVariant::query()
                            ->with('product')
                            ->where('barcode', $item['barcode'])
                            ->lockForUpdate()
                            ->first();
                    }

                    if ($variant === null) {
                        abort(422, 'Variant not found for barcode: '.$item['barcode']);
                    }

                    $quantity = (float) $item['quantity'];
                    $unitPrice = (float) $item['price'];
                    $subtotal = $unitPrice * $quantity;

                    $this->variantStock->decrement(
                        $variant,
                        $quantity,
                        StockMovement::TYPE_SALE,
                        Sale::class,
                        null,
                        'Vente POS'
                    );

                    $resolvedItems[] = [
                        'product' => $variant->product,
                        'variant' => $variant,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];

                    $totalAmount += $subtotal;
                }

                $sale = Sale::query()->create([
                    'customer_id' => $customerId,
                    'cashier_user_id' => Auth::id(),
                    'date' => now(),
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'status' => 'completed',
                ]);

                foreach ($resolvedItems as $row) {
                    SaleItem::query()->create([
                        'sale_id' => $sale->id,
                        'product_id' => $row['product']->id,
                        'product_variant_id' => $row['variant']->id,
                        'quantity' => $row['quantity'],
                        'unit_price' => $row['unit_price'],
                        'subtotal' => $row['subtotal'],
                    ]);
                }

                // Attach sale id on movements for the just-created sale (optional refinement skipped).

                $receipt = Receipt::query()->create([
                    'sale_id' => $sale->id,
                    'receipt_number' => 'RCP-'.now()->format('Ymd').'-'.$sale->id,
                    'printed_at' => now(),
                ]);

                return [$sale->id, $receipt->id];
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // abort(422, '...') for "variant not found" / "insufficient stock" —
            // surface it as a friendly flash instead of crashing the whole POS
            // screen to a raw exception page mid-sale.
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pos.receipt.print', ['receipt' => $result[1]]);
    }

    public function receiptPrint(Receipt $receipt)
    {
        $receipt->load(['sale.items.product', 'sale.cashier']);

        // ── Settings ────────────────────────────────────────────────────────
        $printerName = Setting::get('printer_name_sale', Setting::get('printer_name', 'printer WD8260'));
        $currencySymbol = Setting::get('currency_symbol', 'DH');

        // ── Exact Layout (42 columns standard for 80mm) ────────────────────
        $w = 40;
        $divider = str_repeat('-', $w);

        $lines = [];
        $lines[] = "";
        $lines[] = str_pad('D3 ALLOUCHE', $w, ' ', STR_PAD_BOTH);
        $lines[] = str_pad('Quincaillerie & Articles de', $w, ' ', STR_PAD_BOTH);
        $lines[] = str_pad('rue AHMED AL MOKRI', $w, ' ', STR_PAD_BOTH);
        $lines[] = str_pad('Tél: +212 6699112486', $w, ' ', STR_PAD_BOTH);
        $lines[] = $divider;

        $dateStr = optional($receipt->printed_at)->format('d/m/Y') ?? now()->format('d/m/Y');
        $timeStr = optional($receipt->printed_at)->format('H:i') ?? now()->format('H:i');
        $cashierName = $receipt->sale->cashier->name ?? 'Précisé';

        $lines[] = str_pad("Ticket N° : " . $receipt->receipt_number, 22) . str_pad("Date : " . $dateStr, 18, ' ', STR_PAD_LEFT);
        $lines[] = str_pad("Caisse : Principal", 22) . str_pad("Heure : " . $timeStr, 18, ' ', STR_PAD_LEFT);
        $lines[] = "Caissier : " . $cashierName;
        $lines[] = $divider;

        $lines[] = str_pad("Article", 16) . str_pad("Qté", 4, ' ', STR_PAD_BOTH) . str_pad("Prix U.", 10, ' ', STR_PAD_LEFT) . str_pad("Total", 10, ' ', STR_PAD_LEFT);
        $lines[] = $divider;

        foreach ($receipt->sale->items as $item) {
            $product = $item->product;
            if (!$product) continue;
            
            $name = $product->name;
            $unit = $product->unit ?? 'unité';
            $qtyValue = (float) $item->quantity;
            
            if ($unit === 'metre') {
                $qty = (float)$qtyValue . 'm';
            } elseif ($unit === 'cm') {
                $qty = (float)$qtyValue . 'cm';
            } else {
                // If it's a whole number, show as integer, otherwise show with 2 decimal places
                $qty = (floor($qtyValue) == $qtyValue) ? (string)(int)$qtyValue : number_format($qtyValue, 2);
            }

            $price = number_format($item->unit_price, 2);
            $total = number_format($item->subtotal, 2);

            $lines[] = str_pad(mb_substr($name, 0, 16), 16) . str_pad($qty, 4, ' ', STR_PAD_BOTH) . str_pad($price, 10, ' ', STR_PAD_LEFT) . str_pad($total, 10, ' ', STR_PAD_LEFT);
            if (mb_strlen($name) > 16) {
                $lines[] = "  " . mb_substr($name, 16, 40);
            }
            $lines[] = "Code: " . $product->barcode . " Unit: " . $unit;
        }

        $lines[] = $divider;
        $sum = number_format($receipt->sale->total_amount, 2);

        $lines[] = str_pad("Sous-total :", 30, ' ', STR_PAD_LEFT) . str_pad($sum, 10, ' ', STR_PAD_LEFT);
        $lines[] = str_pad("TVA (0%) :", 30, ' ', STR_PAD_LEFT) . str_pad("0.00", 10, ' ', STR_PAD_LEFT);
        $lines[] = "";
        $lines[] = str_pad("NET À PAYER :", 23, ' ', STR_PAD_LEFT) . str_pad($sum . " " . $currencySymbol, 17, ' ', STR_PAD_LEFT);
        $lines[] = $divider;

        $lines[] = str_pad("Merci", $w, ' ', STR_PAD_BOTH);
        $lines[] = "";
        $lines[] = "";
        $lines[] = "";
        $lines[] = "";

        $textReceipt = implode("\r\n", $lines);

        // ── Direct Background Printing ───────────────────────────────────────
        $tmpDir = sys_get_temp_dir();
        $baseName = 'rcp_' . $receipt->id . '_' . time();
        $tmpTxt = $tmpDir . DIRECTORY_SEPARATOR . $baseName . '.txt';
        $tmpPs1 = $tmpDir . DIRECTORY_SEPARATOR . $baseName . '.ps1';

        // Write content with UTF-8 BOM
        file_put_contents($tmpTxt, "\xEF\xBB\xBF" . $textReceipt);

        $printerSafe = str_replace("'", "''", $printerName);
        $fileSafe = str_replace("'", "''", $tmpTxt);

        // This PowerShell script uses .NET Drawing with explicit PaperSize
        $psContent = <<<PS
Add-Type -AssemblyName System.Drawing
\$text = Get-Content -Path '{$fileSafe}' -Raw
\$printDoc = New-Object System.Drawing.Printing.PrintDocument
\$printDoc.PrinterSettings.PrinterName = '{$printerSafe}'
\$printDoc.PrintController = New-Object System.Drawing.Printing.StandardPrintController
\$printDoc.DefaultPageSettings.Margins = New-Object System.Drawing.Printing.Margins(0, 0, 0, 0)

# Set 80mm Custom Paper (approx 315 units)
\$paperSize = New-Object System.Drawing.Printing.PaperSize('Custom', 315, 2000)
\$printDoc.DefaultPageSettings.PaperSize = \$paperSize

\$printDoc.Add_PrintPage({
    param(\$sender, \$ev)
    \$font = New-Object System.Drawing.Font('Consolas', 8.0)
    \$brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Black)
    \$ev.Graphics.DrawString(\$text, \$font, \$brush, 0, 0)
    \$ev.HasMorePages = \$false
})
\$printDoc.Print()
\$printDoc.Dispose()
Remove-Item -Force '{$fileSafe}'
Remove-Item -Force '{$tmpPs1}'
PS;

        file_put_contents($tmpPs1, $psContent);

        // Execute background without popen window issues
        $psFile = str_replace('\\', '\\\\', $tmpPs1);
        $cmd = "powershell -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File \"$psFile\"";
        exec("start /B $cmd");

        return redirect()->route('pos.index')
            ->with('success', "Ticket envoyé à l'imprimante.");
    }

    /**
     * Return a PNG barcode image for a product (Code 128) with human-readable text.
     */
    public function productBarcodeImage(Product $product)
    {
        $svgGenerator = new BarcodeGeneratorSVG();
        $svg = $svgGenerator->getBarcode($product->barcode, $svgGenerator::TYPE_CODE_128);

        // Return raw SVG. The caller can embed or display it.
        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // REPORTS & HISTORIQUE
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Main reports dashboard
     */
    public function reports(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        // Calculate summary stats
        $totalSales = Sale::query()
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalExpenses = Expense::query()
            ->whereBetween('expense_date', [$from, $to])
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        $totalCancellations = Sale::query()
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'cancelled')
            ->count();

        $salesCount = Sale::query()
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'completed')
            ->count();

        return view('pos.reports.index', compact(
            'from',
            'to',
            'totalSales',
            'totalExpenses',
            'totalCancellations',
            'salesCount'
        ));
    }

    /**
     * Sales by product report
     */
    public function salesByProduct(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $salesData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('product_variants', 'sale_items.product_variant_id', '=', 'product_variants.id')
            ->whereBetween('sales.date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('sales.status', 'completed')
            ->select(
                'products.id',
                'products.name',
                'brands.name as brand',
                'products.unit',
                'products.category_id',
                'products.purchase_price',
                'product_variants.id as variant_id',
                'product_variants.size',
                'product_variants.color',
                'product_variants.barcode as variant_barcode',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as total_cost'),
                DB::raw('MAX(sales.date) as last_sale_date')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'brands.name',
                'products.unit',
                'products.category_id',
                'products.purchase_price',
                'product_variants.id',
                'product_variants.size',
                'product_variants.color',
                'product_variants.barcode'
            )
            ->orderByDesc('total_revenue')
            ->get();

        return view('pos.reports.sales-by-product', compact('salesData', 'from', 'to'));
    }

    /**
     * Sales by cashier report
     */
    public function salesByCashier(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        // Cancellation counts per cashier in a single subquery — avoids N+1 queries.
        $cancellationCounts = DB::table('sales')
            ->select('cashier_user_id', DB::raw('COUNT(*) as total_cancelled'))
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'cancelled')
            ->groupBy('cashier_user_id')
            ->pluck('total_cancelled', 'cashier_user_id');

        $salesData = Sale::query()
            ->with('cashier:id,name')
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'completed')
            ->select(
                'cashier_user_id',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as avg_basket')
            )
            ->groupBy('cashier_user_id')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($row) use ($cancellationCounts) {
                $cancellations = (int) ($cancellationCounts[$row->cashier_user_id] ?? 0);
                $row->cancellation_rate = $row->total_sales > 0
                    ? round(($cancellations / ($row->total_sales + $cancellations)) * 100, 1)
                    : 0;

                return $row;
            });

        return view('pos.reports.sales-by-cashier', compact('salesData', 'from', 'to'));
    }

    /**
     * Sales cancellations history
     */
    public function cancellations(Request $request): View
    {
        $from    = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to      = $request->input('to', now()->format('Y-m-d'));
        $tab     = $request->input('tab', 'sales'); // 'sales' or 'history'
        $barcode = trim($request->input('barcode', ''));

        // Active sales list (completed, not cancelled) for the period
        $salesQuery = Sale::query()
            ->with([
                'cashier:id,name',
                'customer:id,name',
                'items.product:id,name,barcode,brand_id,unit',
                'items.product.brand:id,name',
                'items.variant:id,product_id,size,color,barcode,stock_quantity',
                'items.variant.product:id,name,brand_id,sale_price',
                'items.variant.product.brand:id,name',
            ])
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'completed')
            ->orderByDesc('date');

        // Filter by barcode: only sales containing a product with this barcode
        if ($barcode !== '') {
            $salesQuery->whereHas('items.product', function ($q) use ($barcode) {
                $q->where('barcode', 'like', '%' . $barcode . '%');
            });
        }

        $sales = $salesQuery->paginate(20, ['*'], 'sales_page');

        // Cancellation history
        $cancellations = SaleCancellation::query()
            ->with(['sale.cashier:id,name', 'cancelledBy:id,name', 'authorizedBy:id,name'])
            ->whereBetween('cancelled_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('cancelled_at')
            ->paginate(20, ['*'], 'cancel_page');

        $currencySymbol = Setting::get('currency', 'DHs');

        return view('pos.reports.cancellations', compact(
            'cancellations', 'sales', 'from', 'to', 'tab', 'currencySymbol', 'barcode'
        ));
    }

    /**
     * Cancel a sale
     */
    public function cancelSale(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|in:client_request,error,fraud,return,other',
            'notes' => 'nullable|string|max:500',
            'refund_amount' => 'required|numeric|min:0|max:' . $sale->total_amount,
            'refund_method' => 'required|in:cash,card,transfer',
        ]);

        // Check if already cancelled
        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Cette vente est déjà annulée.');
        }

        DB::transaction(function () use ($sale, $validated) {
            // Create cancellation record
            SaleCancellation::create([
                'sale_id' => $sale->id,
                'cancelled_by_user_id' => Auth::id(),
                'authorized_by_user_id' => Auth::id(),
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'refund_amount' => $validated['refund_amount'],
                'refund_method' => $validated['refund_method'],
                'cancelled_at' => now(),
            ]);

            // Update sale status
            $sale->update(['status' => 'cancelled']);

            // Restore stock at variant level
            foreach ($sale->items as $item) {
                $variant = $item->variant
                    ?? ($item->product ? $this->variantStock->ensureDefaultVariant($item->product) : null);

                if ($variant) {
                    $this->variantStock->increment(
                        $variant,
                        (float) $item->quantity,
                        StockMovement::TYPE_RETURN,
                        Sale::class,
                        $sale->id,
                        'Annulation ticket'
                    );
                }
            }

            // Audit log
            $this->writeAudit(
                'sale.cancelled',
                Sale::class,
                $sale->id,
                [
                    'reason' => $validated['reason'],
                    'refund_amount' => $validated['refund_amount'],
                ]
            );
        });

        return back()->with('success', 'Vente annulée avec succès. Stock restauré.');
    }

    /**
     * Cancel a single item (product) within a sale.
     * Reduces the sale total and restores the item's stock.
     * If the item is the last one, the whole sale is cancelled.
     */
    public function cancelSaleItem(Request $request, Sale $sale, SaleItem $item): RedirectResponse
    {
        // Security: item must belong to this sale
        if ($item->sale_id !== $sale->id) {
            return back()->with('error', 'Cet article n\'appartient pas à cette vente.');
        }

        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Cette vente est déjà entièrement annulée.');
        }

        $validated = $request->validate([
            'qty_to_cancel' => 'required|numeric|min:0.001|max:' . $item->quantity,
            'reason'        => 'required|in:client_request,error,fraud,return,other',
            'notes'         => 'nullable|string|max:500',
            'refund_method' => 'required|in:cash,card,transfer',
        ]);

        $qtyToCancel = (float) $validated['qty_to_cancel'];
        $refundAmount = round($qtyToCancel * (float) $item->unit_price, 2);

        DB::transaction(function () use ($sale, $item, $validated, $qtyToCancel, $refundAmount) {
            $fullItemCancel = abs($qtyToCancel - (float)$item->quantity) < 0.0001;

            // Restore stock for cancelled quantity (variant-level)
            $variant = $item->variant
                ?? ($item->product ? $this->variantStock->ensureDefaultVariant($item->product) : null);

            if ($variant) {
                $this->variantStock->increment(
                    $variant,
                    $qtyToCancel,
                    StockMovement::TYPE_RETURN,
                    Sale::class,
                    $sale->id,
                    'Annulation article'
                );
            }

            if ($fullItemCancel) {
                // Remove the item entirely
                $item->delete();
            } else {
                // Reduce quantity and subtotal
                $item->quantity  = (float)$item->quantity - $qtyToCancel;
                $item->subtotal  = round($item->quantity * (float)$item->unit_price, 2);
                $item->save();
            }

            // Recalculate sale total from remaining items
            $sale->refresh();
            $remainingItems = $sale->items()->get();
            $newTotal = $remainingItems->sum('subtotal');
            $sale->total_amount = $newTotal;

            if ($remainingItems->isEmpty()) {
                // No items left — cancel the whole sale
                $sale->status = 'cancelled';
            }
            $sale->save();

            // Record a cancellation log entry
            SaleCancellation::create([
                'sale_id'               => $sale->id,
                'cancelled_by_user_id'  => Auth::id(),
                'authorized_by_user_id' => Auth::id(),
                'reason'                => $validated['reason'],
                'notes'                 => $validated['notes'] ?? null,
                'refund_amount'         => $refundAmount,
                'refund_method'         => $validated['refund_method'],
                'cancelled_at'          => now(),
            ]);

            $this->writeAudit('sale.item.cancelled', Sale::class, $sale->id, [
                'item_id'        => $item->id,
                'qty_cancelled'  => $qtyToCancel,
                'refund_amount'  => $refundAmount,
            ]);
        });

        return back()->with('success', 'Article annulé avec succès. Stock restauré.');
    }

    /**
     * Exchange a sold variant for another size/color/brand variant.
     * Restocks the returned variant and decrements the exchanged variant automatically.
     */
    public function exchangeSaleItem(Request $request, Sale $sale, SaleItem $item): RedirectResponse
    {
        if ($item->sale_id !== $sale->id) {
            return back()->with('error', 'Cet article n\'appartient pas à cette vente.');
        }

        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Cette vente est déjà entièrement annulée.');
        }

        $validated = $request->validate([
            'qty_to_exchange' => 'required|numeric|min:0.001|max:'.$item->quantity,
            'new_variant_id' => 'nullable|integer|exists:product_variants,id',
            'new_barcode' => 'nullable|string',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'settlement_method' => 'nullable|in:cash,card,transfer',
        ]);

        if (empty($validated['new_variant_id']) && empty($validated['new_barcode'])) {
            return back()->with('error', 'Sélectionnez la variante d\'échange (taille / couleur / marque).');
        }

        $qty = (float) $validated['qty_to_exchange'];

        DB::transaction(function () use ($sale, $item, $validated, $qty) {
            $returnedVariant = $item->variant
                ?? ($item->product ? $this->variantStock->ensureDefaultVariant($item->product) : null);

            if (! $returnedVariant) {
                abort(422, 'Variante d\'origine introuvable.');
            }

            $returnedVariant = ProductVariant::query()->whereKey($returnedVariant->id)->lockForUpdate()->firstOrFail();

            $newVariant = null;
            if (! empty($validated['new_variant_id'])) {
                $newVariant = ProductVariant::query()
                    ->with('product')
                    ->whereKey($validated['new_variant_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
            } else {
                $newVariant = ProductVariant::query()
                    ->with('product')
                    ->where('barcode', $validated['new_barcode'])
                    ->lockForUpdate()
                    ->first();
            }

            if (! $newVariant) {
                abort(422, 'Variante d\'échange introuvable.');
            }

            if ($newVariant->id === $returnedVariant->id) {
                abort(422, 'Choisissez une variante différente pour l\'échange.');
            }

            // Restock returned item
            $this->variantStock->increment(
                $returnedVariant,
                $qty,
                StockMovement::TYPE_EXCHANGE_IN,
                Sale::class,
                $sale->id,
                'Échange — retour'
            );

            // Decrement new variant
            $this->variantStock->decrement(
                $newVariant,
                $qty,
                StockMovement::TYPE_EXCHANGE_OUT,
                Sale::class,
                $sale->id,
                'Échange — sortie'
            );

            $oldPrice = (float) $item->unit_price;
            $newPrice = (float) $newVariant->product->sale_price;
            $priceDiff = round(($newPrice - $oldPrice) * $qty, 2);

            $fullExchange = abs($qty - (float) $item->quantity) < 0.0001;

            if ($fullExchange) {
                $item->update([
                    'product_id' => $newVariant->product_id,
                    'product_variant_id' => $newVariant->id,
                    'unit_price' => $newPrice,
                    'subtotal' => round($newPrice * $qty, 2),
                ]);
            } else {
                $item->quantity = (float) $item->quantity - $qty;
                $item->subtotal = round((float) $item->quantity * $oldPrice, 2);
                $item->save();

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $newVariant->product_id,
                    'product_variant_id' => $newVariant->id,
                    'quantity' => $qty,
                    'unit_price' => $newPrice,
                    'subtotal' => round($newPrice * $qty, 2),
                ]);
            }

            $sale->refresh();
            $sale->update([
                'total_amount' => $sale->items()->sum('subtotal'),
            ]);

            SaleExchange::query()->create([
                'sale_id' => $sale->id,
                'original_sale_item_id' => $item->id,
                'returned_variant_id' => $returnedVariant->id,
                'exchanged_variant_id' => $newVariant->id,
                'processed_by_user_id' => Auth::id(),
                'quantity' => $qty,
                'price_difference' => $priceDiff,
                'reason' => $validated['reason'] ?? 'exchange',
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->writeAudit('sale.item.exchanged', Sale::class, $sale->id, [
                'from_variant' => $returnedVariant->id,
                'to_variant' => $newVariant->id,
                'qty' => $qty,
                'price_difference' => $priceDiff,
            ]);
        });

        return back()->with('success', 'Échange effectué. Stocks variantes mis à jour automatiquement.');
    }

    /**
     * JSON helper: list variants for exchange UI (same product or any brand).
     */
    public function variantOptions(Request $request): JsonResponse
    {
        $productId = $request->query('product_id');
        $brand = $request->query('brand');
        $q = ProductVariant::query()->with(['product:id,name,brand_id,sale_price', 'product.brand:id,name']);

        if ($productId) {
            $q->where('product_id', $productId);
        }

        if ($brand) {
            $q->whereHas('product.brand', fn ($b) => $b->where('name', $brand));
        }

        if ($request->filled('size')) {
            $q->where('size', $request->query('size'));
        }

        if ($request->filled('color')) {
            $q->where('color', $request->query('color'));
        }

        $variants = $q->orderBy('product_id')->orderBy('size')->orderBy('color')->limit(200)->get()
            ->map(fn (ProductVariant $v) => [
                'id' => $v->id,
                'product_id' => $v->product_id,
                'name' => $v->product?->name,
                'brand' => $v->product?->brand?->name,
                'size' => $v->size,
                'color' => $v->color,
                'barcode' => $v->barcode,
                'stock_quantity' => (float) $v->stock_quantity,
                'price' => (float) ($v->product?->sale_price ?? 0),
                'label' => $v->displayName(),
            ]);

        return response()->json(['variants' => $variants]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // EXPENSES MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Expenses management page
     */
    public function expenses(Request $request): View
    {
        $status = $request->input('status', 'all');
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $query = Expense::query()
            ->with(['createdBy', 'approvedBy'])
            ->whereBetween('expense_date', [$from, $to]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(20);

        $totalPending = Expense::whereBetween('expense_date', [$from, $to])
            ->where('status', 'pending')
            ->sum('amount');

        $totalApproved = Expense::whereBetween('expense_date', [$from, $to])
            ->where('status', 'approved')
            ->sum('amount');

        $totalPaid = Expense::whereBetween('expense_date', [$from, $to])
            ->where('status', 'paid')
            ->sum('amount');

        return view('pos.reports.expenses', compact(
            'expenses',
            'status',
            'from',
            'to',
            'totalPending',
            'totalApproved',
            'totalPaid'
        ));
    }

    /**
     * Create new expense
     */
    public function createExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:rent,utilities,salaries,supplies,maintenance,marketing,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,card',
            'notes' => 'nullable|string|max:500',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        // Upload receipt if provided
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'private');
        }

        $expense = Expense::create([
            'created_by_user_id' => Auth::id(),
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'receipt_path' => $receiptPath,
            'status' => 'pending',
        ]);

        $this->writeAudit('expense.created', Expense::class, $expense->id, [
            'category' => $validated['category'],
            'amount' => $validated['amount'],
        ]);

        return back()->with('success', 'Dépense enregistrée. En attente d\'approbation.');
    }

    /**
     * Update expense
     */
    public function updateExpense(Request $request, Expense $expense): RedirectResponse
    {
        // Only allow editing pending expenses
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Seules les dépenses en attente peuvent être modifiées.');
        }

        $validated = $request->validate([
            'category' => 'required|in:rent,utilities,salaries,supplies,maintenance,marketing,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,card',
            'notes' => 'nullable|string|max:500',
        ]);

        $expense->update($validated);

        $this->writeAudit('expense.updated', Expense::class, $expense->id, $validated);

        return back()->with('success', 'Dépense mise à jour.');
    }

    /**
     * Delete expense
     */
    public function deleteExpense(Expense $expense): RedirectResponse
    {
        // Only allow deleting pending or rejected expenses
        if (!in_array($expense->status, ['pending', 'rejected'])) {
            return back()->with('error', 'Seules les dépenses en attente ou rejetées peuvent être supprimées.');
        }

        $this->writeAudit('expense.deleted', Expense::class, $expense->id, [
            'description' => $expense->description,
            'amount' => $expense->amount,
        ]);

        $expense->delete();

        return back()->with('success', 'Dépense supprimée.');
    }

    /**
     * Approve or reject expense
     */
    public function approveExpense(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($expense->status !== 'pending') {
            return back()->with('error', 'Cette dépense a déjà été traitée.');
        }

        $newStatus = $validated['action'] === 'approve' ? 'approved' : 'rejected';

        $expense->update([
            'status' => $newStatus,
            'approved_by_user_id' => Auth::id(),
            'notes' => $validated['notes'] ?? $expense->notes,
        ]);

        $this->writeAudit('expense.' . $validated['action'] . 'd', Expense::class, $expense->id, [
            'status' => $newStatus,
        ]);

        $message = $validated['action'] === 'approve'
            ? 'Dépense approuvée.'
            : 'Dépense rejetée.';

        return back()->with('success', $message);
    }

    // ── PROMOTIONS & SOLDES ──────────────────────────────────────────────────

    /**
     * Display the Promotions & Soldes management page
     */
    public function promos(Request $request): View
    {
        $allProducts = Product::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->orderBy('name')
            ->get();

        $promoProducts = Product::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->where('is_promo', true)
            ->orderBy('name')
            ->get();

        $categories = Category::query()->orderBy('name')->get();
        $brands = Brand::query()->orderBy('name')->get();

        $totalPromosCount = $promoProducts->count();
        $avgDiscount = $promoProducts->count() > 0 ? (float) $promoProducts->avg('discount_percentage') : 0.0;
        $totalPromoStock = (float) $promoProducts->sum('stock_quantity');

        return view('pos.promos', [
            'promoProducts' => $promoProducts,
            'allProducts' => $allProducts,
            'categories' => $categories,
            'brands' => $brands,
            'totalPromosCount' => $totalPromosCount,
            'avgDiscount' => round($avgDiscount, 1),
            'totalPromoStock' => $totalPromoStock,
        ]);
    }

    /**
     * Store a brand new product directly with original price & promo price
     */
    public function storePromoProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'barcode' => ['nullable', 'string', 'unique:products,barcode', 'unique:product_variants,barcode'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'in:unité,metre,cm,m'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['required', 'numeric', 'min:0'],
            'promo_start_date' => ['nullable', 'date'],
            'promo_end_date' => ['nullable', 'date', 'after_or_equal:promo_start_date'],
            'promo_title' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:2048', 'regex:'.self::IMAGE_URL_REGEX],
        ]);

        if ((float) $validated['promo_price'] >= (float) $validated['sale_price']) {
            return back()->withInput()->withErrors([
                'promo_price' => 'Le prix de promotion ('.$validated['promo_price'].' DH) doit être strictement inférieur au prix d\'origine ('.$validated['sale_price'].' DH).'
            ]);
        }

        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->variantStock->generateUniqueBarcode();
        }

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('products', 'public');
        } elseif (! empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        $product = Product::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'] ?? null,
            'barcode' => $validated['barcode'],
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'unit' => $validated['unit'] ?? 'unité',
            'purchase_price' => $validated['purchase_price'],
            'sale_price' => $validated['sale_price'],
            'is_promo' => true,
            'promo_price' => $validated['promo_price'],
            'promo_start_date' => $validated['promo_start_date'] ?? null,
            'promo_end_date' => $validated['promo_end_date'] ?? null,
            'promo_title' => $validated['promo_title'] ?? 'Solde',
            'image' => $imagePath,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => null,
            'color' => null,
            'barcode' => $product->barcode,
            'stock_quantity' => $product->stock_quantity,
            'is_default' => true,
        ]);

        $this->writeAudit('product.created_promo', Product::class, $product->id, [
            'name' => $product->name,
            'sale_price' => $product->sale_price,
            'promo_price' => $product->promo_price,
        ]);

        return back()->with('success', 'Nouveau produit en promotion créé avec succès!');
    }

    /**
     * Apply promo to an existing product in DB
     */
    public function applyPromo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'promo_price' => ['required', 'numeric', 'min:0'],
            'promo_start_date' => ['nullable', 'date'],
            'promo_end_date' => ['nullable', 'date', 'after_or_equal:promo_start_date'],
            'promo_title' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ((float) $validated['promo_price'] >= (float) $product->sale_price) {
            return back()->withInput()->withErrors(['promo_price' => 'Le prix de promotion ('.$validated['promo_price'].' DH) doit être strictly inférieur au prix d\'origine ('.$product->sale_price.' DH).']);
        }

        $product->update([
            'is_promo' => true,
            'promo_price' => $validated['promo_price'],
            'promo_start_date' => $validated['promo_start_date'] ?? null,
            'promo_end_date' => $validated['promo_end_date'] ?? null,
            'promo_title' => $validated['promo_title'] ?? 'Promotion',
        ]);

        $this->writeAudit('product.promo_applied', Product::class, $product->id, [
            'name' => $product->name,
            'sale_price' => $product->sale_price,
            'promo_price' => $product->promo_price,
        ]);

        return back()->with('success', 'Promotion appliquée sur "'.$product->name.'" avec succès!');
    }

    /**
     * Update promo details for a product
     */
    public function updatePromo(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sale_price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['required', 'numeric', 'min:0'],
            'promo_start_date' => ['nullable', 'date'],
            'promo_end_date' => ['nullable', 'date', 'after_or_equal:promo_start_date'],
            'promo_title' => ['nullable', 'string', 'max:255'],
        ]);

        if ((float) $validated['promo_price'] >= (float) $validated['sale_price']) {
            return back()->withInput()->withErrors([
                'promo_price' => 'Le prix de promotion ('.$validated['promo_price'].' DH) doit être strictement inférieur au prix d\'origine ('.$validated['sale_price'].' DH).'
            ]);
        }

        $product->update([
            'sale_price' => $validated['sale_price'],
            'is_promo' => true,
            'promo_price' => $validated['promo_price'],
            'promo_start_date' => $validated['promo_start_date'] ?? null,
            'promo_end_date' => $validated['promo_end_date'] ?? null,
            'promo_title' => $validated['promo_title'] ?? 'Promotion',
        ]);

        $this->writeAudit('product.promo_updated', Product::class, $product->id, [
            'name' => $product->name,
            'promo_price' => $product->promo_price,
        ]);

        return back()->with('success', 'Promotion mise à jour avec succès pour "'.$product->name.'".');
    }

    /**
     * Toggle active promo status for a product
     */
    public function togglePromo(Request $request, Product $product): RedirectResponse
    {
        $product->is_promo = ! $product->is_promo;
        $product->save();

        $statusStr = $product->is_promo ? 'activée' : 'désactivée';
        return back()->with('success', 'Promotion '.$statusStr.' pour le produit "'.$product->name.'".');
    }

    /**
     * Remove promo from product
     */
    public function removePromo(Product $product): RedirectResponse
    {
        $product->update([
            'is_promo' => false,
            'promo_price' => null,
            'promo_start_date' => null,
            'promo_end_date' => null,
            'promo_title' => null,
        ]);

        $this->writeAudit('product.promo_removed', Product::class, $product->id, ['name' => $product->name]);

        return back()->with('success', 'Promotion retirée avec succès.');
    }

    // ── COMMANDES EXTERNES ───────────────────────────────────────────────────

    /**
     * Display the External Orders management page
     */
    public function externalOrders(Request $request): View
    {
        $query = ExternalOrder::query()
            ->with('createdBy:id,name')
            ->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('platform')) {
            $query->byPlatform($request->platform);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('client_name', 'ILIKE', "%{$s}%")
                  ->orWhere('client_phone', 'ILIKE', "%{$s}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => ExternalOrder::count(),
            'en_attente'  => ExternalOrder::byStatus('En attente')->count(),
            'expedie'     => ExternalOrder::byStatus('Expédié')->count(),
            'livre'       => ExternalOrder::byStatus('Livré')->count(),
            'annule'      => ExternalOrder::byStatus('Annulé')->count(),
            'ca_total'    => ExternalOrder::byStatus('Livré')->sum('total_amount'),
        ];

        return view('pos.external-orders', [
            'orders'    => $orders,
            'stats'     => $stats,
            'platforms' => ExternalOrder::PLATFORMS,
            'statuses'  => ExternalOrder::STATUSES,
            'statusColors'   => ExternalOrder::STATUS_COLORS,
            'platformColors' => ExternalOrder::PLATFORM_COLORS,
        ]);
    }

    /**
     * Store a new external order
     */
    public function storeExternalOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name'     => ['required', 'string', 'max:255'],
            'client_phone'    => ['required', 'string', 'max:50'],
            'client_address'  => ['required', 'string', 'max:1000'],
            'platform_source' => ['required', 'in:Instagram,WhatsApp,Autre'],
            'status'          => ['required', 'in:En attente,Expédié,Livré,Annulé'],
            'total_amount'    => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        $order = ExternalOrder::create(array_merge($validated, [
            'created_by_user_id' => Auth::id(),
        ]));

        $this->writeAudit('external_order.created', ExternalOrder::class, $order->id, [
            'client' => $order->client_name,
            'platform' => $order->platform_source,
            'amount' => $order->total_amount,
        ]);

        return back()->with('success', 'Commande de "'.$order->client_name.'" ajoutée avec succès.');
    }

    /**
     * Update an existing external order
     */
    public function updateExternalOrder(Request $request, ExternalOrder $externalOrder): RedirectResponse
    {
        $validated = $request->validate([
            'client_name'     => ['required', 'string', 'max:255'],
            'client_phone'    => ['required', 'string', 'max:50'],
            'client_address'  => ['required', 'string', 'max:1000'],
            'platform_source' => ['required', 'in:Instagram,WhatsApp,Autre'],
            'status'          => ['required', 'in:En attente,Expédié,Livré,Annulé'],
            'total_amount'    => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        $externalOrder->update($validated);

        $this->writeAudit('external_order.updated', ExternalOrder::class, $externalOrder->id, [
            'client' => $externalOrder->client_name,
            'status' => $externalOrder->status,
        ]);

        return back()->with('success', 'Commande de "'.$externalOrder->client_name.'" mise à jour.');
    }

    /**
     * Update only the status of an external order (quick action)
     */
    public function updateExternalOrderStatus(Request $request, ExternalOrder $externalOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:En attente,Expédié,Livré,Annulé'],
        ]);

        $externalOrder->update(['status' => $validated['status']]);

        $this->writeAudit('external_order.status_changed', ExternalOrder::class, $externalOrder->id, [
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Statut mis à jour en "'.$validated['status'].'".');
    }

    /**
     * Delete an external order
     */
    public function deleteExternalOrder(ExternalOrder $externalOrder): RedirectResponse
    {
        $clientName = $externalOrder->client_name;

        $this->writeAudit('external_order.deleted', ExternalOrder::class, $externalOrder->id, [
            'client' => $clientName,
        ]);

        $externalOrder->delete();

        return back()->with('success', 'Commande de "'.$clientName.'" supprimée.');
    }
}