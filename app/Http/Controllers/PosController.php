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
            'dashboard.view', 'pos.view', 'stock.view', 'stock.create', 'stock.edit', 'stock.adjust',
            'supplier.manage', 'category.manage', 'notifications.view', 'checkout.create', 'receipt.export',
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
            $email = 'user_'.Str::lower(Str::random(10)).'@local.pos';
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
            'barcode' => ['nullable', 'string', 'unique:products,barcode'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['unit'] = 'unité';

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
            'quantity'   => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Generate the barcode SVG once — all labels are identical
        $generator = new BarcodeGeneratorSVG();
        $svg = $generator->getBarcode((string) $product->barcode, $generator::TYPE_CODE_128);

        return view('pos.barcode-print', [
            'product'  => $product,
            'svg'      => $svg,
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
        $this->ensureDefaultAccessRights();

        $users = User::query()->orderBy('name')->get();
        $roles = Role::query()->with('permissions:id,key')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('page')->orderBy('action')->get();
        $permissionsByPage = $permissions->groupBy('page');
        $auditLogs = AuditLog::query()->with('actor:id,name')->latest()->limit(20)->get();
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
            'currency' => Setting::get('currency', 'DH'),
            'low_stock_threshold' => Setting::get('low_stock_threshold', '10'),
            'default_customer' => Setting::get('default_customer', 'Zbon عام (Cash)'),
            'notification_email' => Setting::get('notification_email', 'admin@droguerie.com'),
            'stock_check_frequency' => Setting::get('stock_check_frequency', 'Chaque heure'),
        ];
        
        return view('pos.settings', compact('users', 'settings', 'printers', 'roles', 'permissions', 'permissionsByPage', 'auditLogs', 'license', 'licensePeriods'));
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
                'receipt_number' => 'RCP-'.now()->format('Ymd').'-'.$sale->id,
                'printed_at' => now(),
            ]);

            return [$sale->id, $receipt->id];
        });

        return redirect()->route('pos.receipt.print', ['receipt' => $result[1]]);
    }

    public function receiptPrint(Receipt $receipt)
    {
        $receipt->load('sale.items.product');

        // ── Resolve printer name from settings ───────────────────────────────
        // The actual Windows printer name is "printer WD8260"
        $printerName = Setting::get('printer_name_sale', Setting::get('printer_name', 'printer WD8260'));

        // ── Build a plain-text receipt (42 columns — standard thermal 80mm) ──
        $w = 42; // characters per line
        $divider = str_repeat('-', $w);
        $doubleLine = str_repeat('=', $w);

        $lines   = [];
        $lines[] = $doubleLine;
        $lines[] = str_pad('DROGUERIE S', $w, ' ', STR_PAD_BOTH);
        $lines[] = str_pad('Quincaillerie & Bricolage', $w, ' ', STR_PAD_BOTH);
        $lines[] = str_pad('Tel: +213 XX XX XX XX', $w, ' ', STR_PAD_BOTH);
        $lines[] = $doubleLine;

        $dateStr = optional($receipt->printed_at)->format('d/m/Y') ?? now()->format('d/m/Y');
        $timeStr = optional($receipt->printed_at)->format('H:i') ?? now()->format('H:i');

        $lines[] = 'Ticket N : ' . $receipt->receipt_number;
        $lines[] = 'Date     : ' . $dateStr . '   Heure: ' . $timeStr;
        $lines[] = 'Caisse   : Principal';
        $lines[] = $divider;

        // Column header: Article (28) | Qte (4) | Total (10)
        $lines[] = str_pad('Article', 26) . str_pad('Qte', 6, ' ', STR_PAD_LEFT) . str_pad('Total', 10, ' ', STR_PAD_LEFT);
        $lines[] = $divider;

        $grandTotal = 0;

        foreach ($receipt->sale->items as $item) {
            $name = mb_substr($item->product->name, 0, 26);
            $qty  = (string) $item->quantity;
            $tot  = number_format($item->subtotal, 2);

            $lines[] = str_pad($name, 26) . str_pad($qty, 6, ' ', STR_PAD_LEFT) . str_pad($tot, 10, ' ', STR_PAD_LEFT);

            // Print unit price on next line if multi-unit
            if ($item->quantity > 1) {
                $lines[] = '  ' . str_pad('@ ' . number_format($item->unit_price, 2) . ' /u', 40);
            }

            if (mb_strlen($item->product->name) > 26) {
                // Long name: print rest on next line
                array_splice($lines, count($lines) - ($item->quantity > 1 ? 2 : 1), 0, [
                    '  ' . mb_substr($item->product->name, 26, 40)
                ]);
            }

            $grandTotal += $item->subtotal;
        }

        $lines[] = $divider;
        $lines[] = str_pad('Sous-total :', 30) . str_pad(number_format($grandTotal, 2) . ' DH', 12, ' ', STR_PAD_LEFT);
        $lines[] = str_pad('TVA (0%)   :', 30) . str_pad('0.00 DH', 12, ' ', STR_PAD_LEFT);
        $lines[] = $doubleLine;
        $lines[] = str_pad('NET A PAYER:', 30) . str_pad(number_format($grandTotal, 2) . ' DH', 12, ' ', STR_PAD_LEFT);
        $lines[] = $doubleLine;
        $lines[] = '';
        $lines[] = str_pad('Merci pour votre visite !', $w, ' ', STR_PAD_BOTH);
        $lines[] = str_pad('Articles vendus non repris apres 7j', $w, ' ', STR_PAD_BOTH);
        $lines[] = '';
        $lines[] = '';
        $lines[] = ''; // feed lines for cut

        $textReceipt = implode("\r\n", $lines);

        // ── Write receipt to a temp .txt file ────────────────────────────────
        $tmpTxt = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'receipt_' . $receipt->id . '_' . time() . '.txt';
        file_put_contents($tmpTxt, $textReceipt);

        // ── Write a .ps1 that sends the file directly to the printer ─────────
        $tmpPs1 = str_replace('.txt', '.ps1', $tmpTxt);
        $safeTxt     = str_replace("'", "''", $tmpTxt);
        $safePrinter = str_replace("'", "''", $printerName);

        $psContent  = "Get-Content -Path '{$safeTxt}' | Out-Printer -Name '{$safePrinter}'\r\n";
        $psContent .= "Remove-Item -Force '{$safeTxt}' -ErrorAction SilentlyContinue\r\n";
        $psContent .= "Remove-Item -Force '{$tmpPs1}' -ErrorAction SilentlyContinue\r\n";

        file_put_contents($tmpPs1, $psContent);

        // ── Run the .ps1 in background (non-blocking, no window) ─────────────
        $cmd = 'powershell.exe -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File "' . $tmpPs1 . '"';
        pclose(popen('start "" /b ' . $cmd, 'r'));

        return redirect()->route('pos.index')
            ->with('success', "✓ Ticket imprimé sur {$printerName}");
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
            'from', 'to', 'totalSales', 'totalExpenses', 'totalCancellations', 'salesCount'
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
                'products.category_id',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('MAX(sales.date) as last_sale_date')
            )
            ->groupBy('products.id', 'products.name', 'products.category_id')
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
            ->map(function($row) use ($from, $to) {
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
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $cancellations = SaleCancellation::query()
            ->with(['sale', 'cancelledBy', 'authorizedBy'])
            ->whereBetween('cancelled_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('cancelled_at')
            ->paginate(20);

        return view('pos.reports.cancellations', compact('cancellations', 'from', 'to'));
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
                $item->product->increment('stock_quantity', $item->quantity);
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
            'expenses', 'status', 'from', 'to', 
            'totalPending', 'totalApproved', 'totalPaid'
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