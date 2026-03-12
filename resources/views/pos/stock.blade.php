<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de stock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Maintenance de la logique JavaScript pour l'affichage/masquage des onglets et fenêtres */
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .modal { display: none; }
        .modal.active { display: flex; animation: fadeIn 0.2s ease-out; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Personnalisation de la barre de défilement */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden antialiased">

<nav class="top-nav flex items-center gap-2 p-4 bg-white border-b border-slate-200 shadow-sm shrink-0">
    <a href="{{ route('pos.dashboard') }}" style="display: flex; align-items: center; gap: 0px; text-decoration: none; margin-right: 20px;">
        <img src="{{ asset('images/logo.png') }}" onerror="this.src='{{ asset('logo.svg') }}'" alt="TickDroguerie" style="width: 150px; height: auto; margin-right: -10px;">
        <div style="font-family: 'Montserrat', sans-serif; font-size: 22px; font-weight: 900; line-height: 1;">
            <span style="color: #F27036;">Tick</span><span style="color: #1A476F;">Droguerie</span>
        </div>
    </a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.dashboard') }}">Tableau de bord</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link active px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-blue-600 text-white shadow-md border-blue-600" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link notification-badge relative px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.notifications') }}">
        Notifications stock
        @if($notificationCount > 0)
            <span class="badge-count absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-sm">{{ $notificationCount }}</span>
        @endif
    </a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.settings') }}">Paramètres</a>

    @include('partials.license-badge')

    <form method="POST" action="{{ route('logout') }}" >
        @csrf
        <button type="submit" class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 cursor-pointer flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Déconnexion
        </button>
    </form>
</nav>

<div class="container flex-1 p-6 lg:p-8 overflow-y-auto w-full max-w-7xl mx-auto">
    
    <div class="page-header flex justify-between items-start mb-6">
        <div>
            <h1 class="page-title text-3xl font-extrabold text-slate-900 tracking-tight">Gestion de stock</h1>
            <p class="page-subtitle text-slate-500 mt-1 font-medium">Gestion des produits, fournisseurs et livraisons</p>
        </div>
        <button class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm px-6 py-3 shadow-sm transition-all flex items-center gap-2" onclick="openModal('addProductModal')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
            Ajouter produit
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success bg-emerald-50 text-emerald-700 border border-emerald-200 p-4 rounded-xl mb-6 font-semibold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error bg-red-50 text-red-700 border border-red-200 p-4 rounded-xl mb-6 font-semibold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="tabs flex gap-6 mb-6 border-b border-slate-200">
        <button class="tab-btn active pb-3 text-sm font-bold text-slate-500 border-b-2 border-transparent hover:text-slate-800 transition-colors [&.active]:text-blue-600 [&.active]:border-blue-600" onclick="switchTab('products')">Produits</button>
        <button class="tab-btn pb-3 text-sm font-bold text-slate-500 border-b-2 border-transparent hover:text-slate-800 transition-colors [&.active]:text-blue-600 [&.active]:border-blue-600" onclick="switchTab('suppliers')">Fournisseurs</button>
        <button class="tab-btn pb-3 text-sm font-bold text-slate-500 border-b-2 border-transparent hover:text-slate-800 transition-colors [&.active]:text-blue-600 [&.active]:border-blue-600" onclick="switchTab('categories')">Catégories</button>
    </div>

    <div class="tab-content active" id="products-tab">
        <div class="mb-6 flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <!--<input type="text" id="searchProduct" class="pl-10 form-input bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none transition-all" placeholder="Rechercher un produit (nom, barcode)..." onkeyup="filterProducts()">-->
            </div>
            <select id="filterCategory" class="form-select bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full md:w-[200px] p-2.5 outline-none" onchange="filterProducts()">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select id="filterStatus" class="form-select bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full md:w-[150px] p-2.5 outline-none" onchange="filterProducts()">
                <option value="">Tous les statuts</option>
                <option value="OK">OK</option>
                <option value="Faible">Faible</option>
                <option value="Critique">Critique</option>
            </select>
        </div>

        <div class="table-container bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table id="productsTable" class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold">Produit</th>
                            <th class="px-6 py-4 font-bold">Barcode</th>
                            <th class="px-6 py-4 font-bold">Type</th>
                            <th class="px-6 py-4 font-bold">Catégorie</th>
                            <th class="px-6 py-4 font-bold">Fournisseur</th>
                            <th class="px-6 py-4 font-bold">Stock</th>
                            <th class="px-6 py-4 font-bold">Prix achat</th>
                            <th class="px-6 py-4 font-bold">Prix vente</th>
                            <th class="px-6 py-4 font-bold">Statut</th>
                            <th class="px-6 py-4 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr ondblclick='showProductDetails({{ json_encode($product) }})' class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0 group">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $product->name }}</td>
                                <td class="px-6 py-4"><span class="text-slate-400 font-mono tracking-wider text-xs">{{ $product->barcode }}</span></td>
                                <td class="px-6 py-4">
                                    @if(($product->barcode_type ?? 'internal') === 'scanned')
                                        <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-1 rounded-md text-xs font-semibold border border-blue-100" title="Barcode scanné depuis le produit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                            Scanné
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-violet-50 text-violet-700 px-2 py-1 rounded-md text-xs font-semibold border border-violet-100" title="Barcode généré automatiquement">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                            Interne
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md text-xs font-semibold">{{ $product->category->name ?? 'N/A' }}</span></td>
                                <td class="px-6 py-4">{{ $product->supplier->name ?? 'Non assigné' }}</td>
                                <td class="px-6 py-4 font-extrabold text-slate-900">
                                    @if($product->sell_by_length)
                                        {{ number_format((float)$product->stock_quantity, 3) }}
                                        <span class="ml-1 text-xs font-bold text-teal-600 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded">{{ $product->length_unit }}</span>
                                    @else
                                        {{ (int) $product->stock_quantity }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ number_format($product->purchase_price, 2) }} {{ $currencySymbol }}</td>
                                <td class="px-6 py-4 font-bold text-blue-600">{{ number_format($product->sale_price, 2) }} {{ $currencySymbol }}</td>
                                <td class="px-6 py-4">
                                    @if($product->stock_quantity <= 5)
                                        <span class="badge bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase">Critique</span>
                                    @elseif($product->stock_quantity <= 10)
                                        <span class="badge bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase">Faible</span>
                                    @else
                                        <span class="badge bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase">OK</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2 justify-end opacity-70 group-hover:opacity-100 transition-opacity">
                                        <button class="text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 p-2 rounded-lg transition-all shadow-sm group/btn relative" title="Éditer" onclick='editProduct({{ json_encode($product) }})'>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        
                                        <button class="text-amber-600 bg-amber-50 hover:bg-amber-100 hover:text-amber-700 p-2 rounded-lg transition-all shadow-sm group/btn" title="Ajuster Stock" onclick='adjustStock({{ json_encode($product) }})'>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                        </button>
                                        
                                        <form method="POST" action="{{ route('pos.stock.delete-product', $product) }}" class="inline" onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 bg-red-50 hover:bg-red-500 hover:text-white p-2 rounded-lg transition-all shadow-sm group/btn" title="Supprimer">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-slate-500 font-medium">Aucun produit trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Print Barcode Modal -->
