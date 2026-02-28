# 📊 Système d'Historique et Rapports Avancés - Concept Professionnel

## Vue d'ensemble
Ce document présente une architecture professionnelle pour implémenter un système complet d'historique et de rapports incluant :
- **Historique des ventes par article** (suivi détaillé des produits vendus)
- **Historique des ventes par serveur/caissier** (performance des utilisateurs)
- **Gestion des dépenses** (tracking des coûts opérationnels)
- **Historique des annulations** (traçabilité des ventes annulées/remboursées)

---

## 🎯 Objectifs Business

### 1. Traçabilité Complète
- Suivre chaque transaction, modification et annulation
- Identifier les performances par vendeur
- Analyser les tendances de vente par produit
- Contrôler les dépenses opérationnelles

### 2. Analyse de Performance
- KPIs par utilisateur (CA, nombre de ventes, panier moyen)
- Top produits et produits en difficulté
- Taux d'annulation et raisons
- Rentabilité par période

### 3. Conformité et Audit
- Piste d'audit complète pour toutes les opérations
- Justification des annulations avec motifs
- Traçabilité des dépenses avec catégorisation
- Rapports exportables pour comptabilité

---

## 🗄️ Architecture de Base de Données

### 1. Modification de la table `sales`
**Problème actuel** : La table `sales` ne track pas quel utilisateur a créé la vente.

```php
// Migration: 2026_03_01_000001_add_user_tracking_to_sales.php
Schema::table('sales', function (Blueprint $table) {
    $table->foreignId('cashier_user_id')->nullable()->after('customer_id')
          ->constrained('users')->nullOnDelete();
    $table->string('status')->default('completed')->after('payment_method');
    $table->text('notes')->nullable()->after('status');
    $table->index(['cashier_user_id', 'date']);
    $table->index(['status', 'date']);
});
```

**Statuts possibles** :
- `completed` : Vente terminée et validée
- `cancelled` : Vente annulée
- `refunded` : Vente remboursée

---

### 2. Nouvelle table `sale_cancellations`
Track toutes les annulations avec justification et traçabilité.

```php
// Migration: 2026_03_01_000002_create_sale_cancellations_table.php
Schema::create('sale_cancellations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sale_id')->constrained()->onDelete('cascade');
    $table->foreignId('cancelled_by_user_id')->constrained('users')->nullOnDelete();
    $table->foreignId('authorized_by_user_id')->nullable()
          ->constrained('users')->nullOnDelete();
    $table->string('reason'); // 'client_request', 'error', 'fraud', 'return', 'other'
    $table->text('notes')->nullable();
    $table->decimal('refund_amount', 10, 2);
    $table->string('refund_method')->nullable(); // cash, card, transfer
    $table->timestamp('cancelled_at');
    $table->timestamps();
    
    $table->index(['cancelled_at', 'reason']);
    $table->index('cancelled_by_user_id');
});
```

**Workflow d'annulation** :
1. Utilisateur demande annulation → popup avec motif obligatoire
2. Si montant > seuil (ex: 10,000 DZD) → nécessite approbation manager
3. Stock des produits restauré automatiquement
4. Audit log créé automatiquement

---

### 3. Nouvelle table `expenses`
Gestion complète des dépenses opérationnelles.

```php
// Migration: 2026_03_01_000003_create_expenses_table.php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('created_by_user_id')->constrained('users')->nullOnDelete();
    $table->foreignId('approved_by_user_id')->nullable()
          ->constrained('users')->nullOnDelete();
    $table->string('category'); // 'rent', 'utilities', 'salaries', 'supplies', 'maintenance', 'other'
    $table->string('description');
    $table->decimal('amount', 10, 2);
    $table->date('expense_date');
    $table->string('payment_method'); // 'cash', 'bank_transfer', 'check', 'card'
    $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected', 'paid'
    $table->text('notes')->nullable();
    $table->string('receipt_path')->nullable(); // chemin vers scan du reçu
    $table->timestamps();
    
    $table->index(['expense_date', 'category']);
    $table->index(['status', 'created_by_user_id']);
});
```

