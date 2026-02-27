<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* إخفاء أسهم الأرقام في حقل الإدخال */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        
        /* تخصيص شريط التمرير (Scrollbar) ليكون أنيقاً */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden antialiased">

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

<nav class="top-nav flex items-center gap-2 p-4 bg-white border-b border-slate-200 shadow-sm shrink-0">
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link active px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-blue-600 text-white shadow-md border-blue-600" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link notification-badge relative px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.notifications') }}">
        Notifications stock
        @if($notificationCount > 0)
            <span class="badge-count absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-sm">{{ $notificationCount }}</span>
        @endif
    </a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.settings') }}">Settings</a>

    @include('partials.license-badge')

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 cursor-pointer flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Déconnexion
        </button>
    </form>
</nav>

@if(session('success'))
    <div id="flash-success" class="mx-4 mt-4 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3 rounded-xl font-semibold text-sm flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
        {{ session('success') }}
    </div>
    <script>setTimeout(()=>{const el=document.getElementById('flash-success');if(el)el.style.display='none';},4000);</script>
@endif

@if(session('error'))
    <div class="mx-4 mt-4 bg-red-50 text-red-700 border border-red-200 p-3 rounded-xl font-semibold text-sm flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
        {{ session('error') }}
    </div>
@endif