<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="printBarcodeModal">
    <div class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-3">Imprimer tickets - <span id="print_product_name"></span></h3>
        <form id="printBarcodeForm" onsubmit="submitPrintForm(event)">
            <input type="hidden" id="print_product_id">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre de tickets</label>
                <input type="text" name="quantity" id="print_quantity_input" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg p-2.5 outline-none cursor-pointer" value="1" readonly onclick="openNumericKeyboard('print_quantity_input')">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('printBarcodeModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Imprimer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="productDetailsModal">
    <div class="modal-content bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <button type="button" class="absolute top-3 right-3 text-slate-400 hover:text-slate-700 text-xl" onclick="closeModal('productDetailsModal')">&times;</button>
        <h3 class="text-xl font-bold text-slate-900 mb-2" id="detail_product_name"></h3>
        <p class="text-sm text-slate-600 mb-3" id="detail_product_barcode"></p>
        <p class="text-sm text-slate-600 mb-3">Stock: <span id="detail_product_stock"></span></p>
        <div class="flex gap-2">
            <button type="button" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg" onclick="openPrintFromDetails()">Imprimer barcode</button>
            <button type="button" class="px-4 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg" onclick="closeModal('productDetailsModal')">Fermer</button>
        </div>
    </div>
</div>

    <div class="tab-content" id="suppliers-tab">
        <div class="mb-6 flex justify-between items-center">
             <h2 class="text-lg font-bold text-slate-900">Liste des Fournisseurs</h2>
            <button class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl text-sm px-6 py-2.5 shadow-sm transition-all flex items-center gap-2" onclick="openModal('addSupplierModal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                Ajouter fournisseur
            </button>
        </div>
        <div class="table-container bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold">Nom</th>
                            <th class="px-6 py-4 font-bold">Contact</th>
                            <th class="px-6 py-4 font-bold text-center">Produits liés</th>
                            <th class="px-6 py-4 font-bold">Notes</th>
                            <th class="px-6 py-4 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0 group">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $supplier->name }}</td>
                                <td class="px-6 py-4">{{ $supplier->contact_info ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-bold text-xs">{{ $supplier->products_count ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-400 italic text-xs">{{ $supplier->notes ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2 justify-end opacity-70 group-hover:opacity-100 transition-opacity">
                                        <button class="flex items-center gap-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 font-semibold text-xs px-3 py-2 rounded-lg transition-all shadow-sm" onclick='editSupplier({{ json_encode($supplier) }})'>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            Éditer
                                        </button>
                                        <form method="POST" action="{{ route('pos.stock.delete-supplier', $supplier) }}" class="inline" onsubmit="return confirm('Supprimer ce fournisseur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex items-center gap-1.5 text-red-500 bg-red-50 hover:bg-red-500 hover:text-white font-semibold text-xs px-3 py-2 rounded-lg transition-all shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 font-medium">Aucun fournisseur</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-content" id="categories-tab">
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-900">Liste des Catégories</h2>
            <button class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl text-sm px-6 py-2.5 shadow-sm transition-all flex items-center gap-2" onclick="openModal('addCategoryModal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                Ajouter catégorie
            </button>
        </div>
        <div class="table-container bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold">Nom de la catégorie</th>
                            <th class="px-6 py-4 font-bold text-center">Nombre de produits</th>
                            <th class="px-6 py-4 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0 group">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-bold text-xs">{{ $category->products_count ?? 0 }} produit(s)</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2 opacity-70 group-hover:opacity-100 transition-opacity">
                                        <button class="flex items-center gap-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 font-semibold text-xs px-3 py-2 rounded-lg transition-all shadow-sm" onclick='editCategory({{ json_encode($category) }})'>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            Éditer
                                        </button>
                                        <form method="POST" action="{{ route('pos.stock.delete-category', $category) }}" class="inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex items-center gap-1.5 text-red-500 bg-red-50 hover:bg-red-500 hover:text-white font-semibold text-xs px-3 py-2 rounded-lg transition-all shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-500 font-medium">Aucune catégorie</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="addProductModal">
    <div class="modal-content bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Ajouter un produit</h3>
        <form method="POST" action="{{ route('pos.stock.add-product') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom du produit</label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none" required>
            </div>
            {{-- ── Barcode Type Selector ── --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Type de Barcode</label>
                <div class="flex gap-2 mb-3" id="barcode_type_toggle">
                    <label id="btn_scanned" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-4 py-3 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 font-semibold text-sm transition-all">
                        <input type="radio" name="barcode_type" value="scanned" class="sr-only" checked onchange="onBarcodeTypeChange('scanned')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        Scanné (Produit avec barcode)
                    </label>
                    <label id="btn_internal" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm transition-all">
                        <input type="radio" name="barcode_type" value="internal" class="sr-only" onchange="onBarcodeTypeChange('internal')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        Interne (Pas de barcode)
                    </label>
                </div>
                {{-- Barcode input: only shown for 'scanned' type --}}
                <div id="barcode_input_wrapper">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Scanner ou saisir le barcode du produit</label>
                    <input type="text" name="barcode" id="add_barcode_input" class="w-full bg-slate-50 border border-blue-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none font-mono" placeholder="Ex: 6111250450232" autofocus>
                    <p class="text-xs text-slate-400 mt-1">Utilisez votre lecteur de codes-barres ou saisissez manuellement.</p>
                </div>
                <div id="barcode_auto_notice" class="hidden">
                    <div class="flex items-center gap-2 bg-violet-50 border border-violet-200 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <span class="text-sm text-violet-700 font-semibold">Un barcode unique sera généré automatiquement pour ce produit.</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Catégorie</label>
                    <select name="category_id" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none" required>
                        <option value="">Sélectionner</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fournisseur</label>
                    <select name="supplier_id" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none">
                        <option value="">Aucun</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- ── Unit de vente ── --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Unité de vente</label>
                <div class="flex gap-2" id="unit_type_toggle">
                    <label id="btn_pcs" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-4 py-3 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 font-semibold text-sm transition-all">
                        <input type="radio" name="sell_by_length" id="sell_by_length_no" value="0" class="sr-only" checked onchange="onUnitTypeChange(false)">
                        📦 Par pièce
                    </label>
                    <label id="btn_length" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm transition-all">
                        <input type="radio" name="sell_by_length" id="sell_by_length_yes" value="1" class="sr-only" onchange="onUnitTypeChange(true)">
                        📏 Par longueur (m/cm)
                    </label>
                </div>
                {{-- Length unit selector - only shown when sell_by_length is true --}}
                <div id="length_unit_selector" class="hidden mt-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Unité de longueur</label>
                    <div class="flex gap-2">
                        <label class="flex-1 flex items-center justify-center gap-1 cursor-pointer px-3 py-2 rounded-lg border-2 border-teal-500 bg-teal-50 text-teal-700 font-bold text-sm" id="btn_unit_m">
                            <input type="radio" name="length_unit" value="m" class="sr-only" checked onchange="setLengthBtnActive('m')"> 📐 Mètre (m)
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-1 cursor-pointer px-3 py-2 rounded-lg border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm" id="btn_unit_cm">
                            <input type="radio" name="length_unit" value="cm" class="sr-only" onchange="setLengthBtnActive('cm')"> 📏 Centimètre (cm)
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Quantité initiale <span id="add_qty_unit_label" class="text-xs font-normal text-slate-400"></span></label>
                <input type="number" step="0.001" name="stock_quantity" id="stock_quantity_input" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none" value="0" required min="0">
            </div>
            <!-- Numeric Keyboard Modal -->
            <div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="numericKeyboardModal">
                <div class="modal-content bg-white w-full max-w-xs rounded-2xl shadow-xl border border-slate-200 p-6 flex flex-col items-center gap-4 relative">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Entrer la quantité</h3>
                    <input type="text" id="numericKeyboardInput" class="w-full text-center text-2xl font-bold bg-slate-100 border border-slate-300 rounded-lg p-2.5 mb-2" readonly>
                    <div class="grid grid-cols-3 gap-2 w-full">
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('1')">1</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('2')">2</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('3')">3</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('4')">4</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('5')">5</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('6')">6</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('7')">7</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('8')">8</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('9')">9</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="clearNumericKey()">C</button>
                        <button type="button" class="bg-slate-200 hover:bg-blue-200 text-xl font-bold rounded-lg p-3" onclick="appendNumericKey('0')">0</button>
                        <button type="button" class="bg-blue-600 text-white font-bold rounded-lg p-3" onclick="confirmNumericKey()">OK</button>
                    </div>
                    <button type="button" class="absolute top-2 right-2 text-slate-400 hover:text-slate-700 text-xl" onclick="closeModal('numericKeyboardModal')">&times;</button>
                </div>
            </div>
            <script>
                let numericTargetInput = null;
                const azertyKeyMap = {
                    '&': '1', 'é': '2', '"': '3', "'": '4', '(': '5',
                    '-': '6', 'è': '7', '_': '8', 'ç': '9', 'à': '0',
                    ')': '°', '=': '+', '§': '!'
                };

                function openNumericKeyboard(inputId) {
                    numericTargetInput = document.getElementById(inputId);
                    document.getElementById('numericKeyboardInput').value = numericTargetInput.value || '';
                    document.getElementById('numericKeyboardModal').classList.add('active');
                    
                    // Add global keydown listener when modal is open
                    document.addEventListener('keydown', handleKeydown);
                }

                function handleKeydown(e) {
                    const modal = document.getElementById('numericKeyboardModal');
                    if (!modal.classList.contains('active')) return;

                    let key = e.key;
                    
                    // Handle AZERTY mapping
                    if (azertyKeyMap[key]) {
                        key = azertyKeyMap[key];
                    }

                    if (key >= '0' && key <= '9') {
                        appendNumericKey(key);
                    } else if (key === 'Backspace') {
                        const input = document.getElementById('numericKeyboardInput');
                        input.value = input.value.slice(0, -1);
                    } else if (key === 'Enter') {
                        confirmNumericKey();
                    } else if (key === 'Escape') {
                        closeModal('numericKeyboardModal');
                    }
                }

                function appendNumericKey(digit) {
                    const input = document.getElementById('numericKeyboardInput');
                    if (input.value.length < 6) {
                        input.value = (input.value === '0') ? digit : input.value + digit;
                    }
                }
                function clearNumericKey() {
                    document.getElementById('numericKeyboardInput').value = '';
                }
                function confirmNumericKey() {
                    if (numericTargetInput) {
                        let val = document.getElementById('numericKeyboardInput').value;
                        if (val === '' || isNaN(val)) val = '0';
                        numericTargetInput.value = val;
                    }
                    closeModal('numericKeyboardModal');
                }
                
                // Override closeModal to remove listener
                const originalCloseModal = closeModal;
                window.closeModal = function(modalId) {
                    if (modalId === 'numericKeyboardModal') {
                        document.removeEventListener('keydown', handleKeydown);
                    }
                    originalCloseModal(modalId);
                };
            </script>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prix d'achat ({{ $currencySymbol }})</label>
                    <input type="number" step="0.01" name="purchase_price" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prix vente ({{ $currencySymbol }})</label>
                    <input type="number" step="0.01" name="sale_price" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none" required>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('addProductModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="editProductModal">
    <div class="modal-content bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Modifier le produit</h3>
        <form method="POST" id="editProductForm" class="flex flex-col gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_product_id">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom du produit</label>
                <input type="text" name="name" id="edit_product_name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Type de Barcode</label>
                <div class="flex gap-2 mb-3">
                    <label id="edit_btn_scanned" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-3 py-2.5 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm transition-all">
                        <input type="radio" name="barcode_type" id="edit_type_scanned" value="scanned" class="sr-only" onchange="onEditBarcodeTypeChange('scanned')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        Scanné
                    </label>
                    <label id="edit_btn_internal" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-3 py-2.5 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm transition-all">
                        <input type="radio" name="barcode_type" id="edit_type_internal" value="internal" class="sr-only" onchange="onEditBarcodeTypeChange('internal')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        Interne
                    </label>
                </div>
                <div id="edit_barcode_input_wrapper">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Barcode du produit</label>
                    <input type="text" name="barcode" id="edit_product_barcode" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none font-mono" required>
                </div>
                <div id="edit_barcode_auto_notice" class="hidden">
                    <div class="flex items-center gap-2 bg-violet-50 border border-violet-200 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <span class="text-sm text-violet-700 font-semibold">Un nouveau barcode interne sera généré automatiquement.</span>
                    </div>
                    <input type="hidden" name="barcode" id="edit_product_barcode_hidden" value="">
                </div>
            </div>

            {{-- ── Unité de vente (Edit) ── --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Unité de vente</label>
                <div class="flex gap-2">
                    <label id="edit_btn_pcs" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-4 py-3 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 font-semibold text-sm transition-all">
                        <input type="radio" name="sell_by_length" id="edit_sell_by_length_no" value="0" class="sr-only" checked onchange="onEditUnitTypeChange(false)">
                        📦 Par pièce
                    </label>
                    <label id="edit_btn_length" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm transition-all">
                        <input type="radio" name="sell_by_length" id="edit_sell_by_length_yes" value="1" class="sr-only" onchange="onEditUnitTypeChange(true)">
                        📏 Par longueur (m/cm)
                    </label>
                </div>
                <div id="edit_length_unit_selector" class="hidden mt-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Unité de longueur</label>
                    <div class="flex gap-2">
                        <label class="flex-1 flex items-center justify-center gap-1 cursor-pointer px-3 py-2 rounded-lg border-2 border-teal-500 bg-teal-50 text-teal-700 font-bold text-sm" id="edit_btn_unit_m">
                            <input type="radio" name="edit_length_unit" value="m" class="sr-only" checked onchange="setEditLengthBtnActive('m')"> 📐 Mètre (m)
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-1 cursor-pointer px-3 py-2 rounded-lg border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm" id="edit_btn_unit_cm">
                            <input type="radio" name="edit_length_unit" value="cm" class="sr-only" onchange="setEditLengthBtnActive('cm')"> 📏 Centimètre (cm)
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Catégorie</label>
                    <select name="category_id" id="edit_product_category" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
                        <option value="">Sélectionner</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fournisseur</label>
                    <select name="supplier_id" id="edit_product_supplier" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none">
                        <option value="">Aucun</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prix d'achat</label>
                    <input type="number" step="0.01" name="purchase_price" id="edit_product_purchase_price" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prix de vente</label>
                    <input type="number" step="0.01" name="sale_price" id="edit_product_sale_price" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('editProductModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="adjustStockModal">
    <div class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Ajuster le stock</h3>
        <form method="POST" id="adjustStockForm" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" id="adjust_product_id">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Produit</label>
                <input type="text" id="adjust_product_name" class="w-full bg-slate-100 border border-slate-200 text-slate-600 font-bold text-sm rounded-lg p-2.5 cursor-not-allowed" readonly>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Stock actuel</label>
                <input type="text" id="adjust_current_stock" class="w-full bg-slate-100 border border-slate-200 text-slate-600 font-bold text-sm rounded-lg p-2.5 cursor-not-allowed" readonly>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Type d'ajustement</label>
                <select name="adjustment_type" id="adjustment_type" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
                    <option value="add">Ajouter au stock (+)</option>
                    <option value="remove">Retirer du stock (-)</option>
                    <option value="set">Définir le stock (=)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Quantité <span id="adjust_qty_unit_label" class="text-xs font-normal text-slate-400"></span></label>
                <input type="number" name="quantity" id="adjust_qty_input" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-lg font-bold rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required min="0" step="0.001">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Motif (optionnel)</label>
                <input type="text" name="reason" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" placeholder="Ex: Inventaire, Casse...">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('adjustStockModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-bold text-amber-900 bg-amber-400 hover:bg-amber-500 rounded-lg shadow-sm transition-colors">Ajuster</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="addSupplierModal">
    <div class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Ajouter un fournisseur</h3>
        <form method="POST" action="{{ route('pos.stock.add-supplier') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom du fournisseur</label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contact</label>
                <input type="text" name="contact_info" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Notes</label>
                <input type="text" name="notes" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('addSupplierModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg shadow-sm transition-colors">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="editSupplierModal">
    <div class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Modifier le fournisseur</h3>
        <form method="POST" id="editSupplierForm" class="flex flex-col gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_supplier_id">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom du fournisseur</label>
                <input type="text" name="name" id="edit_supplier_name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contact</label>
                <input type="text" name="contact_info" id="edit_supplier_contact" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Notes</label>
                <input type="text" name="notes" id="edit_supplier_notes" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('editSupplierModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="addCategoryModal">
    <div class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Ajouter une catégorie</h3>
        <form method="POST" action="{{ route('pos.stock.add-category') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom de la catégorie</label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required placeholder="Ex: Produits d'entretien">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('addCategoryModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg shadow-sm transition-colors">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center" id="editCategoryModal">
    <div class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Modifier la catégorie</h3>
        <form method="POST" id="editCategoryForm" class="flex flex-col gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_category_id">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom de la catégorie</label>
                <input type="text" name="name" id="edit_category_name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeModal('editCategoryModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    function switchTab(tab, event) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        if (event && event.target) {
            event.target.classList.add('active');
        } else if (window.event && window.event.target) {
             window.event.target.classList.add('active');
        }
        document.getElementById(tab + '-tab').classList.add('active');
    }

    const BASE_EDIT_CATEGORY = '{{ url("/pos/stock/edit-category") }}';
    function editCategory(category) {
        document.getElementById('edit_category_id').value = category.id;
        document.getElementById('edit_category_name').value = category.name;
        document.getElementById('editCategoryForm').action = BASE_EDIT_CATEGORY + '/' + category.id;
        openModal('editCategoryModal');
    }

    const BASE_EDIT_PRODUCT = '{{ url("/pos/stock/edit-product") }}';
    function editProduct(product) {
        document.getElementById('edit_product_id').value = product.id;
        document.getElementById('edit_product_name').value = product.name;
        document.getElementById('edit_product_barcode').value = product.barcode;
        document.getElementById('edit_product_category').value = product.category_id;
        document.getElementById('edit_product_supplier').value = product.supplier_id || '';
        document.getElementById('edit_product_purchase_price').value = product.purchase_price;
        document.getElementById('edit_product_sale_price').value = product.sale_price;
        document.getElementById('editProductForm').action = BASE_EDIT_PRODUCT + '/' + product.id;

        // Set barcode_type toggle
        const barcodeType = product.barcode_type || 'internal';
        document.getElementById('edit_type_scanned').checked = (barcodeType === 'scanned');
        document.getElementById('edit_type_internal').checked = (barcodeType === 'internal');
        onEditBarcodeTypeChange(barcodeType);

        // Set sell_by_length toggle
        const sellByLength = !!product.sell_by_length;
        document.getElementById('edit_sell_by_length_no').checked  = !sellByLength;
        document.getElementById('edit_sell_by_length_yes').checked = sellByLength;
        onEditUnitTypeChange(sellByLength);
        if (sellByLength && product.length_unit) {
            const lu = product.length_unit;
            document.querySelector(`input[name="edit_length_unit"][value="${lu}"]`).checked = true;
            setEditLengthBtnActive(lu);
        }

        openModal('editProductModal');
    }

    const BASE_ADJUST_STOCK = '{{ url("/pos/stock/adjust-stock") }}';
    function adjustStock(product) {
        document.getElementById('adjust_product_id').value = product.id;
        document.getElementById('adjust_product_name').value = product.name;
        const unit = product.sell_by_length ? product.length_unit : 'unités';
        const stockFormatted = product.sell_by_length
            ? parseFloat(product.stock_quantity).toFixed(3) + ' ' + unit
            : Math.round(product.stock_quantity) + ' ' + unit;
        document.getElementById('adjust_current_stock').value = stockFormatted;
        document.getElementById('adjustStockForm').action = BASE_ADJUST_STOCK + '/' + product.id;

        // Update quantity label and step
        const qtyLabel = document.getElementById('adjust_qty_unit_label');
        const qtyInput = document.getElementById('adjust_qty_input');
        if (product.sell_by_length) {
            qtyLabel.textContent = '(' + unit + ')';
            qtyInput.step = '0.001';
            qtyInput.placeholder = 'Ex: 2.500';
        } else {
            qtyLabel.textContent = '';
            qtyInput.step = '1';
            qtyInput.placeholder = '';
        }

        openModal('adjustStockModal');
    }

    const BASE_EDIT_SUPPLIER = '{{ url("/pos/stock/edit-supplier") }}';
    function editSupplier(supplier) {
        document.getElementById('edit_supplier_id').value = supplier.id;
        document.getElementById('edit_supplier_name').value = supplier.name;
        document.getElementById('edit_supplier_contact').value = supplier.contact_info || '';
        document.getElementById('edit_supplier_notes').value = supplier.notes || '';
        document.getElementById('editSupplierForm').action = BASE_EDIT_SUPPLIER + '/' + supplier.id;
        openModal('editSupplierModal');
    }

    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    function filterProducts() {
        const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
        const categoryFilter = document.getElementById('filterCategory').value.toLowerCase();
        const statusFilter = document.getElementById('filterStatus').value;
        
        const table = document.getElementById('productsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let row of rows) {
            if (row.cells.length === 1) continue;
            
            const productName = row.cells[0].textContent.toLowerCase();
            const barcode = row.cells[1].textContent.toLowerCase();
            const category = row.cells[2].textContent.toLowerCase();
            const statusBadge = row.cells[7].querySelector('.badge');
            const status = statusBadge ? statusBadge.textContent.trim() : '';
            
            const matchesSearch = productName.includes(searchTerm) || barcode.includes(searchTerm);
            const matchesCategory = !categoryFilter || category.includes(categoryFilter);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

<<<<<<< Updated upstream
    // Simple product lookup map for modal actions
=======
    function filterSuppliers() {
        const searchTerm = document.getElementById('searchSupplier').value.toLowerCase();
        const table = document.querySelector('#suppliers-tab table');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let row of rows) {
            if (row.cells.length <= 1) continue;
            
            const name = row.cells[0].textContent.toLowerCase();
            const contact = row.cells[1].textContent.toLowerCase();
            
            if (name.includes(searchTerm) || contact.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function filterCategories() {
        const searchTerm = document.getElementById('searchCategory').value.toLowerCase();
        const table = document.querySelector('#categories-tab table');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let row of rows) {
            if (row.cells.length <= 1) continue;
            
            const name = row.cells[0].textContent.toLowerCase();
            
            if (name.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    // ── Add Product: Unit Type Toggle ───────────────────────────────────────
    function onUnitTypeChange(isByLength) {
        const btnPcs    = document.getElementById('btn_pcs');
        const btnLength = document.getElementById('btn_length');
        const selector  = document.getElementById('length_unit_selector');
        const qtyLabel  = document.getElementById('add_qty_unit_label');
        const qtyInput  = document.getElementById('stock_quantity_input');

        if (isByLength) {
            btnPcs.className    = btnPcs.className.replace('border-blue-500 bg-blue-50 text-blue-700', 'border-slate-200 bg-slate-50 text-slate-500');
            btnLength.className = btnLength.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-blue-500 bg-blue-50 text-blue-700');
            selector.classList.remove('hidden');
            const checkedUnit = document.querySelector('input[name="length_unit"]:checked');
            qtyLabel.textContent = checkedUnit ? '(' + checkedUnit.value + ')' : '(m)';
            qtyInput.step = '0.001';
            qtyInput.placeholder = 'Ex: 50.500';
        } else {
            btnPcs.className    = btnPcs.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-blue-500 bg-blue-50 text-blue-700');
            btnLength.className = btnLength.className.replace('border-blue-500 bg-blue-50 text-blue-700', 'border-slate-200 bg-slate-50 text-slate-500');
            selector.classList.add('hidden');
            qtyLabel.textContent = '';
            qtyInput.step = '1';
            qtyInput.placeholder = '';
        }
    }

    function setLengthBtnActive(unit) {
        const btnM  = document.getElementById('btn_unit_m');
        const btnCm = document.getElementById('btn_unit_cm');
        const qtyLabel = document.getElementById('add_qty_unit_label');
        if (unit === 'm') {
            btnM.className  = btnM.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-teal-500 bg-teal-50 text-teal-700');
            btnCm.className = btnCm.className.replace('border-teal-500 bg-teal-50 text-teal-700', 'border-slate-200 bg-slate-50 text-slate-500');
            qtyLabel.textContent = '(m)';
        } else {
            btnCm.className = btnCm.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-teal-500 bg-teal-50 text-teal-700');
            btnM.className  = btnM.className.replace('border-teal-500 bg-teal-50 text-teal-700', 'border-slate-200 bg-slate-50 text-slate-500');
            qtyLabel.textContent = '(cm)';
        }
    }

    // ── Edit Product: Unit Type Toggle ──────────────────────────────────────
    function onEditUnitTypeChange(isByLength) {
        const btnPcs    = document.getElementById('edit_btn_pcs');
        const btnLength = document.getElementById('edit_btn_length');
        const selector  = document.getElementById('edit_length_unit_selector');
        if (isByLength) {
            btnPcs.className    = btnPcs.className.replace('border-blue-500 bg-blue-50 text-blue-700', 'border-slate-200 bg-slate-50 text-slate-500');
            btnLength.className = btnLength.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-blue-500 bg-blue-50 text-blue-700');
            selector.classList.remove('hidden');
        } else {
            btnPcs.className    = btnPcs.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-blue-500 bg-blue-50 text-blue-700');
            btnLength.className = btnLength.className.replace('border-blue-500 bg-blue-50 text-blue-700', 'border-slate-200 bg-slate-50 text-slate-500');
            selector.classList.add('hidden');
        }
    }

    function setEditLengthBtnActive(unit) {
        const btnM  = document.getElementById('edit_btn_unit_m');
        const btnCm = document.getElementById('edit_btn_unit_cm');
        if (unit === 'm') {
            btnM.className  = btnM.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-teal-500 bg-teal-50 text-teal-700 font-bold');
            btnCm.className = btnCm.className.replace('border-teal-500 bg-teal-50 text-teal-700 font-bold', 'border-slate-200 bg-slate-50 text-slate-500');
        } else {
            btnCm.className = btnCm.className.replace('border-slate-200 bg-slate-50 text-slate-500', 'border-teal-500 bg-teal-50 text-teal-700 font-bold');
            btnM.className  = btnM.className.replace('border-teal-500 bg-teal-50 text-teal-700 font-bold', 'border-slate-200 bg-slate-50 text-slate-500');
        }
    }
>>>>>>> Stashed changes
    @php
        $products_for_js = $products->map(function($p) {
            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'barcode'        => $p->barcode,
                'barcode_type'   => $p->barcode_type ?? 'internal',
                'stock_quantity' => (float) $p->stock_quantity,
                'sale_price'     => $p->sale_price,
                'sell_by_length' => (bool) $p->sell_by_length,
                'length_unit'    => $p->length_unit,
                'category_id'    => $p->category_id,
                'supplier_id'    => $p->supplier_id,
                'purchase_price' => $p->purchase_price,
            ];
        });
    @endphp
    const PRODUCTS = {!! $products_for_js->toJson() !!};

    // ── AZERTY → Numérique (pour les scanners de codes-barres sur clavier FR) ──
    // Un scanner se comporte comme un clavier. Sur un clavier AZERTY, les touches
    // du rang supérieur envoient des symboles (&, é, ", etc.) au lieu de chiffres.
    // Ce mapping corrige automatiquement ces symboles en chiffres.
    const azertyMap = {
        '&': '1', 'é': '2', '"': '3', "'": '4', '(': '5',
        '-': '6', 'è': '7', '_': '8', 'ç': '9', 'à': '0',
        ')': '°', '=': '+'
    };

    function applyAzertyMap(value) {
        let corrected = '';
        let changed = false;
        for (let char of value) {
            if (azertyMap[char] !== undefined) {
                corrected += azertyMap[char];
                changed = true;
            } else {
                corrected += char;
            }
        }
        return changed ? corrected : null; // returns null if no change needed
    }

    // Apply to inputs where barcode scanners might be used
    document.addEventListener('DOMContentLoaded', function () {
        const inputsToCorrect = [
            'add_barcode_input',
            'edit_product_barcode',
            'searchProduct',
            'searchSupplier',
            'searchCategory'
        ];

        inputsToCorrect.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function () {
                    const corrected = applyAzertyMap(this.value);
                    if (corrected !== null) {
                        this.value = corrected;
                        // For search inputs, trigger the filter function manually since we changed the value
                        if (id === 'searchProduct') filterProducts();
                        if (id === 'searchSupplier') filterSuppliers();
                        if (id === 'searchCategory') filterCategories();
                    }
                });
            }
        });
    });

    // ── Barcode type toggle for ADD PRODUCT modal ──────────────────────
    function onBarcodeTypeChange(type) {
        const wrapper = document.getElementById('barcode_input_wrapper');
        const notice  = document.getElementById('barcode_auto_notice');
        const btnScanned  = document.getElementById('btn_scanned');
        const btnInternal = document.getElementById('btn_internal');
        const input = document.getElementById('add_barcode_input');

        if (type === 'scanned') {
            wrapper.classList.remove('hidden');
            notice.classList.add('hidden');
            btnScanned.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
            btnScanned.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-500');
            btnInternal.classList.remove('border-violet-500', 'bg-violet-50', 'text-violet-700');
            btnInternal.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-500');
            if (input) input.focus();
        } else {
            wrapper.classList.add('hidden');
            notice.classList.remove('hidden');
            btnInternal.classList.add('border-violet-500', 'bg-violet-50', 'text-violet-700');
            btnInternal.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-500');
            btnScanned.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
            btnScanned.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-500');
        }
    }

    // ── Barcode type toggle for EDIT PRODUCT modal ─────────────────────
    function onEditBarcodeTypeChange(type) {
        const wrapper = document.getElementById('edit_barcode_input_wrapper');
        const notice  = document.getElementById('edit_barcode_auto_notice');
        const btnScanned  = document.getElementById('edit_btn_scanned');
        const btnInternal = document.getElementById('edit_btn_internal');
        const visibleInput = document.getElementById('edit_product_barcode');
        const hiddenInput  = document.getElementById('edit_product_barcode_hidden');

        if (type === 'scanned') {
            wrapper.classList.remove('hidden');
            notice.classList.add('hidden');
            // Restore name on visible input, remove from hidden
            visibleInput.name = 'barcode';
            if (hiddenInput) hiddenInput.removeAttribute('name');
            btnScanned.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
            btnScanned.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-500');
            btnInternal.classList.remove('border-violet-500', 'bg-violet-50', 'text-violet-700');
            btnInternal.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-500');
        } else {
            wrapper.classList.add('hidden');
            notice.classList.remove('hidden');
            // Move barcode to hidden input so form still sends it (server will regenerate)
            if (hiddenInput) {
                hiddenInput.name = 'barcode';
                hiddenInput.value = visibleInput.value;
            }
            visibleInput.removeAttribute('name');
            btnInternal.classList.add('border-violet-500', 'bg-violet-50', 'text-violet-700');
            btnInternal.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-500');
            btnScanned.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
            btnScanned.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-500');
        }
    }

    let currentDetailProduct = null;

    function showProductDetails(product) {
        currentDetailProduct = product;
        document.getElementById('detail_product_name').textContent = product.name;
        document.getElementById('detail_product_barcode').textContent = product.barcode;
        document.getElementById('detail_product_stock').textContent = product.stock_quantity;
        openModal('productDetailsModal');
    }

    function openPrintFromDetails() {
        if (!currentDetailProduct) return;
        document.getElementById('print_product_id').value = currentDetailProduct.id;
        document.getElementById('print_product_name').textContent = currentDetailProduct.name;
        document.getElementById('print_quantity_input').value = '1';
        closeModal('productDetailsModal');
        openModal('printBarcodeModal');
    }

    function submitPrintForm(e) {
        e.preventDefault();
        const id  = document.getElementById('print_product_id').value;
        const qty = document.getElementById('print_quantity_input').value;
        const url = '{{ route("pos.barcode.print") }}?product_id=' + encodeURIComponent(id) + '&quantity=' + encodeURIComponent(qty);
        window.open(url, '_blank');
        closeModal('printBarcodeModal');
    }

    // If a new product was just created, open the print modal automatically
    @if(session('new_product_id'))
        (function() {
            const newId = {{ session('new_product_id') }};
            const p = PRODUCTS.find(x => x.id === newId);
            if (p) {
                document.getElementById('print_product_id').value = p.id;
                document.getElementById('print_product_name').textContent = p.name;
                document.getElementById('print_quantity_input').value = '1';
                openModal('printBarcodeModal');
            }
        })();
    @endif
</script>
</body>
</html>