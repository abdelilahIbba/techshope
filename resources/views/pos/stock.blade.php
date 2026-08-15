@extends('layouts.pos')
@section('title', 'Gestion de stock')
@push('styles')
<style>
.tab-content { display: none; }
.tab-content.active { display: block; }
.modal { display: none; position: fixed; inset: 0; z-index: 50; overflow-y: auto; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
.modal.active { display: grid; place-items: center; padding: 1rem; animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
.glass-panel { background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); }
.modal-content { width: min(100%, 48rem); max-height: calc(100vh - 2rem); overflow-y: auto; margin: auto; }
.modal#numericKeyboardModal .modal-content { width: min(100%, 20rem); }
</style>
@endpush
@section('content')



<div class="container flex-1 px-4 py-4 lg:px-6 lg:py-6 overflow-y-auto w-full max-w-[1920px] mx-auto relative">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -left-20 top-10 h-56 w-56 rounded-full bg-indigo-200/30 blur-3xl"></div>
        <div class="absolute right-10 top-24 h-64 w-64 rounded-full bg-sky-200/25 blur-3xl"></div>
        <div class="absolute left-1/2 bottom-0 h-72 w-72 -translate-x-1/2 rounded-full bg-white/50 blur-3xl"></div>
    </div>

    <div class="relative z-10">
    <div class="glass-panel rounded-3xl px-5 py-4 shadow-sm border border-white/60 flex items-center justify-between gap-4 mb-4">
        <div class="min-w-0">
            <p class="text-[10px] font-black uppercase tracking-[0.35em] text-muted-foreground/60">Back office</p>
            <h1 class="text-3xl font-black text-card-foreground tracking-tight leading-tight">Gestion de stock</h1>
            <p class="text-sm text-muted-foreground mt-1 font-medium">Produits, fournisseurs et catégories dans une interface compacte et plus lisible.</p>
        </div>
        <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-sm px-5 py-3 shadow-lg shadow-slate-200 transition-all flex items-center gap-2 active:scale-[0.98] whitespace-nowrap" onclick="openModal('addProductModal')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
            Ajouter produit
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="glass-panel rounded-3xl p-4 border border-white/60 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground/60">Catalogue</p>
            <p class="mt-2 text-2xl font-black text-card-foreground">Produits, marques et variantes</p>
        </div>
        <div class="glass-panel rounded-3xl p-4 border border-white/60 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground/60">Contrôle</p>
            <p class="mt-2 text-2xl font-black text-card-foreground">Stock et fournisseurs centralisés</p>
        </div>
        <div class="glass-panel rounded-3xl p-4 border border-white/60 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground/60">Vitesse</p>
            <p class="mt-2 text-2xl font-black text-card-foreground">Filtres rapides et modals plus propres</p>
        </div>
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

    <div class="glass-panel rounded-3xl p-2 shadow-sm border border-white/60 mb-4 inline-flex gap-2 flex-wrap">
        <button class="tab-btn active px-4 py-2.5 rounded-2xl text-sm font-black text-slate-500 border border-transparent bg-white shadow-sm hover:text-slate-900 transition-all [&.active]:text-indigo-700 [&.active]:bg-indigo-50 [&.active]:border-indigo-100" onclick="switchTab('products')">Produits</button>
        <button class="tab-btn px-4 py-2.5 rounded-2xl text-sm font-black text-slate-500 border border-transparent bg-transparent hover:bg-slate-50 hover:text-slate-900 transition-all [&.active]:text-indigo-700 [&.active]:bg-indigo-50 [&.active]:border-indigo-100" onclick="switchTab('suppliers')">Fournisseurs</button>
        <button class="tab-btn px-4 py-2.5 rounded-2xl text-sm font-black text-slate-500 border border-transparent bg-transparent hover:bg-slate-50 hover:text-slate-900 transition-all [&.active]:text-indigo-700 [&.active]:bg-indigo-50 [&.active]:border-indigo-100" onclick="switchTab('categories')">Catégories</button>
    </div>

    <div class="tab-content active" id="products-tab">
        <div class="glass-panel mb-4 flex flex-wrap gap-4 items-center p-4 rounded-3xl border border-white/60 shadow-sm">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" id="searchProduct" class="pl-10 form-input bg-white border border-slate-200 text-slate-900 text-sm rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full h-12 px-3 outline-none transition-all shadow-sm" placeholder="Rechercher un produit (nom, barcode)..." onkeyup="filterProducts()">
            </div>
            <select id="filterCategory" class="form-select bg-white border border-slate-200 text-slate-900 text-sm rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full md:w-[200px] h-12 px-3 outline-none shadow-sm" onchange="filterProducts()">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select id="filterStatus" class="form-select bg-white border border-slate-200 text-slate-900 text-sm rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full md:w-[150px] h-12 px-3 outline-none shadow-sm" onchange="filterProducts()">
                <option value="">Tous les statuts</option>
                <option value="OK">OK</option>
                <option value="Faible">Faible</option>
                <option value="Critique">Critique</option>
            </select>
        </div>

        <div class="table-container glass-panel shadow-sm ring-1 ring-black/5 rounded-3xl overflow-hidden border border-white/60">
            <div class="overflow-x-auto">
                <table id="productsTable" class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/90 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold">Produit</th>
                            <th class="px-6 py-4 font-bold">Barcode</th>
                            <th class="px-6 py-4 font-bold">Type</th>
                            <th class="px-6 py-4 font-bold">Catégorie</th>
                            <th class="px-6 py-4 font-bold">Fournisseur</th>
                            <th class="px-6 py-4 font-bold">Unité</th>
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
                                <td class="px-6 py-4 font-bold text-slate-800">
                                    <div>{{ $product->name }}</div>
                                    <div class="text-xs font-medium text-slate-500 mt-0.5">
                                        {{ $product->brand ?: 'Sans marque' }}
                                        @if(($product->variants?->count() ?? 0) > 1)
                                            · {{ $product->variants->count() }} variantes
                                        @endif
                                    </div>
                                </td>
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
                                <td class="px-6 py-4">
                                    @if($product->unit === 'metre')
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2 py-1 rounded-md text-xs font-semibold border border-amber-100">📏 m</span>
                                    @elseif($product->unit === 'cm')
                                        <span class="inline-flex items-center gap-1 bg-cyan-50 text-cyan-700 px-2 py-1 rounded-md text-xs font-semibold border border-cyan-100">📐 cm</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-slate-50 text-slate-700 px-2 py-1 rounded-md text-xs font-semibold border border-slate-100">📦 pcs</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-extrabold text-slate-900">
                                    @if($product->unit === 'unité')
                                        {{ number_format($product->stock_quantity, 0) }}
                                    @else
                                        {{ (float)$product->stock_quantity }}
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
<div class="modal" id="printBarcodeModal">
    <div class="modal-content glass-panel w-full max-w-sm rounded-3xl shadow-2xl border border-white/70 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-3">Imprimer tickets - <span id="print_product_name"></span></h3>
        <form id="printBarcodeForm" onsubmit="submitPrintForm(event)">
            <input type="hidden" id="print_product_id">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre de tickets</label>
                <input type="text" name="quantity" id="print_quantity_input" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg p-2.5 outline-none cursor-pointer" value="1" readonly onclick="openNumericKeyboard('print_quantity_input')">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition-colors" onclick="closeModal('printBarcodeModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-2xl shadow-lg transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Imprimer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal" id="productDetailsModal">
    <div class="modal-content glass-panel w-full max-w-md rounded-3xl shadow-2xl border border-white/70 p-6 relative">
        <button type="button" class="absolute top-3 right-3 text-slate-400 hover:text-slate-700 text-xl" onclick="closeModal('productDetailsModal')">&times;</button>
        <h3 class="text-xl font-bold text-slate-900 mb-2" id="detail_product_name"></h3>
        <p class="text-sm text-slate-600 mb-3" id="detail_product_barcode"></p>
        <p class="text-sm text-slate-600 mb-3">Stock: <span id="detail_product_stock"></span></p>
        <div class="flex gap-2">
            <button type="button" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg" onclick="openPrintFromDetails()">Imprimer barcode</button>
            <button type="button" class="px-4 py-2 text-sm bg-slate-100 text-slate-700 rounded-2xl" onclick="closeModal('productDetailsModal')">Fermer</button>
        </div>
    </div>
</div>

    <div class="tab-content" id="suppliers-tab">
        <div class="glass-panel mb-4 flex flex-wrap gap-4 items-center p-4 rounded-3xl border border-white/60 shadow-sm">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" id="searchSupplier" class="pl-10 form-input bg-white border border-slate-200 text-slate-900 text-sm rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full h-12 px-3 outline-none transition-all shadow-sm" placeholder="Rechercher un fournisseur (nom, contact)..." onkeyup="filterSuppliers()">
            </div>
            <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-sm px-5 py-3 shadow-lg shadow-slate-200 transition-all flex items-center gap-2 active:scale-[0.98]" onclick="openModal('addSupplierModal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                Ajouter fournisseur
            </button>
        </div>
        <div class="table-container glass-panel shadow-sm ring-1 ring-black/5 rounded-3xl overflow-hidden border border-white/60">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/90 border-b border-slate-200">
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
        <div class="glass-panel mb-4 flex flex-wrap gap-4 items-center p-4 rounded-3xl border border-white/60 shadow-sm">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" id="searchCategory" class="pl-10 form-input bg-white border border-slate-200 text-slate-900 text-sm rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full h-12 px-3 outline-none transition-all shadow-sm" placeholder="Rechercher une catégorie (nom)..." onkeyup="filterCategories()">
            </div>
            <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-sm px-5 py-3 shadow-lg shadow-slate-200 transition-all flex items-center gap-2 active:scale-[0.98]" onclick="openModal('addCategoryModal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                Ajouter catégorie
            </button>
        </div>
        <div class="table-container glass-panel shadow-sm ring-1 ring-black/5 rounded-3xl overflow-hidden border border-white/60">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/90 border-b border-slate-200">
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

<div class="modal" id="addProductModal">
    <div class="modal-content glass-panel w-full max-w-3xl rounded-3xl shadow-2xl border border-white/80 p-6 md:p-7 relative my-auto bg-white/95 backdrop-blur-xl">
        
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-md shadow-slate-900/10 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">Ajouter un produit</h3>
                    <p class="text-xs text-slate-400 font-medium">Enregistrer un nouvel article dans le stock textile & habillement</p>
                </div>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-2 rounded-xl text-xl transition-all" onclick="closeModal('addProductModal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('pos.stock.add-product') }}" class="flex flex-col gap-4" id="addProductForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {{-- Row 1: Nom du produit & Type de Barcode --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nom du produit <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" class="w-full px-4 py-2.5 bg-slate-50/90 border border-slate-200 text-slate-900 text-sm font-semibold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none transition-all shadow-xs" placeholder="Ex: T-shirt Oversize Cotton" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Type de Barcode</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100/90 rounded-xl border border-slate-200/70" id="barcode_type_toggle">
                        <label id="btn_scanned" class="flex items-center justify-center gap-2 cursor-pointer py-2 px-3 rounded-lg text-xs font-bold transition-all border-2 border-slate-900 bg-white text-slate-900 shadow-xs">
                            <input type="radio" name="barcode_type" value="scanned" class="sr-only" checked onchange="onBarcodeTypeChange('scanned')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            <span>Scanné</span>
                        </label>
                        <label id="btn_internal" class="flex items-center justify-center gap-2 cursor-pointer py-2 px-3 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-500 hover:text-slate-800">
                            <input type="radio" name="barcode_type" value="internal" class="sr-only" onchange="onBarcodeTypeChange('internal')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <span>Interne</span>
                        </label>
                    </div>
                </div>

                {{-- Row 2: Marque & Barcode Input --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Marque</label>
                        <span class="text-[11px] text-slate-400 font-medium">Sélection ou écriture</span>
                    </div>
                    <input type="hidden" name="brand" id="add_product_brand" value="">
                    <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-50/90 border border-slate-200 rounded-xl min-h-[46px] focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/10 focus-within:border-slate-800 transition-all shadow-xs">
                        <div id="selected_brand_tags" class="flex flex-wrap items-center gap-1.5"></div>
                        <input type="text" id="brand_custom_input" placeholder="Écrire une marque..." class="flex-1 min-w-[120px] bg-transparent text-xs font-semibold text-slate-800 outline-none placeholder:text-slate-400 py-1 px-1.5">
                    </div>
                    {{-- Quick brand presets --}}
                    <div class="flex flex-wrap gap-1 mt-1.5" id="brand_quick_presets">
                        <button type="button" class="brand-chip px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg transition-all" onclick="toggleBrandTag('Nike')">Nike</button>
                        <button type="button" class="brand-chip px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg transition-all" onclick="toggleBrandTag('Zara')">Zara</button>
                        <button type="button" class="brand-chip px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg transition-all" onclick="toggleBrandTag('Adidas')">Adidas</button>
                        <button type="button" class="brand-chip px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg transition-all" onclick="toggleBrandTag('Puma')">Puma</button>
                        <button type="button" class="brand-chip px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg transition-all" onclick="toggleBrandTag('H&M')">H&M</button>
                        <button type="button" class="brand-chip px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg transition-all" onclick="toggleBrandTag('Bershka')">Bershka</button>
                    </div>
                </div>

                <div>
                    <div id="barcode_input_wrapper">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Scanner / Barcode</label>
                        <div class="relative flex items-center">
                            <input type="text" name="barcode" id="add_barcode_input" class="w-full pl-10 pr-4 py-2.5 bg-slate-50/90 border border-slate-200 text-slate-900 text-sm font-mono font-bold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none transition-all shadow-xs" placeholder="Ex: 6111250450232" autofocus>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Utilisez votre lecteur ou saisissez le code manuellement.</p>
                    </div>
                    <div id="barcode_auto_notice" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Génération Automatique</label>
                        <div class="flex items-center gap-2.5 bg-indigo-50/90 border border-indigo-200/80 rounded-xl p-3 shadow-xs">
                            <span class="p-1.5 bg-indigo-600 text-white rounded-lg inline-flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </span>
                            <span class="text-xs text-indigo-950 font-bold leading-snug">Un barcode unique interne sera généré automatiquement.</span>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Catégorie & Fournisseur --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catégorie <span class="text-rose-500">*</span></label>
                    <div class="relative flex items-center">
                        <select name="category_id" class="w-full px-4 pr-10 py-2.5 bg-slate-50/90 border border-slate-200 text-slate-900 text-sm font-semibold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none appearance-none transition-all shadow-xs" required>
                            <option value="">Sélectionner une catégorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute right-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Fournisseur</label>
                    <div class="relative flex items-center">
                        <select name="supplier_id" class="w-full px-4 pr-10 py-2.5 bg-slate-50/90 border border-slate-200 text-slate-900 text-sm font-semibold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none appearance-none transition-all shadow-xs">
                            <option value="">Aucun fournisseur</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute right-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Row 4: Unité de vente & Quantité initiale --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unité de vente</label>
                    <div class="relative flex items-center">
                        <select name="unit" id="add_product_unit" class="w-full px-4 pr-10 py-2.5 bg-slate-50/90 border border-slate-200 text-slate-900 text-sm font-semibold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none appearance-none transition-all shadow-xs" required>
                            <option value="unité">Pièce (Unité)</option>
                            <option value="metre">Mètre (m)</option>
                            <option value="cm">Centimètre (cm)</option>
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute right-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Quantité initiale</label>
                    <div class="relative flex items-center">
                        <input type="text" name="stock_quantity" id="stock_quantity_input" class="w-full pl-4 pr-11 py-2.5 bg-slate-50/90 border border-slate-200 text-slate-900 text-sm font-bold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none cursor-pointer transition-all shadow-xs" value="0" required readonly onclick="openNumericKeyboard('stock_quantity_input')">
                        <button type="button" onclick="openNumericKeyboard('stock_quantity_input')" class="absolute right-2 text-slate-400 hover:text-slate-800 p-1.5 rounded-lg hover:bg-slate-200/60 transition-colors" title="Ouvrir le pavé numérique">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Row 5: Prix d'achat & Prix vente --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prix d'achat ({{ $currencySymbol }}) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="purchase_price" class="w-full px-4 py-2.5 bg-slate-50/90 border border-slate-200 text-slate-900 text-sm font-bold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none transition-all shadow-xs" placeholder="120.00" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prix vente ({{ $currencySymbol }}) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="sale_price" class="w-full px-4 py-2.5 bg-slate-50/90 border border-slate-200 text-emerald-600 text-sm font-extrabold rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition-all shadow-xs" placeholder="250.00" required>
                </div>

                {{-- Row 6: Tailles (Sizes) & Couleurs (Colors) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Tailles (Tags)</label>
                        <span class="text-[11px] text-slate-400 font-medium">Sélection rapide</span>
                    </div>
                    <input type="hidden" name="sizes_text" id="add_sizes_text" value="S,M,L">
                    
                    {{-- Selected Size Tags Container --}}
                    <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-50/90 border border-slate-200 rounded-xl min-h-[46px] focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/10 focus-within:border-slate-800 transition-all shadow-xs">
                        <div id="selected_size_tags" class="flex flex-wrap items-center gap-1.5"></div>
                        <input type="text" id="size_custom_input" placeholder="+ Taille..." class="flex-1 min-w-[80px] bg-transparent text-xs font-bold text-slate-800 outline-none placeholder:text-slate-400 py-1 px-1.5">
                    </div>

                    {{-- Quick Size Chips --}}
                    <div class="flex flex-wrap gap-1 mt-1.5" id="size_quick_chips">
                        @foreach(['S', 'M', 'L', 'XL', 'XXL', '38', '40', '42', '44'] as $quickSize)
                            <button type="button" class="size-chip px-2.5 py-1 text-xs font-bold rounded-lg border transition-all" data-size="{{ $quickSize }}" onclick="toggleSizeTag('{{ $quickSize }}')">
                                {{ $quickSize }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Couleurs (Pastilles)</label>
                        <button type="button" onclick="openColorCreatorModal()" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/60 px-2 py-0.5 rounded-lg transition-all shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Créer couleur</span>
                        </button>
                    </div>
                    <input type="hidden" name="colors_text" id="add_colors_text" value="Bleu,Rouge">
                    
                    {{-- Selected Color Tags Container --}}
                    <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-50/90 border border-slate-200 rounded-xl min-h-[46px] focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/10 focus-within:border-slate-800 transition-all shadow-xs">
                        <div id="selected_color_tags" class="flex flex-wrap items-center gap-1.5"></div>
                        <input type="text" id="color_custom_input" placeholder="+ Couleur..." class="flex-1 min-w-[80px] bg-transparent text-xs font-semibold text-slate-800 outline-none placeholder:text-slate-400 py-1 px-1.5">
                    </div>

                    {{-- Quick Color Swatches --}}
                    <div class="flex flex-wrap items-center gap-2 mt-1.5" id="color_quick_swatches">
                        @php
                            $defaultColors = [
                                ['name' => 'Noir', 'hex' => '#0f172a'],
                                ['name' => 'Blanc', 'hex' => '#ffffff'],
                                ['name' => 'Bleu', 'hex' => '#2563eb'],
                                ['name' => 'Rouge', 'hex' => '#dc2626'],
                                ['name' => 'Vert', 'hex' => '#16a34a'],
                                ['name' => 'Gris', 'hex' => '#64748b'],
                                ['name' => 'Beige', 'hex' => '#d4b996'],
                                ['name' => 'Rose', 'hex' => '#ec4899'],
                                ['name' => 'Jaune', 'hex' => '#eab308'],
                            ];
                            $allColorList = array_merge($defaultColors, $customColors ?? []);
                            // Deduplicate by name
                            $uniqueColors = [];
                            foreach ($allColorList as $c) {
                                $uniqueColors[strtolower($c['name'])] = $c;
                            }
                        @endphp
                        @foreach($uniqueColors as $pc)
                            <button type="button" class="color-swatch-btn relative w-6 h-6 rounded-full border border-slate-300 shadow-xs hover:scale-110 active:scale-95 transition-all flex items-center justify-center shrink-0" data-color="{{ $pc['name'] }}" data-hex="{{ $pc['hex'] }}" title="{{ $pc['name'] }}" style="background-color: {{ $pc['hex'] }};" onclick="toggleColorTag('{{ $pc['name'] }}', '{{ $pc['hex'] }}')">
                                <span class="swatch-check hidden w-1.5 h-1.5 rounded-full {{ in_array(strtolower($pc['name']), ['blanc', 'beige', 'jaune', 'menthe', 'lavande']) ? 'bg-slate-900' : 'bg-white' }}"></span>
                            </button>
                        @endforeach
                        
                        {{-- Add custom color round palette button --}}
                        <button type="button" onclick="openColorCreatorModal()" class="w-6 h-6 rounded-full border-2 border-dashed border-indigo-400 hover:border-indigo-700 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 flex items-center justify-center transition-all shadow-xs hover:scale-110 shrink-0" title="Créer une couleur personnalisée">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>

            </div>

            {{-- Footer Action Buttons --}}
            <div class="flex items-center justify-end gap-3 mt-3 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all active:scale-95" onclick="closeModal('addProductModal')">
                    Annuler
                </button>
                <button type="submit" class="flex items-center gap-2 px-6 py-2.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-lg shadow-slate-900/20 transition-all hover:scale-[1.01] active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Ajouter</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Créer une nouvelle couleur personnalisée -->
<div class="modal fixed inset-0 z-[75]" id="createColorModal">
    <div class="modal-content glass-panel w-full max-w-sm rounded-3xl shadow-2xl border border-white/80 p-6 relative bg-white/98 my-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-pink-500 text-white flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 5 5 0 014-4h4a5 5 0 014 4 4 4 0 01-4 4H7zm0 0l-3-3m0 0l3-3m-3 3h18M14 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <div>
                    <h3 class="text-base font-black text-slate-900 tracking-tight">Créer une couleur</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Sauvegarder dans la base de données</p>
                </div>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-100 text-lg transition-all" onclick="closeModal('createColorModal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Fields --}}
        <div class="space-y-4">
            
            {{-- Color Picker & Swatch Preview --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Choisir la nuance visuelle</label>
                <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="relative w-12 h-12 rounded-2xl overflow-hidden shadow-inner border border-black/10 shrink-0">
                        <input type="color" id="custom_color_picker" value="#06b6d4" class="absolute -inset-2 w-16 h-16 cursor-pointer border-0 p-0" oninput="onCustomColorPickerChange(this.value)">
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-[11px] font-semibold text-slate-400 block mb-0.5">Code couleur (HEX)</span>
                        <input type="text" id="custom_color_hex" value="#06B6D4" maxlength="7" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-mono font-bold text-slate-800 outline-none uppercase focus:border-slate-800 transition-all" oninput="onCustomColorHexChange(this.value)">
                    </div>
                </div>
            </div>

            {{-- Nom de la couleur --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nom de la couleur <span class="text-rose-500">*</span></label>
                <input type="text" id="custom_color_name" placeholder="Ex: Turquoise, Bordeaux, Menthe..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold rounded-xl focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 outline-none transition-all" onkeydown="if(event.key==='Enter'){event.preventDefault();saveNewCustomColor();}">
            </div>

            {{-- Quick Palette Ideas --}}
            <div>
                <span class="text-[11px] font-bold text-slate-500 block mb-1.5">Nuances populaires :</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" class="px-2 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-all flex items-center gap-1.5" onclick="setPresetInColorCreator('Turquoise', '#06b6d4')">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#06b6d4]"></span> Turquoise
                    </button>
                    <button type="button" class="px-2 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-all flex items-center gap-1.5" onclick="setPresetInColorCreator('Bordeaux', '#800020')">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#800020]"></span> Bordeaux
                    </button>
                    <button type="button" class="px-2 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-all flex items-center gap-1.5" onclick="setPresetInColorCreator('Menthe', '#a7f3d0')">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#a7f3d0]"></span> Menthe
                    </button>
                    <button type="button" class="px-2 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-all flex items-center gap-1.5" onclick="setPresetInColorCreator('Corail', '#fb7185')">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#fb7185]"></span> Corail
                    </button>
                    <button type="button" class="px-2 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-all flex items-center gap-1.5" onclick="setPresetInColorCreator('Olive', '#65a30d')">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#65a30d]"></span> Olive
                    </button>
                    <button type="button" class="px-2 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-all flex items-center gap-1.5" onclick="setPresetInColorCreator('Marine', '#1e3a8a')">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#1e3a8a]"></span> Marine
                    </button>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all" onclick="closeModal('createColorModal')">
                    Annuler
                </button>
                <button type="button" id="btn_save_custom_color" onclick="saveNewCustomColor()" class="flex items-center gap-2 px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-lg shadow-slate-900/15 transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Sauvegarder</span>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Floating Toast Notification -->
<div id="stock_toast" class="fixed top-5 right-5 z-[100] transform transition-all duration-300 translate-y-[-20px] opacity-0 pointer-events-none">
    <div class="bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-800">
        <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </span>
        <span id="stock_toast_message" class="text-xs font-bold tracking-wide">Action effectuée</span>
    </div>
</div>

<!-- Numeric Keyboard Modal (Cleanly styled on top) -->
<div class="modal fixed inset-0 z-[70]" id="numericKeyboardModal">
    <div class="modal-content glass-panel w-full max-w-xs rounded-3xl shadow-2xl border border-white/80 p-6 flex flex-col items-center gap-4 relative bg-white/98">
        <h3 class="text-lg font-black text-slate-900 mb-1">Entrer la quantité</h3>
        <input type="text" id="numericKeyboardInput" class="w-full text-center text-3xl font-black bg-slate-100 border border-slate-300 rounded-2xl p-3 mb-2 text-slate-900 font-mono tracking-wider outline-none" readonly>
        <div class="grid grid-cols-3 gap-2 w-full">
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('1')">1</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('2')">2</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('3')">3</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('4')">4</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('5')">5</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('6')">6</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('7')">7</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('8')">8</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('9')">9</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('.')">.</button>
            <button type="button" class="bg-slate-100 hover:bg-indigo-50 text-xl font-black text-slate-800 rounded-2xl p-3.5 shadow-xs transition-all active:scale-95" onclick="appendNumericKey('0')">0</button>
            <button type="button" class="bg-rose-50 hover:bg-rose-100 text-xl font-black rounded-2xl p-3.5 text-rose-600 transition-all active:scale-95" onclick="clearNumericKey()">C</button>
            <div class="col-span-3">
                <button type="button" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl p-3.5 mt-1 shadow-lg shadow-slate-900/10 transition-all active:scale-98" onclick="confirmNumericKey()">Confirmer</button>
            </div>
        </div>
        <button type="button" class="absolute top-3 right-3 text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-100 text-xl" onclick="closeModal('numericKeyboardModal')">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>

<div class="modal" id="editProductModal">
    <div class="modal-content glass-panel w-full max-w-md rounded-3xl shadow-2xl border border-white/70 p-5 relative my-auto">
        <h3 class="text-lg font-bold text-slate-900 mb-2">Modifier le produit</h3>
        <form method="POST" id="editProductForm" class="flex flex-col gap-2">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_product_id">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom du produit</label>
                <input type="text" name="name" id="edit_product_name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Marque</label>
                <input type="text" name="brand" id="edit_product_brand" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2 outline-none" placeholder="Ex: Zara, Nike, ...">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Type de Barcode</label>
                <div class="flex gap-2 mb-3">
                    <label id="edit_btn_scanned" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-3 py-2 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm transition-all">
                        <input type="radio" name="barcode_type" id="edit_type_scanned" value="scanned" class="sr-only" onchange="onEditBarcodeTypeChange('scanned')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        Scanné
                    </label>
                    <label id="edit_btn_internal" class="flex-1 flex items-center justify-center gap-2 cursor-pointer px-3 py-2 rounded-xl border-2 border-slate-200 bg-slate-50 text-slate-500 font-semibold text-sm transition-all">
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
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Unité de vente</label>
                <select name="unit" id="edit_product_unit" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required>
                    <option value="unité">📦 Pièce (Unité)</option>
                    <option value="metre">📏 Mètre (m)</option>
                    <option value="cm">📐 Centimètre (cm)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tailles (virgules)</label>
                    <input type="text" name="sizes_text" id="edit_sizes_text" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" placeholder="S,M,L ou 38,40,42">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Couleurs (virgules)</label>
                    <input type="text" name="colors_text" id="edit_colors_text" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" placeholder="Noir,Blanc,Rouge">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Stock par nouvelle variante (optionnel)</label>
                <input type="number" name="stock_per_variant" step="0.001" min="0" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" placeholder="0">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition-colors" onclick="closeModal('editProductModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="adjustStockModal">
    <div class="modal-content glass-panel w-full max-w-sm rounded-3xl shadow-2xl border border-white/70 p-6 relative">
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
                <label class="block text-sm font-semibold text-slate-700 mb-1">Quantité <span id="adjust_unit_badge" class="ml-2 text-xs bg-slate-200 px-2 py-0.5 rounded text-slate-600">unités</span></label>
                <input type="number" name="quantity" step="0.001" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-lg font-bold rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required min="0">
                <p class="text-[10px] text-slate-400 mt-1">Accepte les décimales (ex: 1.5 pour 1m 50cm)</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Motif (optionnel)</label>
                <input type="text" name="reason" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" placeholder="Ex: Inventaire, Casse...">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition-colors" onclick="closeModal('adjustStockModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-bold text-amber-900 bg-amber-400 hover:bg-amber-500 rounded-lg shadow-sm transition-colors">Ajuster</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="addSupplierModal">
    <div class="modal-content glass-panel w-full max-w-sm rounded-3xl shadow-2xl border border-white/70 p-6 relative">
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
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition-colors" onclick="closeModal('addSupplierModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg shadow-sm transition-colors">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editSupplierModal">
    <div class="modal-content glass-panel w-full max-w-sm rounded-3xl shadow-2xl border border-white/70 p-6 relative">
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
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition-colors" onclick="closeModal('editSupplierModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="addCategoryModal">
    <div class="modal-content glass-panel w-full max-w-sm rounded-3xl shadow-2xl border border-white/70 p-6 relative">
        <h3 class="text-xl font-bold text-slate-900 mb-5">Ajouter une catégorie</h3>
        <form method="POST" action="{{ route('pos.stock.add-category') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nom de la catégorie</label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 outline-none" required placeholder="Ex: Produits d'entretien">
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition-colors" onclick="closeModal('addCategoryModal')">Annuler</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg shadow-sm transition-colors">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editCategoryModal">
    <div class="modal-content glass-panel w-full max-w-sm rounded-3xl shadow-2xl border border-white/70 p-6 relative">
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
                <button type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-2xl transition-colors" onclick="closeModal('editCategoryModal')">Annuler</button>
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
        // Clean up the numeric keyboard's keydown listener when that modal closes.
        if (modalId === 'numericKeyboardModal' && window.numericKeyboardHandler) {
            document.removeEventListener('keydown', window.numericKeyboardHandler);
        }
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

    var BASE_EDIT_CATEGORY = '{{ url("/pos/stock/edit-category") }}';
    function editCategory(category) {
        document.getElementById('edit_category_id').value = category.id;
        document.getElementById('edit_category_name').value = category.name;
        document.getElementById('editCategoryForm').action = BASE_EDIT_CATEGORY + '/' + category.id;
        openModal('editCategoryModal');
    }

    var BASE_EDIT_PRODUCT = '{{ url("/pos/stock/edit-product") }}';
    function editProduct(product) {
        document.getElementById('edit_product_id').value = product.id;
        document.getElementById('edit_product_name').value = product.name;
        document.getElementById('edit_product_brand').value = product.brand || '';
        document.getElementById('edit_product_barcode').value = product.barcode;
        document.getElementById('edit_product_category').value = product.category_id;
        document.getElementById('edit_product_supplier').value = product.supplier_id || '';
        document.getElementById('edit_product_purchase_price').value = product.purchase_price;
        document.getElementById('edit_product_sale_price').value = product.sale_price;
        document.getElementById('edit_product_unit').value = product.unit || 'unité';
        var variantSizes = (product.variants || [])
            .map(v => v.size)
            .filter(v => v)
            .filter((v, i, arr) => arr.indexOf(v) === i)
            .join(',');
        var variantColors = (product.variants || [])
            .map(v => v.color)
            .filter(v => v)
            .filter((v, i, arr) => arr.indexOf(v) === i)
            .join(',');
        document.getElementById('edit_sizes_text').value = variantSizes;
        document.getElementById('edit_colors_text').value = variantColors;
        document.getElementById('editProductForm').action = BASE_EDIT_PRODUCT + '/' + product.id;

        // Set barcode_type toggle
        var barcodeType = product.barcode_type || 'internal';
        document.getElementById('edit_type_scanned').checked = (barcodeType === 'scanned');
        document.getElementById('edit_type_internal').checked = (barcodeType === 'internal');
        onEditBarcodeTypeChange(barcodeType);

        openModal('editProductModal');
    }

    var BASE_ADJUST_STOCK = '{{ url("/pos/stock/adjust-stock") }}';
    function adjustStock(product) {
        document.getElementById('adjust_product_id').value = product.id;
        document.getElementById('adjust_product_name').value = product.name;
        
        var unitLabel = 'unités';
        if (product.unit === 'metre') unitLabel = 'mètres';
        if (product.unit === 'cm') unitLabel = 'cm';
        if (product.unit === 'unité') unitLabel = 'pièces';
        
        document.getElementById('adjust_unit_badge').textContent = unitLabel;
        
        var stockFormatted = product.stock_quantity;
        if (product.unit !== 'unité') {
            stockFormatted = parseFloat(product.stock_quantity).toFixed(3);
        }
        
        document.getElementById('adjust_current_stock').value = stockFormatted + ' ' + unitLabel;
        document.getElementById('adjustStockForm').action = BASE_ADJUST_STOCK + '/' + product.id;
        openModal('adjustStockModal');
    }

    var BASE_EDIT_SUPPLIER = '{{ url("/pos/stock/edit-supplier") }}';
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
        var searchTerm = document.getElementById('searchProduct').value.toLowerCase();
        var categoryFilter = document.getElementById('filterCategory').value.toLowerCase();
        var statusFilter = document.getElementById('filterStatus').value;
        
        var table = document.getElementById('productsTable');
        var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (var row of rows) {
            if (row.cells.length <= 1) continue;
            
            var productName = row.cells[0].textContent.toLowerCase();
            var barcode = row.cells[1].textContent.toLowerCase();
            var category = row.cells[3].textContent.toLowerCase(); // Category is index 3 in Products table
            var statusBadge = row.cells[8].querySelector('.badge'); // Status is index 8 in Products table
            var status = statusBadge ? statusBadge.textContent.trim() : '';
            
            var matchesSearch = productName.includes(searchTerm) || barcode.includes(searchTerm);
            var matchesCategory = !categoryFilter || category.includes(categoryFilter);
            var matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function filterSuppliers() {
        var searchTerm = document.getElementById('searchSupplier').value.toLowerCase();
        var table = document.querySelector('#suppliers-tab table');
        var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (var row of rows) {
            if (row.cells.length <= 1) continue;
            
            var name = row.cells[0].textContent.toLowerCase();
            var contact = row.cells[1].textContent.toLowerCase();
            
            if (name.includes(searchTerm) || contact.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function filterCategories() {
        var searchTerm = document.getElementById('searchCategory').value.toLowerCase();
        var table = document.querySelector('#categories-tab table');
        var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (var row of rows) {
            if (row.cells.length <= 1) continue;
            
            var name = row.cells[0].textContent.toLowerCase();
            
            if (name.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    // Simple product lookup map for modal actions
    @php
        $products_for_js = $products->map(function($p) {
            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'barcode'        => $p->barcode,
                'barcode_type'   => $p->barcode_type ?? 'internal',
                'stock_quantity' => $p->stock_quantity,
                'sale_price'     => $p->sale_price,
            ];
        });
    @endphp
    var PRODUCTS = {!! $products_for_js->toJson() !!};

    // ── AZERTY → Numérique (pour les scanners de codes-barres sur clavier FR) ──
    // Un scanner se comporte comme un clavier. Sur un clavier AZERTY, les touches
    // du rang supérieur envoient des symboles (&, é, ", etc.) au lieu de chiffres.
    // Ce mapping corrige automatiquement ces symboles en chiffres.
    var azertyMap = {
        '&': '1', 'é': '2', '"': '3', "'": '4', '(': '5',
        '-': '6', 'è': '7', '_': '8', 'ç': '9', 'à': '0',
        ')': '°', '=': '+', '§': '!'
    };

    function applyAzertyMap(value) {
        var corrected = '';
        var changed = false;
        for (var char of value) {
            if (azertyMap[char] !== undefined) {
                corrected += azertyMap[char];
                changed = true;
            } else {
                corrected += char;
            }
        }
        return changed ? corrected : null; // returns null if no change needed
    }

    // Apply to inputs
    document.addEventListener('DOMContentLoaded', function () {
        // Apply to main Product Search
        var searchProductInput = document.getElementById('searchProduct');
        if (searchProductInput) {
            searchProductInput.addEventListener('input', function () {
                var corrected = applyAzertyMap(this.value);
                if (corrected !== null) {
                    this.value = corrected;
                    filterProducts(); // Trigger search update
                }
            });
        }

        var searchSupplierInput = document.getElementById('searchSupplier');
        if (searchSupplierInput) {
            searchSupplierInput.addEventListener('input', function () {
                var corrected = applyAzertyMap(this.value);
                if (corrected !== null) {
                    this.value = corrected;
                    filterSuppliers();
                }
            });
        }

        var searchCategoryInput = document.getElementById('searchCategory');
        if (searchCategoryInput) {
            searchCategoryInput.addEventListener('input', function () {
                var corrected = applyAzertyMap(this.value);
                if (corrected !== null) {
                    this.value = corrected;
                    filterCategories();
                }
            });
        }

        var addBarcodeInput = document.getElementById('add_barcode_input');
        if (addBarcodeInput) {
            addBarcodeInput.addEventListener('input', function () {
                var corrected = applyAzertyMap(this.value);
                if (corrected !== null) this.value = corrected;
            });
        }

        // Apply to Edit Product barcode input
        var editBarcodeInput = document.getElementById('edit_product_barcode');
        if (editBarcodeInput) {
            editBarcodeInput.addEventListener('input', function () {
                var corrected = applyAzertyMap(this.value);
                if (corrected !== null) this.value = corrected;
            });
        }
    });

    // ── Barcode type toggle for ADD PRODUCT modal ──────────────────────
    function onBarcodeTypeChange(type) {
        var wrapper = document.getElementById('barcode_input_wrapper');
        var notice  = document.getElementById('barcode_auto_notice');
        var btnScanned  = document.getElementById('btn_scanned');
        var btnInternal = document.getElementById('btn_internal');
        var input = document.getElementById('add_barcode_input');

        if (type === 'scanned') {
            if (wrapper) wrapper.classList.remove('hidden');
            if (notice) notice.classList.add('hidden');
            if (btnScanned) {
                btnScanned.className = 'flex items-center justify-center gap-1.5 cursor-pointer py-1.5 px-3 rounded-lg text-xs font-bold transition-all border-2 border-slate-900 bg-white text-slate-900 shadow-xs';
            }
            if (btnInternal) {
                btnInternal.className = 'flex items-center justify-center gap-1.5 cursor-pointer py-1.5 px-3 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-500 hover:text-slate-800';
            }
            if (input) input.focus();
        } else {
            if (wrapper) wrapper.classList.add('hidden');
            if (notice) notice.classList.remove('hidden');
            if (btnInternal) {
                btnInternal.className = 'flex items-center justify-center gap-1.5 cursor-pointer py-1.5 px-3 rounded-lg text-xs font-bold transition-all border-2 border-slate-900 bg-white text-slate-900 shadow-xs';
            }
            if (btnScanned) {
                btnScanned.className = 'flex items-center justify-center gap-1.5 cursor-pointer py-1.5 px-3 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-500 hover:text-slate-800';
            }
        }
    }

    // ── Interactive Tag System for Brands, Sizes, and Colors ───────────
    var selectedBrands = ['Nike', 'Zara'];
    var selectedSizes  = ['S', 'M', 'L'];
    var selectedColors = [
        { name: 'Bleu', hex: '#2563eb' },
        { name: 'Rouge', hex: '#dc2626' }
    ];

    var knownColorMap = {
        'noir': '#0f172a',
        'blanc': '#ffffff',
        'bleu': '#2563eb',
        'rouge': '#dc2626',
        'vert': '#16a34a',
        'gris': '#64748b',
        'beige': '#d4b996',
        'rose': '#ec4899',
        'jaune': '#eab308',
        'marron': '#78350f',
        'kaki': '#57534e',
        'violet': '#9333ea',
        'orange': '#f97316',
        'turquoise': '#06b6d4',
        'bordeaux': '#800020',
        'menthe': '#a7f3d0',
        'corail': '#fb7185',
        'olive': '#65a30d',
        'marine': '#1e3a8a'
    };

    // Load persisted custom colors from database into knownColorMap
    @if(isset($customColors) && is_array($customColors))
        @foreach($customColors as $cc)
            knownColorMap['{{ strtolower($cc['name']) }}'] = '{{ $cc['hex'] }}';
        @endforeach
    @endif

    // ── Custom Color Creation Functions ────────────────────────────────
    window.openColorCreatorModal = function() {
        var picker = document.getElementById('custom_color_picker');
        var hexInput = document.getElementById('custom_color_hex');
        var nameInput = document.getElementById('custom_color_name');
        
        if (picker) picker.value = '#06b6d4';
        if (hexInput) hexInput.value = '#06B6D4';
        if (nameInput) {
            nameInput.value = '';
            setTimeout(() => nameInput.focus(), 150);
        }
        openModal('createColorModal');
    };

    window.onCustomColorPickerChange = function(hex) {
        var hexInput = document.getElementById('custom_color_hex');
        if (hexInput) hexInput.value = hex.toUpperCase();
    };

    window.onCustomColorHexChange = function(hex) {
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            var picker = document.getElementById('custom_color_picker');
            if (picker) picker.value = hex;
        }
    };

    window.setPresetInColorCreator = function(name, hex) {
        var nameInput = document.getElementById('custom_color_name');
        var picker = document.getElementById('custom_color_picker');
        var hexInput = document.getElementById('custom_color_hex');
        
        if (nameInput) nameInput.value = name;
        if (picker) picker.value = hex;
        if (hexInput) hexInput.value = hex.toUpperCase();
    };

    window.showStockToast = function(message) {
        var toast = document.getElementById('stock_toast');
        var msgElem = document.getElementById('stock_toast_message');
        if (!toast || !msgElem) return;

        msgElem.textContent = message;
        toast.classList.remove('opacity-0', 'translate-y-[-20px]', 'pointer-events-none');
        toast.classList.add('opacity-100', 'translate-y-0');

        setTimeout(function() {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-[-20px]', 'pointer-events-none');
        }, 3500);
    };

    window.saveNewCustomColor = function() {
        var nameInput = document.getElementById('custom_color_name');
        var hexInput = document.getElementById('custom_color_hex');
        var btn = document.getElementById('btn_save_custom_color');

        var name = nameInput ? nameInput.value.trim() : '';
        var hex = hexInput ? hexInput.value.trim() : '#06B6D4';

        if (!name) {
            if (nameInput) {
                nameInput.classList.add('ring-2', 'ring-rose-500');
                nameInput.focus();
                setTimeout(() => nameInput.classList.remove('ring-2', 'ring-rose-500'), 2000);
            }
            return;
        }

        if (!/^#[0-9A-Fa-f]{6}$/i.test(hex)) {
            hex = '#06B6D4';
        }

        // Capitalize color name
        var cleanName = name.charAt(0).toUpperCase() + name.slice(1);

        // 1. Immediately register in known color map
        knownColorMap[cleanName.toLowerCase()] = hex.toLowerCase();

        // 2. Select this color tag immediately
        if (!selectedColors.some(c => c.name.toLowerCase() === cleanName.toLowerCase())) {
            selectedColors.push({ name: cleanName, hex: hex });
            renderColorTags();
        }

        // 3. Dynamically insert swatch button into quick swatches if not existing
        var swatchesContainer = document.getElementById('color_quick_swatches');
        if (swatchesContainer) {
            var existingSwatch = swatchesContainer.querySelector(`[data-color="${cleanName}"]`);
            if (!existingSwatch) {
                var newBtn = document.createElement('button');
                newBtn.type = 'button';
                newBtn.className = 'color-swatch-btn relative w-6 h-6 rounded-full border border-slate-300 shadow-xs hover:scale-110 active:scale-95 transition-all flex items-center justify-center ring-2 ring-slate-900 ring-offset-2 scale-110 shrink-0';
                newBtn.setAttribute('data-color', cleanName);
                newBtn.setAttribute('data-hex', hex);
                newBtn.title = cleanName;
                newBtn.style.backgroundColor = hex;
                newBtn.onclick = function() { toggleColorTag(cleanName, hex); };
                
                var isLight = ['blanc', 'beige', 'jaune', 'menthe', 'lavande'].includes(cleanName.toLowerCase());
                newBtn.innerHTML = `<span class="swatch-check w-1.5 h-1.5 rounded-full ${isLight ? 'bg-slate-900' : 'bg-white'}"></span>`;
                
                // Insert right before the last "+" button
                var lastPlusBtn = swatchesContainer.lastElementChild;
                if (lastPlusBtn) {
                    swatchesContainer.insertBefore(newBtn, lastPlusBtn);
                } else {
                    swatchesContainer.appendChild(newBtn);
                }
            }
        }

        // 4. Save asynchronously to database
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75');
        }

        fetch('{{ url("/pos/stock/save-color") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: cleanName,
                hex: hex
            })
        })
        .then(res => res.json())
        .then(data => {
            closeModal('createColorModal');
            showStockToast(`Couleur "${cleanName}" enregistrée avec succès!`);
        })
        .catch(err => {
            console.error('Error saving custom color:', err);
            closeModal('createColorModal');
            showStockToast(`Couleur "${cleanName}" ajoutée.`);
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
            }
        });
    };

    function getBrandIcon(name) {
        var n = name.toLowerCase();
        if (n === 'nike') {
            return `<svg class="w-3.5 h-3.5 text-slate-900 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M21.707 5.293a1 1 0 0 0-1.414 0L8 17.586l-4.293-4.293a1 1 0 0 0-1.414 1.414l5 5a1 1 0 0 0 1.414 0l13-13a1 1 0 0 0 0-1.414z"/></svg>`;
        }
        if (n === 'zara') {
            return `<span class="px-1.5 py-0.5 text-[9px] font-black bg-slate-900 text-white rounded shrink-0">Z</span>`;
        }
        if (n === 'adidas') {
            return `<svg class="w-3.5 h-3.5 text-slate-900 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18h3l-3-8H3l3 8zm6 0h3l-4.5-12h-3L12 18zm6 0h3l-6-16h-3l6 16z"/></svg>`;
        }
        if (n === 'puma') {
            return `<span class="px-1.5 py-0.5 text-[9px] font-black bg-slate-900 text-white rounded shrink-0">P</span>`;
        }
        return `<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>`;
    }

    // ── Numeric Keyboard Handlers ──────────────────────────────────────
    var numericTargetInput = null;
    var azertyKeyMap = {
        '&': '1', 'é': '2', '"': '3', "'": '4', '(': '5',
        '-': '6', 'è': '7', '_': '8', 'ç': '9', 'à': '0',
        ')': '°', '=': '+', '§': '!'
    };

    window.openNumericKeyboard = function(inputId) {
        numericTargetInput = document.getElementById(inputId);
        var currentVal = numericTargetInput ? numericTargetInput.value : '0';
        var inputElem = document.getElementById('numericKeyboardInput');
        if (inputElem) inputElem.value = currentVal || '0';
        openModal('numericKeyboardModal');
        document.addEventListener('keydown', window.numericKeyboardHandler);
    };

    window.numericKeyboardHandler = function(e) {
        var modal = document.getElementById('numericKeyboardModal');
        if (!modal || !modal.classList.contains('active')) return;

        var key = e.key;
        if (azertyKeyMap[key]) {
            key = azertyKeyMap[key];
        }

        if ((key >= '0' && key <= '9') || key === '.' || key === ',') {
            if (key === ',') key = '.';
            appendNumericKey(key);
        } else if (key === 'Backspace') {
            var input = document.getElementById('numericKeyboardInput');
            if (input) input.value = input.value.slice(0, -1);
        } else if (key === 'Enter') {
            confirmNumericKey();
        } else if (key === 'Escape') {
            closeModal('numericKeyboardModal');
        }
    };

    window.appendNumericKey = function(digit) {
        var input = document.getElementById('numericKeyboardInput');
        if (!input) return;
        if (digit === '.') {
            if (input.value.includes('.')) return;
            if (input.value === '') input.value = '0';
        }
        if (input.value.length < 10) {
            input.value = (input.value === '0' && digit !== '.') ? digit : input.value + digit;
        }
    };

    window.clearNumericKey = function() {
        var input = document.getElementById('numericKeyboardInput');
        if (input) input.value = '';
    };

    window.confirmNumericKey = function() {
        if (numericTargetInput) {
            var input = document.getElementById('numericKeyboardInput');
            var val = input ? input.value : '0';
            if (val === '' || isNaN(val)) val = '0';
            numericTargetInput.value = val;
        }
        closeModal('numericKeyboardModal');
    };

    function renderBrandTags() {
        var container = document.getElementById('selected_brand_tags');
        var hiddenInput = document.getElementById('add_product_brand');
        if (!container || !hiddenInput) return;

        container.innerHTML = '';
        selectedBrands.forEach(function(b) {
            var tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold bg-white text-slate-800 border border-slate-200 rounded-lg shadow-xs animate-in fade-in zoom-in-95';
            tag.innerHTML = `
                ${getBrandIcon(b)}
                <span>${b}</span>
                <button type="button" class="text-slate-400 hover:text-rose-500 ml-0.5" onclick="removeBrandTag('${b}')">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            container.appendChild(tag);
        });

        hiddenInput.value = selectedBrands.join(', ');

        // Update brand quick presets appearance
        document.querySelectorAll('#brand_quick_presets .brand-chip').forEach(function(chip) {
            var brandName = chip.textContent.trim();
            if (selectedBrands.includes(brandName)) {
                chip.className = 'brand-chip px-2 py-0.5 text-[11px] font-bold bg-slate-900 text-white border border-slate-900 rounded-lg shadow-xs transition-all';
            } else {
                chip.className = 'brand-chip px-2 py-0.5 text-[11px] font-semibold bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg transition-all';
            }
        });
    }

    function toggleBrandTag(brandName) {
        var idx = selectedBrands.indexOf(brandName);
        if (idx > -1) {
            selectedBrands.splice(idx, 1);
        } else {
            selectedBrands.push(brandName);
        }
        renderBrandTags();
    }

    function removeBrandTag(brandName) {
        selectedBrands = selectedBrands.filter(b => b !== brandName);
        renderBrandTags();
    }

    function renderSizeTags() {
        var container = document.getElementById('selected_size_tags');
        var hiddenInput = document.getElementById('add_sizes_text');
        if (!container || !hiddenInput) return;

        container.innerHTML = '';
        selectedSizes.forEach(function(s) {
            var tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-black bg-white text-slate-900 border border-slate-200 rounded-lg shadow-xs animate-in fade-in zoom-in-95';
            tag.innerHTML = `
                <span>${s}</span>
                <button type="button" class="text-slate-400 hover:text-rose-500 ml-0.5" onclick="removeSizeTag('${s}')">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            container.appendChild(tag);
        });

        hiddenInput.value = selectedSizes.join(',');

        // Update size quick chips
        document.querySelectorAll('#size_quick_chips .size-chip').forEach(function(chip) {
            var s = chip.getAttribute('data-size');
            if (selectedSizes.includes(s)) {
                chip.className = 'size-chip px-2.5 py-0.5 text-xs font-bold rounded-lg border bg-slate-900 text-white border-slate-900 shadow-xs transition-all';
            } else {
                chip.className = 'size-chip px-2.5 py-0.5 text-xs font-bold rounded-lg border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 transition-all';
            }
        });
    }

    function toggleSizeTag(sizeName) {
        var idx = selectedSizes.indexOf(sizeName);
        if (idx > -1) {
            selectedSizes.splice(idx, 1);
        } else {
            selectedSizes.push(sizeName);
        }
        renderSizeTags();
    }

    function removeSizeTag(sizeName) {
        selectedSizes = selectedSizes.filter(s => s !== sizeName);
        renderSizeTags();
    }

    function renderColorTags() {
        var container = document.getElementById('selected_color_tags');
        var hiddenInput = document.getElementById('add_colors_text');
        if (!container || !hiddenInput) return;

        container.innerHTML = '';
        selectedColors.forEach(function(c) {
            var tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold bg-white text-slate-800 border border-slate-200 rounded-lg shadow-xs animate-in fade-in zoom-in-95';
            tag.innerHTML = `
                <span class="w-2.5 h-2.5 rounded-full border border-black/10 shrink-0" style="background-color: ${c.hex}"></span>
                <span>${c.name}</span>
                <button type="button" class="text-slate-400 hover:text-rose-500 ml-0.5" onclick="removeColorTag('${c.name}')">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            container.appendChild(tag);
        });

        hiddenInput.value = selectedColors.map(c => c.name).join(',');

        // Update swatch visual state
        document.querySelectorAll('#color_quick_swatches .color-swatch-btn').forEach(function(btn) {
            var colorName = btn.getAttribute('data-color');
            var isSelected = selectedColors.some(c => c.name.toLowerCase() === colorName.toLowerCase());
            var checkSpan = btn.querySelector('.swatch-check');

            if (isSelected) {
                btn.classList.add('ring-2', 'ring-slate-900', 'ring-offset-2', 'scale-110');
                if (checkSpan) checkSpan.classList.remove('hidden');
            } else {
                btn.classList.remove('ring-2', 'ring-slate-900', 'ring-offset-2', 'scale-110');
                if (checkSpan) checkSpan.classList.add('hidden');
            }
        });
    }

    function toggleColorTag(colorName, colorHex) {
        var idx = selectedColors.findIndex(c => c.name.toLowerCase() === colorName.toLowerCase());
        if (idx > -1) {
            selectedColors.splice(idx, 1);
        } else {
            var hex = colorHex || knownColorMap[colorName.toLowerCase()] || '#64748b';
            selectedColors.push({ name: colorName, hex: hex });
        }
        renderColorTags();
    }

    function removeColorTag(colorName) {
        selectedColors = selectedColors.filter(c => c.name !== colorName);
        renderColorTags();
    }

    // Initialize custom inputs and tags on load
    document.addEventListener('DOMContentLoaded', function() {
        renderBrandTags();
        renderSizeTags();
        renderColorTags();

        // Custom Brand Input
        var brandInput = document.getElementById('brand_custom_input');
        if (brandInput) {
            brandInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    var val = this.value.trim().replace(/^,+|,+$/g, '');
                    if (val && !selectedBrands.includes(val)) {
                        selectedBrands.push(val);
                        renderBrandTags();
                    }
                    this.value = '';
                }
            });
        }

        // Custom Size Input
        var sizeInput = document.getElementById('size_custom_input');
        if (sizeInput) {
            sizeInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    var val = this.value.trim().toUpperCase().replace(/^,+|,+$/g, '');
                    if (val && !selectedSizes.includes(val)) {
                        selectedSizes.push(val);
                        renderSizeTags();
                    }
                    this.value = '';
                }
            });
        }

        // Custom Color Input
        var colorInput = document.getElementById('color_custom_input');
        if (colorInput) {
            colorInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    var val = this.value.trim().replace(/^,+|,+$/g, '');
                    if (val) {
                        var capitalVal = val.charAt(0).toUpperCase() + val.slice(1);
                        var hex = knownColorMap[val.toLowerCase()] || '#64748b';
                        if (!selectedColors.some(c => c.name.toLowerCase() === val.toLowerCase())) {
                            selectedColors.push({ name: capitalVal, hex: hex });
                            renderColorTags();
                        }
                    }
                    this.value = '';
                }
            });
        }
    });

    // ── Barcode type toggle for EDIT PRODUCT modal ─────────────────────
    function onEditBarcodeTypeChange(type) {
        var wrapper = document.getElementById('edit_barcode_input_wrapper');
        var notice  = document.getElementById('edit_barcode_auto_notice');
        var btnScanned  = document.getElementById('edit_btn_scanned');
        var btnInternal = document.getElementById('edit_btn_internal');
        var visibleInput = document.getElementById('edit_product_barcode');
        var hiddenInput  = document.getElementById('edit_product_barcode_hidden');

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

    var currentDetailProduct = null;

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
        var id  = document.getElementById('print_product_id').value;
        var qty = document.getElementById('print_quantity_input').value;
        var url = '{{ route("pos.barcode.print") }}?product_id=' + encodeURIComponent(id) + '&quantity=' + encodeURIComponent(qty);
        window.open(url, '_blank');
        closeModal('printBarcodeModal');
    }

    // If a new product was just created, open the print modal automatically
    @if(session('new_product_id'))
        (function() {
            var newId = {{ session('new_product_id') }};
            var p = PRODUCTS.find(x => x.id === newId);
            if (p) {
                document.getElementById('print_product_id').value = p.id;
                document.getElementById('print_product_name').textContent = p.name;
                document.getElementById('print_quantity_input').value = '1';
                openModal('printBarcodeModal');
            }
        })();
    @endif
</script>
@endsection