**Catégories de dépenses** :
- **Loyer** (`rent`) : Loyer mensuel du local
- **Utilités** (`utilities`) : Électricité, eau, internet, téléphone
- **Salaires** (`salaries`) : Paies des employés
- **Fournitures** (`supplies`) : Matériel, emballages, consommables
- **Maintenance** (`maintenance`) : Réparations, entretien
- **Marketing** (`marketing`) : Publicité, promotions
- **Autre** (`other`) : Dépenses diverses

---

### 4. Table `product_sales_summary` (Optionnel - Performance)
Table de cache pour accélérer les requêtes de reporting.

```php
// Migration: 2026_03_01_000004_create_product_sales_summary_table.php
Schema::create('product_sales_summary', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->date('summary_date');
    $table->integer('quantity_sold');
    $table->decimal('total_revenue', 10, 2);
    $table->decimal('total_profit', 10, 2);
    $table->integer('transaction_count');
    $table->timestamps();
    
    $table->unique(['product_id', 'summary_date']);
    $table->index('summary_date');
});
```

**Mise à jour** : Via une commande artisan quotidienne (scheduler).

---

## 🎨 Modèles Eloquent

### 1. Modification du modèle `Sale`

```php
// app/Models/Sale.php
class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'cashier_user_id', // NOUVEAU
        'date',
        'total_amount',
        'payment_method',
        'status', // NOUVEAU
        'notes',  // NOUVEAU
    ];

    protected $casts = [
        'date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // Relations existantes
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(SaleItem::class); }
    public function receipt(): HasOne { return $this->hasOne(Receipt::class); }
    
    // NOUVELLES relations
    public function cashier(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'cashier_user_id'); 
    }
    
    public function cancellation(): HasOne 
    { 
        return $this->hasOne(SaleCancellation::class); 
    }
    
    // Scopes utiles
    public function scopeCompleted($query) 
    { 
        return $query->where('status', 'completed'); 
    }
    
    public function scopeCancelled($query) 
    { 
        return $query->where('status', 'cancelled'); 
    }
    
    public function scopeByCashier($query, int $userId) 
    { 
        return $query->where('cashier_user_id', $userId); 
    }
    
    public function scopeDateRange($query, string $from, string $to) 
    { 
        return $query->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59']); 
    }
}
```

---

### 2. Nouveau modèle `SaleCancellation`

```php
// app/Models/SaleCancellation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleCancellation extends Model
{
    protected $fillable = [
        'sale_id',
        'cancelled_by_user_id',
        'authorized_by_user_id',
        'reason',
        'notes',
        'refund_amount',
        'refund_method',
        'cancelled_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function sale(): BelongsTo 
    { 
        return $this->belongsTo(Sale::class); 
    }

    public function cancelledBy(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'cancelled_by_user_id'); 
    }

    public function authorizedBy(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'authorized_by_user_id'); 
    }

    public const REASONS = [
        'client_request' => 'Demande client',
        'error' => 'Erreur de saisie',
        'fraud' => 'Suspicion de fraude',
        'return' => 'Retour marchandise',
        'other' => 'Autre raison',
    ];
}
```

---

### 3. Nouveau modèle `Expense`

```php
// app/Models/Expense.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'created_by_user_id',
        'approved_by_user_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'status',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function createdBy(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'created_by_user_id'); 
    }

    public function approvedBy(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'approved_by_user_id'); 
    }

    public const CATEGORIES = [
        'rent' => 'Loyer',
        'utilities' => 'Utilités (électricité, eau, internet)',
        'salaries' => 'Salaires',
        'supplies' => 'Fournitures',
        'maintenance' => 'Maintenance',
        'marketing' => 'Marketing',
        'other' => 'Autre',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Approuvée',
        'rejected' => 'Rejetée',
        'paid' => 'Payée',
    ];

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopeByCategory($query, string $category) { return $query->where('category', $category); }
}
```

