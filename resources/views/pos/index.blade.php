@extends('layouts.pos')
@section('title', 'Système POS')
@push('styles')
<style>
/* Hide number input arrows */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
.products-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr)) !important;
    gap: 0.75rem;
    align-content: start;
}
</style>
@endpush
@section('content')

    @php
        $preparedCategories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'barcode' => $product->barcode,
                        'category_id' => $product->category_id,
                        'price' => (float) ($product->sale_price ?? $product->price ?? 0),
                        'purchase_price' => (float) ($product->purchase_price ?? 0),
                        'stock_quantity' => (float) $product->stock_quantity,
                        'unit' => $product->unit ?? 'unité',
                        'image_url' => $product->image_url,
                        'variants' => $product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'size' => $variant->size,
                                'color' => $variant->color,
                                'barcode' => $variant->barcode,
                                'stock_quantity' => (float) $variant->stock_quantity,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();
    @endphp

    

    @if(session('success'))
        <div id="flash-success"
            class="absolute top-20 left-1/2 -translate-x-1/2 z-50 bg-emerald-500 text-white px-6 py-3 rounded-2xl shadow-xl shadow-emerald-200/50 flex items-center gap-3 font-semibold animate-bounce">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
        <script>setTimeout(() => { var el = document.getElementById('flash-success'); if (el) el.style.display = 'none'; }, 4000);</script>
    @endif

    @if(session('error'))
        <div id="flash-error"
            class="absolute top-20 left-1/2 -translate-x-1/2 z-50 bg-rose-500 text-white px-6 py-3 rounded-2xl shadow-xl shadow-rose-200/50 flex items-center gap-3 font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('error') }}
        </div>
        <script>setTimeout(() => { var el = document.getElementById('flash-error'); if (el) el.style.display = 'none'; }, 6000);</script>
    @endif

    <div id="variant-picker-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-card rounded-3xl shadow-2xl border border-white/50 w-full max-w-md max-h-[80vh] flex flex-col overflow-hidden">
            <div class="p-5 border-b border-border/60 flex items-center justify-between gap-3">
                <h3 id="variant-picker-title" class="text-base font-black text-card-foreground leading-snug">Choisir une variante</h3>
                <button type="button" id="variant-picker-close"
                    class="w-8 h-8 shrink-0 flex items-center justify-center rounded-xl hover:bg-muted/30 text-muted-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div id="variant-picker-list" class="flex-1 overflow-y-auto p-4 space-y-2"></div>
        </div>
    </div>

    <div
        class="flex-1 p-3 flex flex-row gap-3 h-full min-h-0 w-full overflow-hidden">

        <!-- Left Sidebar: Categories -->
        <aside class="glass-panel rounded-3xl p-1 shadow-sm border border-white/50 w-44 xl:w-52 shrink-0 flex flex-col overflow-hidden">
            <div class="px-4 py-3 shrink-0">
                <h2 class="text-[10px] font-bold text-muted-foreground/70 uppercase tracking-widest mb-0.5">Navigation</h2>
                <p class="text-lg font-extrabold text-card-foreground">Catégories</p>
            </div>
            <div class="category-list flex-1 overflow-y-auto px-1.5 pb-2 space-y-1" id="category-list">
                <!-- Categories injected by JS -->
            </div>
        </aside>

        <!-- Main Content: Products & Search -->
        <main class="flex-1 min-w-0 flex flex-col gap-2.5 min-h-0 overflow-hidden">
            <div class="glass-panel rounded-3xl px-4 py-3 shadow-sm border border-white/50 flex items-center justify-between gap-3 shrink-0">
                <div class="min-w-0">
                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60">Catalogue instantané</p>
                    <h3 class="text-lg sm:text-xl font-black text-card-foreground leading-tight truncate">Choisissez un produit et encaissez vite</h3>
                </div>
                <div class="hidden 2xl:flex items-center gap-2 rounded-full bg-indigo-50 text-indigo-700 px-3.5 py-1.5 text-xs font-bold whitespace-nowrap">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    Recherche, scan et panier en un seul écran
                </div>
            </div>

            <!-- Search & Scanner Bar -->
            <div class="glass-panel rounded-3xl p-2.5 shadow-sm border border-white/50 shrink-0">
                <div class="flex gap-2">
                    <div class="relative flex-1 group">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-muted-foreground/70 group-focus-within:text-indigo-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <input id="barcode-input" type="text"
                            class="pl-10 w-full h-11 bg-muted/20/50 border border-border text-foreground text-sm font-semibold rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-card block transition-all placeholder:text-muted-foreground/70"
                            placeholder="Scanner ou rechercher..." autofocus>
                    </div>

                    <div class="flex items-center relative w-24 group">
                        <div
                            class="absolute left-2.5 text-[10px] font-bold text-muted-foreground/70 group-focus-within:text-indigo-600 pointer-events-none z-10 transition-colors">
                            QTÉ</div>
                        <input id="scan-quantity" type="number" min="0.001" step="0.001" value="1"
                            class="w-full h-11 pl-8 pr-2 bg-muted/20/50 border border-border rounded-2xl text-center font-bold text-card-foreground focus:ring-2 focus:ring-indigo-500 focus:bg-card focus:border-transparent outline-none transition-all text-sm">
                    </div>

                    <button type="button" id="scan-btn"
                        class="h-11 px-4 sm:px-5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-1.5 text-sm shrink-0">
                        <span>Ajouter</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="flex justify-between items-center mt-2 px-1.5">
                    <div id="active-category-name"
                        class="text-xs font-bold text-muted-foreground flex items-center gap-1.5 truncate">
                        <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
                        Sélectionnez une catégorie
                    </div>
                    <div class="text-[11px] font-medium text-muted-foreground/70 bg-muted/30 px-2.5 py-0.5 rounded-full shrink-0">
                        Entrée pour ajouter
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div
                class="glass-panel rounded-3xl p-3 shadow-sm border border-white/50 flex-1 overflow-hidden flex flex-col min-h-0">
                <div id="products-grid"
                    class="products-grid overflow-y-auto pr-1 pb-1 content-start h-full">
                    <!-- Products injected by JS -->
                </div>
            </div>
        </main>

        <!-- Right Panel: Cart -->
        <section
            class="glass-panel rounded-3xl shadow-lg border border-white/50 w-72 lg:w-80 xl:w-[360px] shrink-0 flex flex-col min-h-0 relative overflow-hidden ring-1 ring-black/5">
            <div class="p-4 pb-3 border-b border-border/60 bg-card/50 backdrop-blur-md z-10">
                <h2 class="text-lg font-extrabold text-foreground flex items-center gap-2.5">
                    <div class="p-1.5 bg-emerald-100 text-emerald-600 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    Panier
                </h2>
            </div>

            @if ($errors->any())
                <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mt-0.5 shrink-0" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-semibold text-red-600 leading-snug">{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form"
                class="flex flex-col flex-1 min-h-0 overflow-hidden relative">
                @csrf

                <div class="flex-1 overflow-y-auto p-2 scroll-smooth">
                    <table class="w-full text-left border-collapse">
                        <tbody id="cart-body" class="space-y-2 block">
                            <!-- Cart items injected by JS -->
                        </tbody>
                    </table>
                    <div id="empty-cart-msg"
                        class="hidden flex-col items-center justify-center h-full text-center p-8 opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-300 mb-4" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="font-bold text-muted-foreground/70">Le panier est vide</p>
                        <p class="text-sm text-muted-foreground/70 mt-1">Scanner un produit pour commencer</p>
                    </div>
                </div>

                <!-- Footer / Total -->
                <div class="p-6 bg-card border-t border-border/60 z-10 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <span
                                class="block text-xs font-bold uppercase tracking-widest text-muted-foreground/70 mb-1">Total
                                à payer</span>
                            <div
                                class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded inline-block">
                                TVA Incluse</div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-baseline gap-1">
                                <span id="cart-total"
                                    class="text-4xl font-black text-card-foreground tracking-tight leading-none">0.00</span>
                                <span class="text-xl font-bold text-muted-foreground/70">{{ $currencySymbol }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 mb-6 p-4 bg-muted/20 rounded-2xl border border-border/40">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground/70">Montant reçu</span>
                            <div class="relative w-32">
                                <input type="number" id="received-amount" step="0.01" min="0" placeholder="0.00"
                                    class="w-full h-9 pl-3 pr-8 bg-card border border-border rounded-xl text-right font-bold text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-muted-foreground/50">{{ $currencySymbol }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground/70">À rendre</span>
                            <div class="flex items-baseline gap-1 mr-1">
                                <span id="change-amount" class="text-lg font-black text-emerald-600 tracking-tight">0.00</span>
                                <span class="text-xs font-bold text-emerald-600/70">{{ $currencySymbol }}</span>
                            </div>
                        </div>
                    </div>

                    <div id="hidden-items"></div>
                    <input type="hidden" name="payment_method" value="cash">

                    <button type="submit"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-lg py-4 rounded-2xl shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-3 group active:scale-[0.98]">
                        <span>Valider & Imprimer</span>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-slate-400 group-hover:text-white transition-colors" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
(function() {
var categories = @json($preparedCategories);
        var currencySymbol = @json($currencySymbol);
        var categoryList = document.getElementById('category-list');
        var productsGrid = document.getElementById('products-grid');
        var activeCategoryName = document.getElementById('active-category-name');
        var cartBody = document.getElementById('cart-body');
        var cartTotal = document.getElementById('cart-total');
        var hiddenItems = document.getElementById('hidden-items');
        var barcodeInput = document.getElementById('barcode-input');
        var scanQuantityInput = document.getElementById('scan-quantity');
        var scanBtn = document.getElementById('scan-btn');
        var emptyCartMsg = document.getElementById('empty-cart-msg');
        var receivedAmountInput = document.getElementById('received-amount');
        var changeAmountDisplay = document.getElementById('change-amount');
        var variantPickerModal = document.getElementById('variant-picker-modal');
        var variantPickerTitle = document.getElementById('variant-picker-title');
        var variantPickerList = document.getElementById('variant-picker-list');

        // AZERTY to QWERTY mapping for numbers (Dual support: FR/EN)
        var azertyMap = {
            '&': '1', 'é': '2', '"': '3', "'": '4', '(': '5',
            '-': '6', 'è': '7', '_': '8', 'ç': '9', 'à': '0',
            ')': '°', '=': '+', '§': '!'
        };

        var activeCategoryId = categories.length ? categories[0].id : null;
        var allProducts = categories.flatMap(c => c.products);
        var cart = {};

        function getCategoryById(categoryId) {
            return categories.find((category) => category.id === categoryId);
        }

        function renderCategories() {
            categoryList.innerHTML = '';

            categories.forEach((category) => {
                var button = document.createElement('button');
                button.type = 'button';

                var isActive = category.id === activeCategoryId;

                var baseClasses = 'w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 border flex items-center justify-between group ';
                var activeClasses = isActive
                    ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white border-transparent shadow-lg shadow-indigo-200 scale-[1.02]'
                    : 'bg-card border-border/60 text-muted-foreground hover:border-border hover:bg-muted/20 hover:text-foreground shadow-sm';

                button.className = baseClasses + activeClasses;

                var countBadge = isActive
                    ? `<span class="bg-white/20 text-white px-2 py-0.5 rounded text-[10px] font-bold">${category.products.length}</span>`
                    : `<span class="bg-gray-100 text-slate-500 px-2 py-0.5 rounded text-[10px] font-bold group-hover:bg-gray-200">${category.products.length}</span>`;

                button.innerHTML = `<span>${category.name}</span>${countBadge}`;

                button.addEventListener('click', () => {
                    activeCategoryId = category.id;
                    barcodeInput.value = ''; // Clear search when switching category
                    renderCategories();
                    renderProducts();
                });
                categoryList.appendChild(button);
            });
        }

        function renderProducts() {
            var searchTerm = barcodeInput.value.trim().toLowerCase();
            var productsToDisplay = [];

            if (searchTerm === '') {
                var activeCategory = getCategoryById(activeCategoryId);
                if (!activeCategory) {
                    activeCategoryName.innerHTML = `<span class="w-2 h-2 rounded-full bg-red-400"></span> Aucune catégorie`;
                    productsGrid.innerHTML = `
                        <div class="col-span-full flex flex-col items-center justify-center py-20 opacity-40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            <span class="font-bold text-lg">Aucun produit ici</span>
                        </div>
                    `;
                    return;
                }
                productsToDisplay = activeCategory.products;
                activeCategoryName.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400"></span> ${activeCategory.name} <span class="bg-muted/30 text-muted-foreground px-2 rounded-md ml-2 text-xs font-bold">${activeCategory.products.length}</span>`;
            } else {
                productsToDisplay = allProducts.filter(p => 
                    p.name.toLowerCase().includes(searchTerm) || 
                    p.barcode.toLowerCase().includes(searchTerm)
                );
                activeCategoryName.innerHTML = `<span class="w-2 h-2 rounded-full bg-indigo-500"></span> Recherche: "${searchTerm}" <span class="bg-muted/30 text-muted-foreground px-2 rounded-md ml-2 text-xs font-bold">${productsToDisplay.length}</span>`;
            }

            productsGrid.innerHTML = '';

            if (productsToDisplay.length === 0) {
                productsGrid.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center py-20 opacity-40">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    <span class="font-bold text-lg">Aucun produit trouvé</span>
                </div>
            `;
                return;
            }

            productsToDisplay.forEach((product) => {
                var card = document.createElement('div');
                var hasMultipleVariants = product.variants && product.variants.length > 1;
                var cartQty = 0;
                if (hasMultipleVariants) {
                    product.variants.forEach((v) => { cartQty += cart[v.barcode]?.quantity || 0; });
                } else {
                    cartQty = cart[product.barcode]?.quantity || 0;
                }

                // Modern Product Card
                card.className = 'group bg-white border border-slate-200/90 hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-500/10 rounded-2xl p-2.5 flex flex-col justify-between transition-all duration-200 relative cursor-pointer active:scale-[0.98] select-none h-[225px] overflow-hidden';

                var isLengthProduct = product.unit === 'metre' || product.unit === 'cm';
                var stockFormatted = isLengthProduct ? parseFloat(Number(product.stock_quantity).toFixed(3)) : Math.floor(product.stock_quantity);
                var stockColor = product.stock_quantity > 5 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200 animate-pulse';
                var stockText = product.stock_quantity > 5 ? `Stock: ${stockFormatted}` : `Faible: ${stockFormatted}`;
                var unitBadge = '';
                if (product.unit === 'metre') unitBadge = '<span class="bg-amber-100/90 text-amber-800 px-1.5 py-0.5 rounded text-[9px] font-bold shadow-xs">📏 m</span>';
                if (product.unit === 'cm') unitBadge = '<span class="bg-cyan-100/90 text-cyan-800 px-1.5 py-0.5 rounded text-[9px] font-bold shadow-xs">📐 cm</span>';
                var variantBadge = hasMultipleVariants
                    ? `<span class="bg-indigo-100/90 text-indigo-800 px-1.5 py-0.5 rounded text-[9px] font-bold shadow-xs">${product.variants.length} var</span>`
                    : '';

                card.innerHTML = `
                <!-- Top Image Area (Height 95px) -->
                <div class="relative w-full h-[95px] rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                    ${product.image_url
                        ? `<img src="${product.image_url}" alt="${product.name}" class="w-full h-full object-contain p-1 group-hover:scale-105 transition-transform duration-300" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                           <div class="w-full h-full items-center justify-center text-slate-300 hidden">
                               <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                           </div>`
                        : `<div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                               <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                           </div>`
                    }

                    <!-- Badges on Image -->
                    <span class="absolute top-1.5 right-1.5 bg-slate-900/80 backdrop-blur-xs text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded shadow-xs uppercase tracking-wider">Sale</span>
                    
                    <div class="absolute bottom-1.5 left-1.5 flex items-center gap-1">
                        ${unitBadge}
                        ${variantBadge}
                    </div>

                    <!-- Hover Add Overlay Icon -->
                    <div class="absolute inset-0 bg-indigo-600/15 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[0.5px]">
                        <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-lg transform scale-75 group-hover:scale-100 transition-transform">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                        </span>
                    </div>

                    <!-- Cart Quantity Badge -->
                    ${cartQty > 0 ? `<div class="absolute top-1.5 left-1.5 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-[11px] font-black shadow-md border-2 border-white">${isLengthProduct ? parseFloat(cartQty.toFixed(3)) : cartQty}</div>` : ''}
                </div>

                <!-- Product Name & Barcode -->
                <div class="pt-1.5 px-0.5 min-w-0 flex-1 flex flex-col justify-start">
                    <h4 class="product-name font-bold text-slate-800 text-xs sm:text-sm leading-snug line-clamp-2" dir="auto" title="${product.name}">${product.name}</h4>
                    <p class="text-[10px] font-mono text-slate-400 truncate mt-0.5">${product.barcode}</p>
                </div>
                
                <!-- Footer: Price & Stock -->
                <div class="pt-1.5 mt-auto border-t border-slate-100 flex items-end justify-between gap-1.5">
                    <div class="flex flex-col min-w-0">
                         <div class="font-extrabold text-indigo-600 text-sm sm:text-base tracking-tight leading-none flex items-baseline gap-0.5">
                            ${Number(product.price).toFixed(2)}
                            <span class="text-[10px] font-bold text-slate-400 self-start mt-0.5">${currencySymbol}</span>
                         </div>
                         <div class="text-[9px] font-medium text-slate-400 mt-1 truncate">
                             A: ${Number(product.purchase_price).toFixed(2)} ${currencySymbol}
                         </div>
                    </div>
                    
                    <div class="${stockColor} px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border text-center leading-tight shrink-0 whitespace-nowrap shadow-xs">
                        ${stockText}
                    </div>
                </div>
            `;

                card.addEventListener('click', () => {
                    if (hasMultipleVariants) {
                        openVariantPicker(product, 1);
                    } else {
                        addProductToCart(product, 1);
                    }
                });

                productsGrid.appendChild(card);
            });
        }

        function addProductToCart(product, quantityToAdd) {
            var currentQuantity = cart[product.barcode]?.quantity || 0;
            var nextQuantity = currentQuantity + quantityToAdd;

            if (nextQuantity > product.stock_quantity) {
                // Toast notification instead of alert could be better, but sticking to alert for simplicity unless upgrade desired
                alert('Stock insuffisant !');
                return;
            }

            cart[product.barcode] = {
                barcode: product.barcode,
                name: product.name,
                price: Number(product.price),
                purchase_price: Number(product.purchase_price),
                unit_price: Number(product.price), // Custom price (editable)
                quantity: nextQuantity,
                stock_quantity: product.stock_quantity,
                category_id: product.category_id,
                unit: product.unit || 'unité',
            };

            renderCart();
            renderProducts(); // To update badges
        }

        function closeVariantPicker() {
            variantPickerModal.classList.add('hidden');
            variantPickerList.innerHTML = '';
        }

        // A product's aggregate stock_quantity is the SUM across all its
        // variants — it is NOT what a single barcode scan/click can sell.
        // Selling against the wrong (ambiguous) variant is exactly the
        // "stock insuffisant" mismatch this picker exists to prevent: make
        // the cashier pick the specific size/color, then add/validate using
        // THAT variant's own barcode and stock, matching what checkout()
        // actually decrements server-side.
        function openVariantPicker(product, quantityToAdd) {
            quantityToAdd = quantityToAdd || 1;
            variantPickerTitle.textContent = product.name;
            variantPickerList.innerHTML = '';

            product.variants.forEach((variant) => {
                var label = variant.label || [
                    variant.size ? `Taille ${variant.size}` : null,
                    variant.color ? `Couleur ${variant.color}` : null,
                ].filter(Boolean).join(' · ') || 'Standard';

                var outOfStock = Number(variant.stock_quantity) <= 0;

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.disabled = outOfStock;
                btn.className = 'w-full flex items-center justify-between gap-3 px-4 py-3 rounded-2xl border-2 transition-all text-left ' +
                    (outOfStock
                        ? 'border-border/40 bg-muted/10 opacity-50 cursor-not-allowed'
                        : 'border-border/60 hover:border-indigo-400 hover:bg-indigo-50/50');
                btn.innerHTML = `
                    <span class="font-bold text-sm text-card-foreground">${label}</span>
                    <span class="text-xs font-bold ${outOfStock ? 'text-red-500' : 'text-emerald-600'} bg-card px-2 py-1 rounded-lg border border-border/40 shrink-0">
                        ${outOfStock ? 'Rupture' : 'Stock: ' + Number(variant.stock_quantity)}
                    </span>
                `;

                if (!outOfStock) {
                    btn.addEventListener('click', () => {
                        addProductToCart({
                            barcode: variant.barcode,
                            name: `${product.name} (${label})`,
                            price: product.price,
                            purchase_price: product.purchase_price,
                            stock_quantity: variant.stock_quantity,
                            category_id: product.category_id,
                            unit: product.unit,
                        }, quantityToAdd);
                        closeVariantPicker();
                        renderCategories();
                        renderProducts();
                    });
                }

                variantPickerList.appendChild(btn);
            });

            variantPickerModal.classList.remove('hidden');
        }

        document.getElementById('variant-picker-close').addEventListener('click', closeVariantPicker);
        variantPickerModal.addEventListener('click', (event) => {
            if (event.target === variantPickerModal) closeVariantPicker();
        });

        function changeQuantity(barcode, delta) {
            var item = cart[barcode];

            if (!item) {
                return;
            }

            var isLength = item.unit === 'metre' || item.unit === 'cm';
            var defaultDelta = isLength ? 0.1 : 1;
            var finalDelta = typeof delta === 'number' ? delta : defaultDelta;
            
            var nextQuantity = Math.max(0, item.quantity + finalDelta);

            if (nextQuantity <= 0) {
                delete cart[barcode];
            } else if (nextQuantity > item.stock_quantity) {
                alert('Stock limite atteint');
                return;
            } else {
                item.quantity = parseFloat(nextQuantity.toFixed(3));
            }

            renderCart();
            renderProducts();
        }

        function updateCartTotal() {
            var items = Object.values(cart);
            var total = 0;
            items.forEach((item) => {
                total += item.unit_price * item.quantity;
            });
            cartTotal.textContent = total.toFixed(2);
            calculateChange();
        }

        function calculateChange() {
            var total = parseFloat(cartTotal.textContent) || 0;
            var received = parseFloat(receivedAmountInput.value) || 0;
            var change = received - total;

            if (received > 0 && change >= 0) {
                changeAmountDisplay.textContent = change.toFixed(2);
                changeAmountDisplay.className = "text-lg font-black text-emerald-600 tracking-tight";
            } else if (received > 0 && change < 0) {
                changeAmountDisplay.textContent = "0.00";
                changeAmountDisplay.className = "text-lg font-black text-red-500 tracking-tight animate-pulse";
            } else {
                changeAmountDisplay.textContent = "0.00";
                changeAmountDisplay.className = "text-lg font-black text-emerald-600 tracking-tight";
            }
        }

        receivedAmountInput.addEventListener('input', calculateChange);

        function renderCart() {
            var items = Object.values(cart);
            cartBody.innerHTML = '';
            hiddenItems.innerHTML = '';

            if (items.length === 0) {
                emptyCartMsg.classList.remove('hidden');
                cartTotal.textContent = "0.00";
                return;
            }

            emptyCartMsg.classList.add('hidden');

            var total = 0;

            items.forEach((item, index) => {
                var row = document.createElement('tr');
                var lineTotal = item.unit_price * item.quantity;
                total += lineTotal;

                // Updated Cart Item Design
                row.className = 'flex items-center gap-3 p-3 bg-card border border-border/60 rounded-2xl mb-2 hover:border-primary/30 hover:shadow-md transition-all group';
                row.style.display = 'flex'; // Ensure flex behavior for the TR replacement

                row.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm text-card-foreground truncate">${item.name}</div>
                     <div class="flex items-center gap-2 mt-1">
                          <input type="number" class="price-input w-20 bg-gray-50 border border-gray-200 text-center font-bold text-indigo-600 focus:ring-2 focus:ring-indigo-500 rounded-lg text-[11px] py-1" data-barcode="${item.barcode}" value="${item.unit_price.toFixed(2)}" min="0" step="0.01">
                          <div class="text-[10px] text-muted-foreground/70 font-mono tracking-wider">${item.barcode}</div>
                          <div class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1 rounded">A: ${item.purchase_price.toFixed(2)}</div>
                          ${item.unit === 'metre' ? '<span class="text-[9px] bg-amber-50 text-amber-600 font-bold px-1 rounded border border-amber-100/50">📏 m</span>' : (item.unit === 'cm' ? '<span class="text-[9px] bg-cyan-50 text-cyan-600 font-bold px-1 rounded border border-cyan-100/50">📐 cm</span>' : '')}
                     </div>
                </div>
                
                <div class="flex flex-col items-end gap-1">
                    <div class="font-extrabold text-card-foreground text-base line-total">${lineTotal.toFixed(2)}</div>
                    
                    <div class="flex items-center gap-1 bg-muted/30 rounded-lg p-0.5">
                        <button type="button" class="w-6 h-6 flex items-center justify-center rounded-md bg-card text-muted-foreground shadow-sm hover:text-red-500 transition-colors qty-minus">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                        </button>
                        <input type="number" class="qty-direct-input text-[11px] font-bold w-14 text-center text-indigo-700 bg-indigo-50/50 border border-indigo-100 rounded-md outline-none focus:ring-1 focus:ring-indigo-400 py-0.5" 
                                  step="0.001" min="0.001" 
                                  max="${item.stock_quantity}" 
                                  value="${item.quantity}">
                        <button type="button" class="w-6 h-6 flex items-center justify-center rounded-md bg-card text-muted-foreground shadow-sm hover:text-green-600 transition-colors qty-plus">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
                
                <button type="button" class="ml-1 w-8 h-8 flex items-center justify-center rounded-xl bg-red-100 text-red-500 hover:bg-red-500 hover:text-white transition-all opacity-0 group-hover:opacity-100 qty-remove">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            `;

                // Handle price input changes
                var priceInput = row.querySelector('.price-input');
                priceInput.addEventListener('input', (event) => {
                    var newPrice = parseFloat(event.target.value);
                    if (!isNaN(newPrice) && newPrice >= 0) {
                        item.unit_price = newPrice;
                        var newLineTotal = item.unit_price * item.quantity;
                        row.querySelector('.line-total').textContent = newLineTotal.toFixed(2);
                        
                        // Update hidden field immediately
                        var hiddenPriceField = hiddenItems.querySelector(`input[name="items[${index}][price]"]`);
                        if (hiddenPriceField) {
                            hiddenPriceField.value = newPrice;
                        }
                        
                        updateCartTotal();
                    }
                });

                // Prevent scanner input when typing in price
                priceInput.addEventListener('focus', () => {
                    barcodeInput.blur();
                });

                row.querySelector('.qty-minus').addEventListener('click', () => {
                    var delta = (item.unit === 'metre' || item.unit === 'cm') ? -0.1 : -1;
                    changeQuantity(item.barcode, delta);
                });
                row.querySelector('.qty-plus').addEventListener('click', () => {
                    var delta = (item.unit === 'metre' || item.unit === 'cm') ? 0.1 : 1;
                    changeQuantity(item.barcode, delta);
                });

                // Handle direct quantity input
                var directInput = row.querySelector('.qty-direct-input');
                if (directInput) {
                    directInput.addEventListener('change', () => {
                        var newQty = parseFloat(parseFloat(directInput.value).toFixed(3));
                        
                        if (isNaN(newQty) || newQty <= 0) {
                            delete cart[item.barcode];
                        } else if (newQty > item.stock_quantity) {
                            alert(`Stock insuffisant! Max: ${item.stock_quantity}`);
                            directInput.value = item.quantity;
                            return;
                        } else {
                            item.quantity = newQty;
                        }
                        renderCart();
                        renderProducts();
                    });

                    // Prevent scanner focus
                    directInput.addEventListener('focus', () => {
                        barcodeInput.blur();
                    });
                    
                    // Allow Enter key to trigger change and blur
                    directInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            directInput.blur();
                        }
                    });

                    // AZERTY to QWERTY mapping for numbers
                    directInput.addEventListener('keydown', (event) => {
                        if (azertyMap[event.key]) {
                            event.preventDefault();
                            var start = directInput.selectionStart;
                            var end = directInput.selectionEnd;
                            var val = directInput.value;
                            directInput.value = val.slice(0, start) + azertyMap[event.key] + val.slice(end);
                            directInput.setSelectionRange(start + 1, start + 1);
                            directInput.dispatchEvent(new Event('change'));
                        }
                    });
                }
                row.querySelector('.qty-remove').addEventListener('click', () => {
                    delete cart[item.barcode];
                    renderCart();
                    renderProducts();
                });

                cartBody.appendChild(row);

                // Form Fields
                var barcodeField = document.createElement('input');
                barcodeField.type = 'hidden';
                barcodeField.name = `items[${index}][barcode]`;
                barcodeField.value = item.barcode;

                var quantityField = document.createElement('input');
                quantityField.type = 'hidden';
                quantityField.name = `items[${index}][quantity]`;
                quantityField.value = item.quantity;

                var priceField = document.createElement('input');
                priceField.type = 'hidden';
                priceField.name = `items[${index}][price]`;
                priceField.value = item.unit_price;

                hiddenItems.appendChild(barcodeField);
                hiddenItems.appendChild(quantityField);
                hiddenItems.appendChild(priceField);
            });

            cartTotal.textContent = total.toFixed(2);
        }

        async function scanAndAdd() {
            var barcode = barcodeInput.value.trim();
            var quantity = parseFloat(scanQuantityInput.value) || 1;

            if (!barcode) {
                return;
            }

            scanBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;

            try {
                var response = await fetch("{{ route('pos.scan') }}?barcode=" + encodeURIComponent(barcode));

                if (!response.ok) {
                    // Show error effect on input
                    barcodeInput.classList.add('ring-2', 'ring-red-500', 'bg-red-50');
                    setTimeout(() => barcodeInput.classList.remove('ring-2', 'ring-red-500', 'bg-red-50'), 500);
                    barcodeInput.value = '';
                    barcodeInput.focus();
                    return;
                }

                var product = await response.json();

                if (product.needs_variant) {
                    openVariantPicker(product.product, quantity);
                    barcodeInput.value = '';
                    scanQuantityInput.value = '1';
                    barcodeInput.focus();
                    return;
                }

                activeCategoryId = product.category_id;
                addProductToCart(product, quantity);

                barcodeInput.value = '';
                scanQuantityInput.value = '1';

                // Show success effect
                barcodeInput.classList.add('ring-2', 'ring-green-500', 'bg-green-50');
                setTimeout(() => barcodeInput.classList.remove('ring-2', 'ring-green-500', 'bg-green-50'), 500);

                renderCategories();
                renderProducts();
                barcodeInput.focus();
            } catch (e) {
                console.error(e);
            } finally {
                scanBtn.innerHTML = `
               <span class="hidden xl:inline">Ajouter</span>
               <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            `;
            }
        }



        barcodeInput.addEventListener('input', (event) => {
            var value = barcodeInput.value;
            var corrected = '';
            var changed = false;

            for (let char of value) {
                if (azertyMap[char]) {
                    corrected += azertyMap[char];
                    changed = true;
                } else {
                    corrected += char;
                }
            }

            if (changed) {
                barcodeInput.value = corrected;
            }
            
            renderProducts();
        });

        scanBtn.addEventListener('click', scanAndAdd);

        // Support AZERTY for scan quantity
        scanQuantityInput.addEventListener('keydown', (event) => {
            if (azertyMap[event.key]) {
                event.preventDefault();
                var start = scanQuantityInput.selectionStart;
                var end = scanQuantityInput.selectionEnd;
                var val = scanQuantityInput.value;
                scanQuantityInput.value = val.slice(0, start) + azertyMap[event.key] + val.slice(end);
                scanQuantityInput.setSelectionRange(start + 1, start + 1);
                // Trigger input event
                scanQuantityInput.dispatchEvent(new Event('input'));
            }
        });

        barcodeInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                scanAndAdd();
            }
        });

        document.getElementById('checkout-form').addEventListener('submit', (event) => {
            if (!Object.keys(cart).length) {
                event.preventDefault();
                alert('Le panier est vide !');
            }
        });

        // Initialisation
        renderCategories();
        renderProducts();
        renderCart();

        // Re-focus scanner on idle interactions
        document.addEventListener('click', (e) => {
            if (e.target.tagName === 'BODY' || e.target.classList.contains('products-grid')) {
                barcodeInput.focus();
            }
        });
})();
</script>
@endsection




