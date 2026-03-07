<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Montserrat:wght@900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Hide number input arrows */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        
        /* Modern Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Smooth transitions */
        .transition-smooth { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Glass effect */
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-100 text-slate-800 h-screen flex flex-col overflow-hidden antialiased">

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
                    'stock_quantity' => (int) $product->stock_quantity,
                ];
            })->values(),
        ];
    })->values();
@endphp

<!-- Top Navigation Bar -->
<nav class="h-16 bg-card border-b border-border flex items-center justify-between px-6 shrink-0 z-20 shadow-sm relative">
    <div class="flex items-center gap-8">
        <a href="{{ route('pos.dashboard') }}" style="display: flex; align-items: center; gap: 0px; text-decoration: none;">
            <img src="{{ asset('images/logo.png') }}" onerror="this.src='{{ asset('logo.svg') }}'" alt="TickDroguerie" style="width: 150px; height: auto; margin-right: -10px;">
            <div style="font-family: 'Montserrat', sans-serif; font-size: 22px; font-weight: 900; line-height: 1;">
                <span style="color: #F27036;">Tick</span><span style="color: #1A476F;">Droguerie</span>
            </div>
        </a>

        <div class="flex items-center bg-muted/30 p-1 rounded-xl">
            <a href="{{ route('pos.dashboard') }}" class="px-5 py-2 rounded-lg text-sm font-semibold text-muted-foreground hover:text-foreground transition-smooth">Tableau de bord</a>
            <a href="{{ route('pos.index') }}" class="px-5 py-2 rounded-lg text-sm font-bold text-indigo-600 bg-card shadow-sm ring-1 ring-black/5 transition-smooth">POS</a>
            <a href="{{ route('pos.stock') }}" class="px-5 py-2 rounded-lg text-sm font-semibold text-muted-foreground hover:text-foreground transition-smooth">Gestion de stock</a>
            <a href="{{ route('pos.settings') }}" class="px-5 py-2 rounded-lg text-sm font-semibold text-muted-foreground hover:text-foreground transition-smooth">Paramètres</a>
        </div>
    </div>


    <div class="flex items-center gap-4">
        <a href="{{ route('pos.notifications') }}" class="relative w-10 h-10 flex items-center justify-center hover:bg-muted/20 rounded-xl transition-smooth text-muted-foreground hover:text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            @if($notificationCount > 0)
                <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full ring-2 ring-white"></span>
            @endif
        </a>

        @include('partials.license-badge')

        <div class="h-8 w-px bg-gray-200 mx-2"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-lg transition-smooth">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span>Sortir</span>
            </button>
        </form>
    </div>
</nav>

@if(session('success'))
    <div id="flash-success" class="absolute top-20 left-1/2 -translate-x-1/2 z-50 bg-emerald-500 text-white px-6 py-3 rounded-2xl shadow-xl shadow-emerald-200/50 flex items-center gap-3 font-semibold animate-bounce">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
        {{ session('success') }}
    </div>
    <script>setTimeout(()=>{const el=document.getElementById('flash-success');if(el)el.style.display='none';},4000);</script>
@endif