---

## 🛣️ Routes & Permissions

### Nouvelles routes à ajouter

```php
// routes/web.php
Route::middleware(['auth', 'check.license'])->group(function () {
    
    // ── Rapports & Historique ──
    Route::get('/pos/reports', [PosController::class, 'reports'])
        ->middleware('permission:reports.view')
        ->name('pos.reports');
    
    Route::get('/pos/reports/sales-by-product', [PosController::class, 'salesByProduct'])
        ->middleware('permission:reports.view')
        ->name('pos.reports.sales-by-product');
    
    Route::get('/pos/reports/sales-by-cashier', [PosController::class, 'salesByCashier'])
        ->middleware('permission:reports.view')
        ->name('pos.reports.sales-by-cashier');
    
    Route::get('/pos/reports/cancellations', [PosController::class, 'cancellations'])
        ->middleware('permission:reports.view')
        ->name('pos.reports.cancellations');
    
    // ── Annulations ──
    Route::post('/pos/sales/{sale}/cancel', [PosController::class, 'cancelSale'])
        ->middleware('permission:sale.cancel')
        ->name('pos.sales.cancel');
    
    Route::post('/pos/sales/{sale}/authorize-cancellation', [PosController::class, 'authorizeCancellation'])
        ->middleware('permission:sale.cancel.authorize')
        ->name('pos.sales.authorize-cancellation');
    
    // ── Dépenses ──
    Route::get('/pos/expenses', [PosController::class, 'expenses'])
        ->middleware('permission:expenses.view')
        ->name('pos.expenses');
    
    Route::post('/pos/expenses', [PosController::class, 'createExpense'])
        ->middleware('permission:expenses.create')
        ->name('pos.expenses.create');
    
    Route::put('/pos/expenses/{expense}', [PosController::class, 'updateExpense'])
        ->middleware('permission:expenses.edit')
        ->name('pos.expenses.update');
    
    Route::delete('/pos/expenses/{expense}', [PosController::class, 'deleteExpense'])
        ->middleware('permission:expenses.delete')
        ->name('pos.expenses.delete');
    
    Route::post('/pos/expenses/{expense}/approve', [PosController::class, 'approveExpense'])
        ->middleware('permission:expenses.approve')
        ->name('pos.expenses.approve');
    
    // ── Export ──
    Route::get('/pos/reports/export/sales-by-product', [PosController::class, 'exportSalesByProduct'])
        ->middleware('permission:reports.export')
        ->name('pos.reports.export.sales-by-product');
    
    Route::get('/pos/reports/export/sales-by-cashier', [PosController::class, 'exportSalesByCashier'])
        ->middleware('permission:reports.export')
        ->name('pos.reports.export.sales-by-cashier');
    
    Route::get('/pos/reports/export/expenses', [PosController::class, 'exportExpenses'])
        ->middleware('permission:reports.export')
        ->name('pos.reports.export.expenses');
});
```

### Nouvelles permissions à ajouter

```php
// Dans PosController::permissionCatalog()
private function permissionCatalog(): array
{
    return [
        // ... permissions existantes ...
        
        // Rapports avancés
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
```

---

## 🎨 Interface Utilisateur

### 1. Page "Rapports" (`resources/views/pos/reports.blade.php`)

