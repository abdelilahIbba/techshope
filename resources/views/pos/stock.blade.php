<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de stock</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --panel: #ffffff;
            --line: #d7e1ef;
            --ink: #1f2a44;
            --muted: #5b6b88;
            --primary: #3b82f6;
            --danger: #dc2626;
            --warning: #f59e0b;
            --success: #10b981;
        }

        html, body { height: 100%; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .top-nav {
            display: flex;
            gap: 10px;
            padding: 10px 16px 6px;
            flex-wrap: nowrap;
            flex: 0 0 auto;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--ink);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
        }

        .nav-link.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .notification-badge {
            position: relative;
        }

        .notification-badge .badge-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc2626;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            border: 2px solid var(--bg);
        }

        .container {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            min-height: 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .page-subtitle {
            color: var(--muted);
            font-size: 14px;
        }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 9px 18px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            border-bottom: 2px solid var(--line);
        }

        .tab-btn {
            border: 0;
            background: transparent;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: var(--muted);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .table-container {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
        }

        th {
            font-weight: 700;
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--panel);
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 16px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-input,
        .form-select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-cancel {
            background: #e5e7eb;
            color: var(--ink);
        }

        .muted {
            color: var(--muted);
            font-size: 12px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <a class="nav-link" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link active" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link notification-badge" href="{{ route('pos.notifications') }}">
        Notifications stock
        @if($notificationCount > 0)
            <span class="badge-count">{{ $notificationCount }}</span>
        @endif
    </a>
    <a class="nav-link" href="{{ route('pos.settings') }}">Settings</a>
    <form method="POST" action="{{ route('logout') }}" style="display: inline; margin-left: auto;">
        @csrf
        <button type="submit" class="nav-link" style="background: #fee2e2; border-color: #fca5a5; color: #991b1b; cursor: pointer;">Déconnexion</button>
    </form>
</nav>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion de stock</h1>
            <p class="page-subtitle">Gestion des produits, fournisseurs et livraisons</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addProductModal')">+ Ajouter produit</button>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('products')">Produits</button>
        <button class="tab-btn" onclick="switchTab('suppliers')">Fournisseurs</button>
        <button class="tab-btn" onclick="switchTab('categories')">Catégories</button>
    </div>

    <div class="tab-content active" id="products-tab">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Barcode</th>
                        <th>Catégorie</th>
                        <th>Fournisseur</th>
                        <th>Stock</th>
                        <th>Prix achat</th>
                        <th>Prix vente</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td><span class="muted">{{ $product->barcode }}</span></td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->supplier->name ?? 'Non assigné' }}</td>
                            <td>{{ $product->stock_quantity }}</td>
                            <td>{{ number_format($product->purchase_price, 2) }} DZD</td>
                            <td>{{ number_format($product->sale_price, 2) }} DZD</td>
                            <td>
                                @if($product->stock_quantity <= 5)
                                    <span class="badge badge-danger">Critique</span>
                                @elseif($product->stock_quantity <= 10)
                                    <span class="badge badge-warning">Faible</span>
                                @else
                                    <span class="badge badge-success">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--muted);">Aucun produit</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-content" id="suppliers-tab">
        <div style="margin-bottom: 16px;">
            <button class="btn btn-success" onclick="openModal('addSupplierModal')">+ Ajouter fournisseur</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Produits liés</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_info ?? 'N/A' }}</td>
                            <td>{{ $supplier->products_count ?? 0 }}</td>
                            <td><span class="muted">{{ $supplier->notes ?? '-' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--muted);">Aucun fournisseur</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-content" id="categories-tab">
        <div style="margin-bottom: 16px;">
            <button class="btn btn-success" onclick="openModal('addCategoryModal')">+ Ajouter catégorie</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nom de la catégorie</th>
                        <th>Nombre de produits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td>{{ $category->products_count ?? 0 }} produit(s)</td>
                            <td>
                                <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px; margin-right: 6px;" onclick='editCategory({{ json_encode($category) }})'>✏️ Éditer</button>
                                <form method="POST" action="{{ route('pos.stock.delete-category', $category) }}" style="display: inline;" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="background: var(--danger); color: white; padding: 4px 10px; font-size: 12px;">🗑️ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--muted);">Aucune catégorie</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal" id="addProductModal">
    <div class="modal-content">
        <h3 class="modal-title">Ajouter un produit</h3>
        <form method="POST" action="{{ route('pos.stock.add-product') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nom du produit</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Barcode</label>
                <input type="text" name="barcode" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Catégorie</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Sélectionner</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fournisseur</label>
                <select name="supplier_id" class="form-select">
                    <option value="">Aucun</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantité initiale</label>
                <input type="number" name="stock_quantity" class="form-input" value="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prix d'achat (DZD)</label>
                <input type="number" step="0.01" name="purchase_price" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prix de vente (DZD)</label>
                <input type="number" step="0.01" name="sale_price" class="form-input" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="closeModal('addProductModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal" id="addSupplierModal">
    <div class="modal-content">
        <h3 class="modal-title">Ajouter un fournisseur</h3>
        <form method="POST" action="{{ route('pos.stock.add-supplier') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nom du fournisseur</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contact</label>
                <input type="text" name="contact_info" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-input">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="closeModal('addSupplierModal')">Annuler</button>
                <button type="submit" class="btn btn-success">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal" id="addCategoryModal">
    <div class="modal-content">
        <h3 class="modal-title">Ajouter une catégorie</h3>
        <form method="POST" action="{{ route('pos.stock.add-category') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nom de la catégorie</label>
                <input type="text" name="name" class="form-input" required placeholder="Ex: Produits d'entretien">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="closeModal('addCategoryModal')">Annuler</button>
                <button type="submit" class="btn btn-success">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal" id="editCategoryModal">
    <div class="modal-content">
        <h3 class="modal-title">Modifier la catégorie</h3>
        <form method="POST" id="editCategoryForm">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_category_id">
            <div class="form-group">
                <label class="form-label">Nom de la catégorie</label>
                <input type="text" name="name" id="edit_category_name" class="form-input" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="closeModal('editCategoryModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        event.target.classList.add('active');
        document.getElementById(tab + '-tab').classList.add('active');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    function editCategory(category) {
        document.getElementById('edit_category_id').value = category.id;
        document.getElementById('edit_category_name').value = category.name;
        document.getElementById('editCategoryForm').action = '/pos/stock/edit-category/' + category.id;
        openModal('editCategoryModal');
    }

    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
</script>
</body>
</html>