<div class="flex-1 p-4 grid grid-cols-1 lg:grid-cols-[240px_1fr_400px] gap-4 h-[calc(100vh-64px)] min-h-0 container mx-auto max-w-[1920px]">
    
    <!-- Left Sidebar: Categories -->
    <aside class="glass-panel rounded-3xl p-1 shadow-sm border border-white/50 flex flex-col overflow-hidden">
        <div class="px-5 py-4 shrink-0">
            <h2 class="text-xs font-bold text-muted-foreground/70 uppercase tracking-widest mb-1">Navigation</h2>
            <p class="text-xl font-extrabold text-card-foreground">Catégories</p>
        </div>
        <div class="category-list flex-1 overflow-y-auto px-2 pb-2 space-y-1" id="category-list">
            <!-- Categories injected by JS -->
        </div>
    </aside>

    <!-- Main Content: Products & Search -->
    <main class="flex flex-col gap-4 min-h-0">
        <!-- Search & Scanner Bar -->
        <div class="glass-panel rounded-3xl p-3 shadow-sm border border-white/50 shrink-0">
            <div class="flex gap-3">
                <div class="relative flex-1 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground/70 group-focus-within:text-indigo-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                    </div>
                    <input id="barcode-input" type="text" class="pl-11 w-full h-12 bg-muted/20/50 border border-border text-foreground text-sm font-semibold rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-card block transition-all placeholder:text-muted-foreground/70" placeholder="Scanner ou rechercher..." autofocus>
                </div>
                
                <div class="flex items-center relative w-28 group">
                    <div class="absolute left-3 text-xs font-bold text-muted-foreground/70 group-focus-within:text-indigo-600 pointer-events-none z-10 transition-colors">QTÉ</div>
                    <input id="scan-quantity" type="number" min="1" value="1" class="w-full h-12 pl-10 pr-2 bg-muted/20/50 border border-border rounded-2xl text-center font-bold text-card-foreground focus:ring-2 focus:ring-indigo-500 focus:bg-card focus:border-transparent outline-none transition-all">
                </div>
                
                <button type="button" id="scan-btn" class="h-12 px-6 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2">
                    <span class="hidden xl:inline">Ajouter</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                </button>
            </div>
            <div class="flex justify-between items-center mt-3 px-2">
                 <div id="active-category-name" class="text-sm font-bold text-muted-foreground flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    Sélectionnez une catégorie
                 </div>
                 <div class="text-xs font-medium text-muted-foreground/70 bg-muted/30 px-3 py-1 rounded-full">Astuce: Appuyez sur Entrée pour ajouter</div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="glass-panel rounded-3xl p-4 shadow-sm border border-white/50 flex-1 overflow-hidden flex flex-col">
            <div id="products-grid" class="products-grid grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-4 overflow-y-auto pr-1 pb-2 content-start h-full">
                <!-- Products injected by JS -->
            </div>
        </div>
    </main>

    <!-- Right Panel: Cart -->
    <section class="glass-panel rounded-3xl shadow-lg border border-white/50 flex flex-col min-h-0 relative overflow-hidden ring-1 ring-black/5">
        <div class="p-6 pb-4 border-b border-border/60 bg-card/50 backdrop-blur-md z-10">
            <h2 class="text-xl font-extrabold text-foreground flex items-center gap-3">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                </div>
                Panier
            </h2>
        </div>

        @if ($errors->any())
            <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mt-0.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                <span class="text-sm font-semibold text-red-600 leading-snug">{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form" class="flex flex-col flex-1 min-h-0 overflow-hidden relative">
            @csrf
            
            <div class="flex-1 overflow-y-auto p-2 scroll-smooth">
                <table class="w-full text-left border-collapse">
                    <tbody id="cart-body" class="space-y-2 block">
                        <!-- Cart items injected by JS -->
                    </tbody>
                </table>
                <div id="empty-cart-msg" class="hidden flex-col items-center justify-center h-full text-center p-8 opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <p class="font-bold text-muted-foreground/70">Le panier est vide</p>
                    <p class="text-sm text-muted-foreground/70 mt-1">Scanner un produit pour commencer</p>
                </div>
            </div>

            <!-- Footer / Total -->
            <div class="p-6 bg-card border-t border-border/60 z-10 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-widest text-muted-foreground/70 mb-1">Total à payer</span>
                        <div class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded inline-block">TVA Incluse</div>
                    </div>
                    <div class="text-right">
                        <div class="flex items-baseline gap-1">
                            <span id="cart-total" class="text-4xl font-black text-card-foreground tracking-tight leading-none">0.00</span>
                            <span class="text-xl font-bold text-muted-foreground/70">{{ $currencySymbol }}</span>
                        </div>
                    </div>
                </div>

                <div id="hidden-items"></div>
                <input type="hidden" name="payment_method" value="cash">

                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-lg py-4 rounded-2xl shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-3 group active:scale-[0.98]">
                    <span>Valider & Imprimer</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    const categories = @json($preparedCategories);
    const currencySymbol = @json($currencySymbol);
    const categoryList = document.getElementById('category-list');
    const productsGrid = document.getElementById('products-grid');
    const activeCategoryName = document.getElementById('active-category-name');
    const cartBody = document.getElementById('cart-body');
    const cartTotal = document.getElementById('cart-total');
    const hiddenItems = document.getElementById('hidden-items');
    const barcodeInput = document.getElementById('barcode-input');
    const scanQuantityInput = document.getElementById('scan-quantity');
    const scanBtn = document.getElementById('scan-btn');
    const emptyCartMsg = document.getElementById('empty-cart-msg');

    let activeCategoryId = categories.length ? categories[0].id : null;
    const cart = {};

    function getCategoryById(categoryId) {
        return categories.find((category) => category.id === categoryId);
    }

    function renderCategories() {
        categoryList.innerHTML = '';

        categories.forEach((category) => {
            const button = document.createElement('button');
            button.type = 'button';
            
            const isActive = category.id === activeCategoryId;
            
            let baseClasses = 'w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 border flex items-center justify-between group ';
            let activeClasses = isActive 
                ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white border-transparent shadow-lg shadow-indigo-200 scale-[1.02]' 
                : 'bg-card border-border/60 text-muted-foreground hover:border-border hover:bg-muted/20 hover:text-foreground shadow-sm';
            
            button.className = baseClasses + activeClasses;
            
            const countBadge = isActive
                ? `<span class="bg-white/20 text-white px-2 py-0.5 rounded text-[10px] font-bold">${category.products.length}</span>`
                : `<span class="bg-gray-100 text-slate-500 px-2 py-0.5 rounded text-[10px] font-bold group-hover:bg-gray-200">${category.products.length}</span>`;

            button.innerHTML = `<span>${category.name}</span>${countBadge}`;

            button.addEventListener('click', () => {
                activeCategoryId = category.id;
                renderCategories();
                renderProducts();
            });
            categoryList.appendChild(button);
        });
    }

    function renderProducts() {
        const activeCategory = getCategoryById(activeCategoryId);

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

        activeCategoryName.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400"></span> ${activeCategory.name} <span class="bg-muted/30 text-muted-foreground px-2 rounded-md ml-2 text-xs font-bold">${activeCategory.products.length}</span>`;
        productsGrid.innerHTML = '';

        if(activeCategory.products.length === 0) {
            productsGrid.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center py-20 opacity-40">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    <span class="font-bold text-lg">Catégorie vide</span>
                </div>
            `;
            return;
        }

        activeCategory.products.forEach((product) => {
            const card = document.createElement('div');
            const cartQty = cart[product.barcode]?.quantity || 0;
            
            // Modern Product Card
            card.className = 'group bg-card border border-border/60 rounded-3xl p-4 flex flex-col gap-2 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:border-indigo-100 transition-all duration-300 relative overflow-hidden cursor-pointer active:scale-95 select-none h-44';
            
            let stockColor = product.stock_quantity > 5 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700 animate-pulse';
            let stockText = product.stock_quantity > 5 ? `En stock: ${product.stock_quantity}` : `Stock Faible: ${product.stock_quantity}`;

            card.innerHTML = `
                <!-- Hover Add Icon (Top Left for LTR, Top Right for RTL visual balance) -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 transform translate-y-1 group-hover:translate-y-0">
                    <div class="bg-indigo-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-lg shadow-indigo-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                    </div>
                </div>

                <!-- Product Content -->
                <div class="flex-1 flex flex-col relative z-0">
                    <!-- Title with auto-RTL detection support via CSS dir=auto typically, but here we enforce right align if arabic? No, let's keep it standard but give breathing room -->
                    <div class="product-name font-bold text-card-foreground text-[16px] leading-[1.3] line-clamp-2 h-[2.6em] mb-1" dir="auto">${product.name}</div>
                    
                    <div class="flex items-center gap-2 mt-auto">
                        <div class="text-[10px] font-mono text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100 truncate max-w-[120px]">${product.barcode}</div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="mt-3 pt-3 border-t border-border/40 flex items-center justify-between gap-2">
                    <div class="flex flex-col">
                         <div class="font-black text-indigo-600 text-xl tracking-tight leading-none flex items-baseline gap-0.5">
                            ${Number(product.price).toFixed(2)}
                            <span class="text-[10px] font-bold text-slate-400 self-start mt-1">${currencySymbol}</span>
                         </div>
                    </div>
                    
                    <div class="${stockColor} px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider shadow-sm border border-transparent">
                        ${stockText}
                    </div>
                </div>

                <!-- Cart Quantity Badge (Top Left to avoid overlapping add button or price) -->
                ${cartQty > 0 ? `<div class="absolute top-3 left-3 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg shadow-indigo-200 z-20 animate-in zoom-in duration-200 border-2 border-white">${cartQty}</div>` : ''}
            `;

            card.addEventListener('click', () => {
                addProductToCart(product, 1);
            });

            productsGrid.appendChild(card);
        });
    }

    function addProductToCart(product, quantityToAdd) {
        const currentQuantity = cart[product.barcode]?.quantity || 0;
        const nextQuantity = currentQuantity + quantityToAdd;

        if (nextQuantity > product.stock_quantity) {
            // Toast notification instead of alert could be better, but sticking to alert for simplicity unless upgrade desired
            alert('Stock insuffisant !');
            return;
        }

        cart[product.barcode] = {
            barcode: product.barcode,
            name: product.name,
            price: Number(product.price),
            quantity: nextQuantity,
            stock_quantity: product.stock_quantity,
            category_id: product.category_id,
        };

        renderCart();
        renderProducts(); // To update badges
    }

    function changeQuantity(barcode, delta) {
        const item = cart[barcode];

        if (!item) {
            return;
        }

        const nextQuantity = item.quantity + delta;

        if (nextQuantity <= 0) {
            delete cart[barcode];
        } else if (nextQuantity > item.stock_quantity) {
            alert('Stock limite atteint');
            return;
        } else {
            item.quantity = nextQuantity;
        }

        renderCart();
        renderProducts();
    }

    function renderCart() {
        const items = Object.values(cart);
        cartBody.innerHTML = '';
        hiddenItems.innerHTML = '';

        if(items.length === 0) {
            emptyCartMsg.classList.remove('hidden');
            cartTotal.textContent = "0.00";
            return;
        }
        
        emptyCartMsg.classList.add('hidden');

        let total = 0;

        items.forEach((item, index) => {
            const row = document.createElement('tr');
            const lineTotal = item.price * item.quantity;
            total += lineTotal;

            // Updated Cart Item Design
            row.className = 'flex items-center gap-3 p-3 bg-card border border-border/60 rounded-2xl mb-2 hover:border-primary/30 hover:shadow-md transition-all group';
            row.style.display = 'flex'; // Ensure flex behavior for the TR replacement

            row.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm text-card-foreground truncate">${item.name}</div>
                    <div class="flex items-center gap-2 mt-1">
                         <div class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">${item.price.toFixed(2)}</div>
                         <div class="text-[10px] text-muted-foreground/70 font-mono tracking-wider">${item.barcode}</div>
                    </div>
                </div>
                
                <div class="flex flex-col items-end gap-1">
                    <div class="font-extrabold text-card-foreground text-base">${lineTotal.toFixed(2)}</div>
                    
                    <div class="flex items-center gap-1 bg-muted/30 rounded-lg p-0.5">
                        <button type="button" class="w-6 h-6 flex items-center justify-center rounded-md bg-card text-muted-foreground shadow-sm hover:text-red-500 transition-colors qty-minus">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                        </button>
                        <span class="text-xs font-bold w-6 text-center text-slate-700">${item.quantity}</span>
                        <button type="button" class="w-6 h-6 flex items-center justify-center rounded-md bg-card text-muted-foreground shadow-sm hover:text-green-600 transition-colors qty-plus">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
                
                <button type="button" class="ml-1 w-8 h-8 flex items-center justify-center rounded-xl bg-red-100 text-red-500 hover:bg-red-500 hover:text-white transition-all opacity-0 group-hover:opacity-100 qty-remove">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            `;

            row.querySelector('.qty-minus').addEventListener('click', () => changeQuantity(item.barcode, -1));
            row.querySelector('.qty-plus').addEventListener('click', () => changeQuantity(item.barcode, 1));
            row.querySelector('.qty-remove').addEventListener('click', () => {
                delete cart[item.barcode];
                renderCart();
                renderProducts();
            });

            cartBody.appendChild(row);

            // Form Fields
            const barcodeField = document.createElement('input');
            barcodeField.type = 'hidden';
            barcodeField.name = `items[${index}][barcode]`;
            barcodeField.value = item.barcode;

            const quantityField = document.createElement('input');
            quantityField.type = 'hidden';
            quantityField.name = `items[${index}][quantity]`;
            quantityField.value = item.quantity;

            hiddenItems.appendChild(barcodeField);
            hiddenItems.appendChild(quantityField);
        });

        cartTotal.textContent = total.toFixed(2);
    }

    async function scanAndAdd() {
        const barcode = barcodeInput.value.trim();
        const quantity = Math.max(1, parseInt(scanQuantityInput.value, 10) || 1);

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
            const response = await fetch("{{ route('pos.scan', ['barcode' => 'BARCODE_PLACEHOLDER']) }}".replace('BARCODE_PLACEHOLDER', barcode));

            if (!response.ok) {
                // Show error effect on input
                barcodeInput.classList.add('ring-2', 'ring-red-500', 'bg-red-50');
                setTimeout(() => barcodeInput.classList.remove('ring-2', 'ring-red-500', 'bg-red-50'), 500);
                barcodeInput.value = '';
                barcodeInput.focus();
                return;
            }

            const product = await response.json();
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

    // AZERTY to QWERTY mapping for numbers (Dual support: FR/EN)
    // English keyboards send digits directly (1, 2, 3...) which are not in this map,
    // so they remain unchanged. Only French layout symbols are corrected.
    const azertyMap = {
        '&': '1', 'é': '2', '"': '3', "'": '4', '(': '5',
        '-': '6', 'è': '7', '_': '8', 'ç': '9', 'à': '0',
        ')': '°', '=': '+', '§': '!' 
    };

    barcodeInput.addEventListener('input', (event) => {
        let value = barcodeInput.value;
        let corrected = '';
        let changed = false;

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
    });

    scanBtn.addEventListener('click', scanAndAdd);
    
    // Support AZERTY for scan quantity
    scanQuantityInput.addEventListener('keydown', (event) => {
        if (azertyMap[event.key]) {
            event.preventDefault();
            const start = scanQuantityInput.selectionStart;
            const end = scanQuantityInput.selectionEnd;
            const val = scanQuantityInput.value;
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
        if(e.target.tagName === 'BODY' || e.target.classList.contains('products-grid')) {
            barcodeInput.focus();
        }
    });
</script>
</body>
</html>