Structure proposée :
```
┌─────────────────────────────────────────────────┐
│ Navigation (Dashboard | POS | Stock | Rapports) │
├─────────────────────────────────────────────────┤
│                                                 │
│  📊 Rapports & Historique                      │
│                                                 │
│  ┌──────────────┬──────────────┬─────────────┐ │
│  │ Ventes par   │ Ventes par   │ Dépenses    │ │
│  │ Article      │ Caissier     │             │ │
│  └──────────────┴──────────────┴─────────────┘ │
│                                                 │
│  ┌──────────────┬──────────────┐               │
│  │ Annulations  │ Profit/Perte │               │
│  └──────────────┴──────────────┘               │
│                                                 │
│  Filtres: [Date début] [Date fin] [Filtrer]    │
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │  📈 Graphique CA par jour               │   │
│  │  ████████████████░░░░░░░░░░ 450,000 DZD│   │
│  └─────────────────────────────────────────┘   │
│                                                 │
└─────────────────────────────────────────────────┘
```

### 2. Rapport "Ventes par Article"

Tableau détaillé :
| Article | Catégorie | Qté Vendue | CA Total | Prix Moyen | Dernière Vente |
|---------|-----------|------------|----------|------------|----------------|
| Product A | Cat 1 | 150 | 45,000 | 300 | 28/02/2026 |

Fonctionnalités :
- Tri par colonne
- Recherche par nom produit
- Filtrage par catégorie
- Export Excel/PDF
- Graphique des top 10 produits

### 3. Rapport "Ventes par Caissier"

KPIs par utilisateur :
```
┌────────────────────────────────────────┐
│ Caissier: Ahmed Ben Ali                │
├────────────────────────────────────────┤
│ Nombre de ventes: 45                   │
│ CA généré: 125,000 DZD                 │
│ Panier moyen: 2,777 DZD                │
│ Taux d'annulation: 2.2%                │
│ Performance: ⭐⭐⭐⭐ (Excellent)        │
└────────────────────────────────────────┘
```

### 4. Gestion des Dépenses

Interface de création :
```
┌─────────────────────────────────────┐
│ Nouvelle Dépense                     │
├─────────────────────────────────────┤
│ Catégorie: [Dropdown ▼]             │
│ Description: [________________]      │
│ Montant: [________] DZD              │
│ Date: [01/03/2026]                   │
│ Méthode: ○ Espèces ○ Virement ○ CB  │
│ Reçu: [📎 Télécharger fichier]      │
│ Notes: [_____________________]       │
│                                      │
│         [Annuler]  [Enregistrer]    │
└─────────────────────────────────────┘
```

Workflow d'approbation :
1. Employé crée dépense → `status = 'pending'`
2. Manager reçoit notification
3. Manager approuve/rejette
4. Si approuvé → Passe à `paid` après paiement

### 5. Modal d'Annulation de Vente

```
┌──────────────────────────────────────┐
│ ⚠️ Annuler la vente #1234            │
├──────────────────────────────────────┤
│ Montant: 5,500 DZD                   │
│ Client: Mohamed Saadi                │
│                                      │
│ Motif d'annulation *                 │
│ ○ Demande client                     │
│ ○ Erreur de saisie                   │
│ ○ Suspicion fraude                   │
│ ○ Retour marchandise                 │
│ ○ Autre                              │
│                                      │
│ Détails: [___________________]       │
│                                      │
│ Montant remboursé: [5,500] DZD       │
│ Méthode: [Espèces ▼]                 │
│                                      │
│ ⚠️ Cette action est irréversible     │
│                                      │
│    [Annuler]      [Confirmer]        │
└──────────────────────────────────────┘
```

---

## ⚙️ Logique Métier (Controllers)

### 1. Méthode `PosController::cancelSale()`