<div class="layout flex-1 p-4 grid grid-cols-1 lg:grid-cols-[260px_1fr_380px] gap-5 h-[calc(100vh-73px)] min-h-0">
    
    <aside class="panel bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col min-h-0">
        <h2 class="title text-lg font-extrabold text-slate-900 mb-1">Catégories</h2>
        <p class="subtitle text-xs text-slate-500 mb-4 font-medium">Sélectionnez pour filtrer</p>
        <div class="category-list flex flex-col gap-2 overflow-y-auto pr-1" id="category-list">
            </div>
    </aside>

    <main class="panel bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col min-h-0">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="title text-lg font-extrabold text-slate-900 mb-1">Produits</h2>
                <div id="active-category-name" class="subtitle text-sm text-blue-600 font-semibold bg-blue-50 px-3 py-1 rounded-full inline-block"></div>
            </div>
        </div>

        <div class="scanner flex gap-3 mb-6 bg-slate-50 p-3 rounded-xl border border-slate-100">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                </div>
                <input id="barcode-input" type="text" class="pl-10 w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none transition-all font-mono" placeholder="Scannez le code-barres..." autofocus>
            </div>
            <div class="flex items-center bg-white border border-slate-300 rounded-lg overflow-hidden w-24 focus-within:ring-2 focus-within:ring-blue-500 transition-all">
                <div class="px-2 text-slate-400 text-xs font-bold border-r border-slate-200 bg-slate-50 h-full flex items-center">Qté</div>
                <input id="scan-quantity" type="number" min="1" value="1" class="w-full text-center p-2.5 outline-none font-bold text-slate-800">
            </div>
            <button type="button" id="scan-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm px-6 py-2.5 transition-all shadow-sm focus:ring-4 focus:ring-blue-300 flex items-center gap-2">
                Scanner
            </button>
        </div>

        <div id="products-grid" class="products-grid grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto pr-1 pb-2">
            </div>
    </main>

    <section class="panel bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col min-h-0">
        <div class="p-5 border-b border-slate-100 flex-shrink-0">
            <h2 class="title text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Panier Actuel
            </h2>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-600 border border-red-200 text-sm font-semibold rounded-lg p-3 m-4 mb-0 flex items-center gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            
            <div class="flex-1 overflow-y-auto p-2">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-white z-10 shadow-[0_4px_6px_-6px_rgba(0,0,0,0.1)]">
                        <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                            <th class="pb-2 px-2">Article</th>
                            <th class="pb-2 px-1 text-center">Qté</th>
                            <th class="pb-2 px-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        </tbody>
                </table>
            </div>

            <div class="p-5 bg-slate-50 border-t border-slate-200 flex-shrink-0 mt-auto rounded-b-2xl">
                <div class="flex justify-between items-end mb-4">
                    <span class="text-slate-500 font-bold uppercase tracking-wide text-sm">Total à payer</span>
                    <div class="text-right">
                        <span id="cart-total" class="text-4xl font-extrabold text-emerald-600 tracking-tight">0.00</span>
                        <span class="text-emerald-500 font-bold ml-1">DZD</span>
                    </div>
                </div>

                <div id="hidden-items"></div>
                <input type="hidden" name="payment_method" value="cash">

                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-lg py-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 group transform active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Valider & Imprimer
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    const categories = @json($preparedCategories);
    const categoryList = document.getElementById('category-list');
    const productsGrid = document.getElementById('products-grid');
    const activeCategoryName = document.getElementById('active-category-name');
    const cartBody = document.getElementById('cart-body');
    const cartTotal = document.getElementById('cart-total');
    const hiddenItems = document.getElementById('hidden-items');
    const barcodeInput = document.getElementById('barcode-input');
    const scanQuantityInput = document.getElementById('scan-quantity');
    const scanBtn = document.getElementById('scan-btn');

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
            // دمج تصميم Tailwind هنا مباشرة مع كلاساتك الأصلية
            let baseClasses = 'category-btn w-full text-left px-4 py-3.5 rounded-xl text-sm font-semibold transition-all duration-200 border ';
            let activeClasses = category.id === activeCategoryId 
                ? 'active bg-blue-50 border-blue-500 text-blue-700 shadow-sm relative overflow-hidden' 
                : 'bg-white border-slate-200 text-slate-600 hover:border-blue-300 hover:bg-slate-50 hover:text-slate-900';
            
            button.className = baseClasses + activeClasses;
            
            // إضافة خط جانبي صغير عند التفعيل للمسة إضافية
            if(category.id === activeCategoryId) {
                button.innerHTML = `<div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-l-xl"></div> ${category.name}`;
            } else {
                button.textContent = category.name;
            }

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
            activeCategoryName.textContent = 'Aucune catégorie';
            productsGrid.innerHTML = '';
            return;
        }

        activeCategoryName.textContent = activeCategory.name;
        productsGrid.innerHTML = '';

        activeCategory.products.forEach((product) => {
            const card = document.createElement('div');
            const cartQty = cart[product.barcode]?.quantity || 0;
            
            // دمج تصميم Tailwind للـ Product Card
            card.className = 'product-card bg-white border border-slate-200 rounded-2xl p-4 flex flex-col gap-1.5 hover:shadow-[0_4px_12px_rgba(0,0,0,0.05)] hover:border-blue-300 transition-all duration-300 group relative overflow-hidden';
            
            let stockBadge = product.stock_quantity > 5 
                ? `<span class="bg-emerald-50 text-emerald-600 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">En Stock: ${product.stock_quantity}</span>`
                : `<span class="bg-red-50 text-red-600 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider animate-pulse">Faible: ${product.stock_quantity}</span>`;

            card.innerHTML = `
                <div class="flex justify-between items-start mb-1">
                    <div class="product-name font-bold text-slate-800 text-[15px] leading-tight line-clamp-2 pr-2">${product.name}</div>
                </div>
                <div class="muted text-[11px] text-slate-400 font-mono tracking-widest">${product.barcode}</div>
                
                <div class="mt-2 mb-1 flex items-end gap-1">
                    <div class="font-extrabold text-blue-600 text-xl leading-none">${Number(product.price).toFixed(2)}</div>
                    <div class="text-xs font-bold text-blue-400 mb-0.5">DZD</div>
                </div>
                
                <div class="flex justify-between items-center mb-3">
                    ${stockBadge}
                    <div class="text-[11px] font-medium text-slate-400">Panier: <span class="text-slate-700 font-bold">${cartQty}</span></div>
                </div>

                <button type="button" class="add-btn mt-auto w-full bg-slate-100 hover:bg-blue-600 text-slate-700 hover:text-white font-bold py-2.5 rounded-xl transition-colors text-sm flex items-center justify-center gap-2 group-hover:bg-blue-50 group-hover:text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                    Ajouter
                </button>
            `;

            card.querySelector('.add-btn').addEventListener('click', () => {
                addProductToCart(product, 1);
            });

            productsGrid.appendChild(card);
        });
    }

    function addProductToCart(product, quantityToAdd) {
        const currentQuantity = cart[product.barcode]?.quantity || 0;
        const nextQuantity = currentQuantity + quantityToAdd;

        if (nextQuantity > product.stock_quantity) {
            alert('Quantité demandée dépasse le stock disponible.');
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
        renderProducts();
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
            alert('Quantité demandée dépasse le stock disponible.');
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

        let total = 0;

        items.forEach((item, index) => {
            const row = document.createElement('tr');
            const lineTotal = item.price * item.quantity;
            total += lineTotal;

            // دمج تصميم Tailwind في صفوف السلة
            row.className = 'border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors group';
            row.innerHTML = `
                <td class="py-3 px-2 align-top">
                    <div class="font-bold text-sm text-slate-800 line-clamp-2 leading-snug">${item.name}</div>
                    <div class="text-[10px] text-slate-400 font-mono mt-1 tracking-wider">${item.barcode}</div>
                </td>
                <td class="py-3 px-1 align-top">
                    <div class="qty-controls flex items-center justify-center gap-1 bg-slate-100/80 border border-slate-200/60 rounded-lg p-1 w-max mx-auto">
                        <button type="button" class="qty-btn minus w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-600 shadow-sm transition-all text-base font-medium leading-none">-</button>
                        <span class="w-6 text-center text-[13px] font-extrabold text-slate-800">${item.quantity}</span>
                        <button type="button" class="qty-btn w-6 h-6 flex items-center justify-center rounded bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-600 shadow-sm transition-all text-base font-medium leading-none">+</button>
                    </div>
                </td>
                <td class="py-3 px-2 text-right align-top relative">
                    <div class="font-extrabold text-slate-700">${lineTotal.toFixed(2)}</div>
                    <div class="text-[10px] text-slate-400 font-medium">${item.price.toFixed(2)} / unité</div>
                    
                    <button type="button" class="qty-btn remove absolute right-2 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 w-7 h-7 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 text-red-500 hover:text-white transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </td>
            `;

            const [minusBtn, plusBtn, removeBtn] = row.querySelectorAll('button');
            minusBtn.addEventListener('click', () => changeQuantity(item.barcode, -1));
            plusBtn.addEventListener('click', () => changeQuantity(item.barcode, 1));
            removeBtn.addEventListener('click', () => {
                delete cart[item.barcode];
                renderCart();
                renderProducts();
            });

            cartBody.appendChild(row);

            // الحفاظ على الحقول المخفية للنموذج (Form)
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

        const response = await fetch(`/pos/scan/${barcode}`);

        if (!response.ok) {
            alert('Produit introuvable pour ce code-barres.');
            return;
        }

        const product = await response.json();
        activeCategoryId = product.category_id;
        addProductToCart(product, quantity);

        barcodeInput.value = '';
        scanQuantityInput.value = '1';

        renderCategories();
        renderProducts();
        barcodeInput.focus();
    }

    scanBtn.addEventListener('click', scanAndAdd);
    barcodeInput.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            scanAndAdd();
        }
    });

    document.getElementById('checkout-form').addEventListener('submit', (event) => {
        if (!Object.keys(cart).length) {
            event.preventDefault();
            alert('Le panier est vide. Scannez ou ajoutez des produits d\'abord.');
        }
    });

    // Initialisation
    renderCategories();
    renderProducts();
    renderCart();
</script>
</body>
</html>