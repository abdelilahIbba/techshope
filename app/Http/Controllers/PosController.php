<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\License;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Sale;
use App\Models\SaleCancellation;
use App\Models\SaleItem;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Services\LicenseService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PosController extends Controller
{
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
        ];
    }

    private function ensureDefaultAccessRights(): void
    {
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
            'notifications.view',
            'checkout.create',
            'receipt.export',
            'barcode.print',
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

    public function dashboardPrint(Request $request): View
    {
        $from = $request->input('from', now()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $baseQuery = Sale::query()
            ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59']);

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
                        ->select('id', 'name', 'barcode', 'sale_price', 'purchase_price', 'stock_quantity', 'category_id')
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
            'name'           => ['required', 'string', 'max:255'],
            'barcode'        => ['nullable', 'string', 'unique:products,barcode'],
            'category_id'    => ['required', 'exists:categories,id'],
            'supplier_id'    => ['nullable', 'exists:suppliers,id'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'unit'           => ['nullable', 'string', 'in:unité,metre,cm,m'],
            'sell_by_length' => ['nullable', 'boolean'],
            'length_unit'    => ['nullable', 'string', 'in:m,cm'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price'     => ['required', 'numeric', 'min:0'],
        ]);

        $validated['sell_by_length'] = (bool) ($validated['sell_by_length'] ?? false);

        // If sold by length, sync unit with length_unit
        if ($validated['sell_by_length'] && !empty($validated['length_unit'])) {
            $validated['unit'] = $validated['length_unit'];
        } else {
            $validated['unit'] = $validated['unit'] ?? 'unité';
        }


        // Auto-generate barcode if not provided
        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateUniqueBarcode();
        }

        $product = Product::query()->create($validated);

        // After creation, redirect back and pass the new product id to allow printing
        return redirect()->route('pos.stock')->with([
            'success' => 'Produit ajouté avec succès!',
            'new_product_id' => $product->id,
        ]);
    }

    private function generateUniqueBarcode(): string
    {
        do {
            // 12-digit numeric code (can be adjusted)
            $barcode = (string) random_int(100000000000, 999999999999);
        } while (Product::query()->where('barcode', $barcode)->exists());

        return $barcode;
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
            'barcode' => ['required', 'string', 'unique:products,barcode,' . $product->id],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'in:unité,metre,cm'],
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
            'quantity' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $currentStock = (float) $product->stock_quantity;
        $adjustmentQty = (float) $validated['quantity'];
        $newStock = $currentStock;

        switch ($validated['adjustment_type']) {
            case 'add':
                $newStock = $currentStock + $adjustmentQty;
                break;
            case 'remove':
                $newStock = max(0, $currentStock - $adjustmentQty);
                break;
            case 'set':
                $newStock = $adjustmentQty;
                break;
        }

        $product->update(['stock_quantity' => $newStock]);

        $unitLabel = $product->unit ?? 'unités';
        if ($unitLabel === 'metre') $unitLabel = 'mètres';
        if ($unitLabel === 'cm') $unitLabel = 'cm';
        if ($unitLabel === 'unité') $unitLabel = 'pièces';

        return redirect()->route('pos.stock')->with('success', 'Stock ajusté avec succès! Nouveau stock: ' . (float)$newStock . ' ' . $unitLabel);
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

        return back()->with('success', 'Paramètres mis à jour avec succès');
    }

    public function scan(Request $request): JsonResponse
    {
        $barcode = $request->query('barcode');

        if (!$barcode) {
             return response()->json([
                'message' => 'Barcode is required.',
            ], 400);
        }

        /** @var Product|null $product */
        $product = Product::query()
            ->where('barcode', $barcode)
            ->orWhere('name', $barcode)
            ->first();

        if ($product === null) {
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
            'purchase_price' => (float) ($product->purchase_price ?? 0),
            'stock_quantity' => (float) $product->stock_quantity,
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
            'payment_method' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customerId = $validated['customer_id'] ?? Customer::query()->value('id');

        if (!$customerId) {
            return back()->withErrors([
                'customer_id' => 'No customer found. Create at least one customer before checkout.',
            ]);
        }

        $result = DB::transaction(function () use ($validated, $customerId) {
            $resolvedItems = [];
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                /** @var Product|null $product */
                $product = Product::query()
                    ->where('barcode', $item['barcode'])
                    ->lockForUpdate()
                    ->first();

                if ($product === null) {
                    abort(422, 'Product not found for barcode: ' . $item['barcode']);
                }

                $quantity = (float) $item['quantity'];

                if ($product->stock_quantity < $quantity) {
                    abort(422, 'Insufficient stock for: ' . $product->name);
                }

                // Use custom price from request
                $unitPrice = (float) $item['price'];

                $subtotal = $unitPrice * $quantity;

                $product->stock_quantity = $product->stock_quantity - $quantity;
                $product->save();

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
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);
            }

            $receipt = Receipt::query()->create([
                'sale_id' => $sale->id,
                'receipt_number' => 'RCP-' . now()->format('Ymd') . '-' . $sale->id,
                'printed_at' => now(),
            ]);

            return [$sale->id, $receipt->id];
        });

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
            ->whereBetween('sales.date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('sales.status', 'completed')
            ->select(
                'products.id',
                'products.name',
                'products.unit',
                'products.category_id',
                'products.purchase_price',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as total_cost'),
                DB::raw('MAX(sales.date) as last_sale_date')
            )
            ->groupBy('products.id', 'products.name', 'products.unit', 'products.category_id', 'products.purchase_price')
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
            ->map(function ($row) use ($from, $to) {
                // Calculate cancellation rate
                $cancellations = Sale::query()
                    ->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59'])
                    ->where('cashier_user_id', $row->cashier_user_id)
                    ->where('status', 'cancelled')
                    ->count();

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
            ->with(['cashier:id,name', 'customer:id,name', 'items.product:id,name,barcode'])
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

            // Restore stock
            foreach ($sale->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock_quantity = (float)$product->stock_quantity + (float)$item->quantity;
                    $product->save();
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

            // Restore stock for cancelled quantity
            $product = $item->product;
            if ($product) {
                $product->stock_quantity = (float)$product->stock_quantity + $qtyToCancel;
                $product->save();
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
}