```php
public function cancelSale(Request $request, Sale $sale): RedirectResponse
{
    $validated = $request->validate([
        'reason' => 'required|in:client_request,error,fraud,return,other',
        'notes' => 'nullable|string|max:500',
        'refund_amount' => 'required|numeric|min:0|max:' . $sale->total_amount,
        'refund_method' => 'required|in:cash,card,transfer',
    ]);

    // Vérifier si annulation déjà effectuée
    if ($sale->status === 'cancelled') {
        return back()->with('error', 'Cette vente est déjà annulée.');
    }

    // Vérifier autorisation si montant élevé
    $requiresAuth = $sale->total_amount > 10000;
    if ($requiresAuth && !$request->user()->can('sale.cancel.authorize')) {
        // Créer demande d'autorisation
        PendingCancellation::create([
            'sale_id' => $sale->id,
            'requested_by_user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
        ]);
        return back()->with('success', 'Demande d\'annulation envoyée au manager.');
    }

    DB::transaction(function () use ($sale, $validated) {
        // 1. Créer entrée d'annulation
        SaleCancellation::create([
            'sale_id' => $sale->id,
            'cancelled_by_user_id' => Auth::id(),
            'authorized_by_user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
            'refund_amount' => $validated['refund_amount'],
            'refund_method' => $validated['refund_method'],
            'cancelled_at' => now(),
        ]);

        // 2. Mettre à jour statut vente
        $sale->update(['status' => 'cancelled']);

        // 3. Restaurer le stock
        foreach ($sale->items as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
        }

        // 4. Audit log
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
```

### 2. Méthode `PosController::salesByCashier()`

```php
public function salesByCashier(Request $request): View
{
    $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
    $to = $request->input('to', now()->format('Y-m-d'));

    $salesData = Sale::query()
        ->with('cashier:id,name')
        ->dateRange($from, $to)
        ->completed()
        ->select('cashier_user_id', 
                 DB::raw('COUNT(*) as total_sales'),
                 DB::raw('SUM(total_amount) as total_revenue'),
                 DB::raw('AVG(total_amount) as avg_basket'))
        ->groupBy('cashier_user_id')
        ->orderByDesc('total_revenue')
        ->get()
        ->map(function($row) {
            // Calculer taux d'annulation
            $cancellations = Sale::cancelled()
                ->where('cashier_user_id', $row->cashier_user_id)
                ->count();
            
            $row->cancellation_rate = $row->total_sales > 0 
                ? round(($cancellations / $row->total_sales) * 100, 1) 
                : 0;
            
            return $row;
        });

    return view('pos.reports.sales-by-cashier', compact('salesData', 'from', 'to'));
}
```

### 3. Méthode `PosController::createExpense()`

```php
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

    // Upload du reçu
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
        'notes' => $validated['notes'],
        'receipt_path' => $receiptPath,
        'status' => 'pending',
    ]);

    $this->writeAudit('expense.created', Expense::class, $expense->id, [
        'category' => $validated['category'],
        'amount' => $validated['amount'],
    ]);

    return back()->with('success', 'Dépense enregistrée. En attente d\'approbation.');
}
```

---

## 📊 Dashboard Widgets (Settings Page)

Ajouter ces widgets à la page Settings :

### Widget "Historique"

```blade
<!-- Dans resources/views/pos/settings.blade.php -->
<div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6">
    <h3 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Rapports & Historique
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('pos.reports') }}" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            <div class="bg-blue-100 p-2 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <div class="font-semibold text-slate-900">Ventes par Article</div>
                <div class="text-sm text-slate-500">Top produits & statistiques</div>
            </div>
        </a>
        
        <a href="{{ route('pos.reports.sales-by-cashier') }}" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            <div class="bg-green-100 p-2 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <div class="font-semibold text-slate-900">Ventes par Caissier</div>
                <div class="text-sm text-slate-500">Performance des utilisateurs</div>
            </div>
        </a>
        
        <a href="{{ route('pos.expenses') }}" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            <div class="bg-red-100 p-2 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="font-semibold text-slate-900">Dépenses</div>
                <div class="text-sm text-slate-500">Suivi des coûts opérationnels</div>
            </div>
        </a>
        
        <a href="{{ route('pos.reports.cancellations') }}" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            <div class="bg-yellow-100 p-2 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <div class="font-semibold text-slate-900">Annulations</div>
                <div class="text-sm text-slate-500">Historique des ventes annulées</div>
            </div>
        </a>
    </div>
</div>
```

---

## 🔄 Workflow Complet

### Scénario 1 : Vente Normale
1. **Caissier** scanne produits dans POS
2. Valide vente → `Sale` créée avec `cashier_user_id = Auth::id()`
3. Stock décrémenté automatiquement
4. Reçu imprimé/envoyé
5. **Audit log** créé : `sale.completed`

### Scénario 2 : Annulation Simple (< 10,000 DZD)
1. Caissier trouve la vente dans Dashboard
2. Clique "Annuler vente" → Modal s'ouvre
3. Sélectionne motif + détails
4. Confirme → Transaction DB :
   - Crée `SaleCancellation`
   - Update `sales.status = 'cancelled'`
   - Restaure stock
   - Audit log
5. Notification : "Vente #1234 annulée"

### Scénario 3 : Annulation avec Autorisation (≥ 10,000 DZD)
1. Caissier demande annulation
2. Système crée `PendingCancellation`
3. Manager reçoit notification
4. Manager examine → Approuve/Rejette
5. Si approuvé → Même workflow que Scénario 2
6. Si rejeté → Notification au caissier

### Scénario 4 : Enregistrement Dépense
1. Utilisateur va dans "Dépenses"
2. Clique "Nouvelle dépense"
3. Remplit formulaire + upload reçu
4. Soumet → `Expense` créée avec `status = 'pending'`
5. Manager approuve → `status = 'approved'`
6. Après paiement → `status = 'paid'`

---

## 📈 KPIs & Métriques Clés

### Tableau de Bord Exécutif (pour Admin/Manager)

```
┌──────────────────────────────────────────────────────┐
│ 📊 Vue d'ensemble - Février 2026                     │
├──────────────────────────────────────────────────────┤
│ CA Total: 1,245,000 DZD  ↑ +15% vs Janvier          │
│ Dépenses: -345,000 DZD   ↓ -5% vs Janvier           │
│ Profit Net: 900,000 DZD  ↑ +25% vs Janvier          │
│ Marge: 72.3%                                         │
├──────────────────────────────────────────────────────┤
│ Top 3 Caissiers (CA généré):                         │
│ 1. Ahmed Ben Ali      425,000 DZD (34%)              │
│ 2. Fatima Zohra       380,000 DZD (31%)              │
│ 3. Mohamed Saadi      295,000 DZD (24%)              │
├──────────────────────────────────────────────────────┤
│ Top 3 Produits vendus:                               │
│ 1. Product A (Cat 1)  150 unités  45,000 DZD         │
│ 2. Product B (Cat 2)  120 unités  36,000 DZD         │
│ 3. Product C (Cat 1)  95 unités   28,500 DZD         │
├──────────────────────────────────────────────────────┤
│ ⚠️ Alertes:                                          │
│ • 12 produits en rupture de stock                    │
│ • 5 dépenses en attente d'approbation                │
│ • Taux d'annulation: 3.2% (normal: <5%)              │
└──────────────────────────────────────────────────────┘
```

---

## 🚀 Plan d'Implémentation

### Phase 1 : Base de Données (1-2 jours)
- [ ] Créer migration `add_user_tracking_to_sales`
- [ ] Créer migration `create_sale_cancellations_table`
- [ ] Créer migration `create_expenses_table`
- [ ] Créer modèles Eloquent (SaleCancellation, Expense)
- [ ] Mettre à jour modèle Sale
- [ ] Tester migrations sur DB de dev

### Phase 2 : Logique Métier (2-3 jours)
- [ ] Implémenter méthodes dans PosController :
  - `cancelSale()`
  - `authorizeCancellation()`
  - `salesByCashier()`
  - `salesByProduct()`
  - `createExpense()`
  - `approveExpense()`
- [ ] Ajouter permissions au catalogue
- [ ] Mettre à jour seeds pour permissions

### Phase 3 : Interface (3-4 jours)
- [ ] Créer `reports.blade.php` (page principale)
- [ ] Créer `sales-by-cashier.blade.php`
- [ ] Créer `sales-by-product.blade.php`
- [ ] Créer `expenses.blade.php`
- [ ] Créer `cancellations.blade.php`
- [ ] Ajouter modals (annulation, nouvelle dépense)
- [ ] Intégrer widgets dans Settings page

### Phase 4 : Exports & Rapports (1-2 jours)
- [ ] Implémenter export Excel (PHPSpreadsheet)
- [ ] Implémenter export PDF (DomPDF)
- [ ] Créer templates d'impression

### Phase 5 : Tests & Déploiement (2 jours)
- [ ] Tests unitaires (models, scopes)
- [ ] Tests fonctionnels (annulation, dépenses)
- [ ] Tests d'intégration
- [ ] Déploiement en production
- [ ] Formation des utilisateurs

**Total estimé** : 9-13 jours de développement

---

## 💡 Améliorations Futures

### Court Terme (1-2 mois)
- **Notifications Push** : Alertes temps réel pour approbations
- **Graphiques interactifs** : Chart.js pour visualisations
- **Recherche avancée** : Filtres multicritères dans rapports
- **Exports planifiés** : Rapports automatiques par email

### Moyen Terme (3-6 mois)
- **BI Dashboard** : Tableaux de bord personnalisables
- **Prédictions ventes** : ML pour anticiper tendances
- **Gestion budgétaire** : Budgets par catégorie de dépense
- **Inventaire prédictif** : Alertes avant rupture stock

### Long Terme (6-12 mois)
- **API externe** : Intégration comptabilité (Sage, QuickBooks)
- **Mobile App** : Application pour managers (React Native)
- **Analyse comportementale** : Patterns d'achat clients
- **Multi-magasin** : Consolidation de plusieurs points de vente

---

## 🔒 Sécurité & Conformité

### Contrôles d'Accès
- **RBAC strict** : Permissions granulaires par rôle
- **Audit trail** : Chaque action loguée avec acteur
- **Authentification 2FA** : Pour opérations sensibles
- **Sessions sécurisées** : Timeouts automatiques

### Protection des Données
- **Encryption** : Données sensibles chiffrées (montants, notes)
- **Backups quotidiens** : Automatiques avec rétention 30 jours
- **RGPD compliant** : Anonymisation possible des données clients
- **Logs immuables** : Audit logs non modifiables

### Validations Business
- **Montants cohérents** : Remboursement ≤ montant vente
- **Dates valides** : Pas de ventes futures, dépenses cohérentes
- **Stock physique** : Vérification avant annulation
- **Double approbation** : Pour dépenses > seuil

---

## 📞 Support & Documentation

### Pour les Utilisateurs
- **Guide utilisateur** : PDF avec screenshots
- **Vidéos tutoriels** : Screencast de chaque workflow
- **FAQ** : Questions fréquentes
- **Hotline** : Support technique dédié

### Pour les Développeurs
- **Documentation API** : Endpoints et payloads
- **Schéma DB** : Diagrammes ER
- **Code comments** : PHPDoc complet
- **Tests** : Coverage > 80%

---

## ✅ Checklist de Validation

Avant mise en production :
- [ ] Toutes les migrations passent sans erreur
- [ ] Seeds créent les données de test correctement
- [ ] Permissions configurées pour tous les rôles
- [ ] Tests manuels de tous les workflows
- [ ] Export Excel/PDF fonctionnel
- [ ] UI responsive (mobile, tablette, desktop)
- [ ] Performance acceptable (< 2s chargement pages)
- [ ] Backup DB configuré et testé
- [ ] Formation admin/manager effectuée
- [ ] Documentation utilisateur finalisée

---

**Auteur** : Système d'Architecture POS  
**Date** : 28 Février 2026  
**Version** : 1.0  
**Statut** : Prêt pour implémentation
