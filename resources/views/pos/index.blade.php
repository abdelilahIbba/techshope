<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --panel: #ffffff;
            --line: #d7e1ef;
            --ink: #1f2a44;
            --muted: #5b6b88;
            --primary: #3b82f6;
            --danger: #dc2626;
        }

        html,
        body {
            height: 100%;
        }

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

        .layout {
            height: calc(100vh - 58px);
            display: grid;
            grid-template-columns: 240px 1fr 380px;
            gap: 16px;
            padding: 16px;
            min-height: 0;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
            min-height: 0;
            overflow: hidden;
        }

        .title { margin: 0 0 12px; font-size: 18px; }
        .subtitle { color: var(--muted); margin: 0 0 10px; font-size: 13px; }

        .category-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow: auto;
        }

        .category-btn {
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 8px;
            padding: 9px;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
        }

        .category-btn.active {
            border-color: var(--primary);
            color: var(--primary);
            font-weight: 700;
        }

        .scanner {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .scanner input {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px;
            width: 100%;
        }

        .scanner button,
        .checkout-btn,
        .qty-btn,
        .add-btn {
            border: 0;
            border-radius: 8px;
            padding: 9px 12px;
            cursor: pointer;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            margin-top: 8px;
            overflow: auto;
            max-height: calc(100% - 90px);
        }

        .product-card {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .product-name { font-weight: 700; font-size: 14px; }
        .muted { color: var(--muted); font-size: 12px; }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .cart-table th,
        .cart-table td {
            border-bottom: 1px solid var(--line);
            padding: 8px 4px;
            text-align: left;
            vertical-align: middle;
            font-size: 13px;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .qty-btn {
            padding: 4px 8px;
            min-width: 28px;
        }

        .qty-btn.minus { background: #64748b; }
        .qty-btn.remove { background: var(--danger); }

        .total-box {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 700;
        }

        .error {
            color: var(--danger);
            margin-bottom: 8px;
            font-size: 13px;
        }

        .checkout-btn {
            width: 100%;
            margin-top: 14px;
            padding: 11px;
        }
    </style>
</head>
<body>
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

<nav class="top-nav">
    <a class="nav-link" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link active" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link" href="{{ route('pos.stock') }}">Gestion de stock</a>
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

<div class="layout">
    <aside class="panel">
        <h2 class="title">Categories</h2>
        <p class="subtitle">Select a category to view products</p>
        <div class="category-list" id="category-list"></div>
    </aside>

    <main class="panel">
        <h2 class="title">Products</h2>
        <p class="subtitle">Workflow: Select category → scan/add product → adjust quantity → checkout</p>

        <div class="scanner">
            <input id="barcode-input" type="text" placeholder="Scan or type barcode" autofocus>
            <input id="scan-quantity" type="number" min="1" value="1" style="max-width: 90px;">
            <button type="button" id="scan-btn">Scan</button>
        </div>

        <div id="active-category-name" class="subtitle"></div>
        <div id="products-grid" class="products-grid"></div>
    </main>

    <section class="panel">
        <h2 class="title">Cart</h2>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form">
            @csrf
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="cart-body"></tbody>
            </table>

            <div class="total-box">
                <span>Total</span>
                <span id="cart-total">0.00</span>
            </div>

            <div id="hidden-items"></div>
            <input type="hidden" name="payment_method" value="cash">

            <button type="submit" class="checkout-btn">Checkout & Print Receipt</button>
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
            button.className = 'category-btn' + (category.id === activeCategoryId ? ' active' : '');
            button.textContent = category.name;
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
            activeCategoryName.textContent = 'No category found';
            productsGrid.innerHTML = '';
            return;
        }

        activeCategoryName.textContent = 'Selected: ' + activeCategory.name;
        productsGrid.innerHTML = '';

        activeCategory.products.forEach((product) => {
            const card = document.createElement('div');
            const cartQty = cart[product.barcode]?.quantity || 0;
            card.className = 'product-card';
            card.innerHTML = `
                <div class="product-name">${product.name}</div>
                <div class="muted">Barcode: ${product.barcode}</div>
                <div class="muted">Price: ${Number(product.price).toFixed(2)}</div>
                <div class="muted">Stock: ${product.stock_quantity}</div>
                <div class="muted">In cart: ${cartQty}</div>
                <button type="button" class="add-btn">Add to cart</button>
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
            alert('Requested quantity exceeds available stock.');
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
            alert('Requested quantity exceeds available stock.');
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

            row.innerHTML = `
                <td>
                    <div>${item.name}</div>
                    <div class="muted">${item.barcode}</div>
                </td>
                <td>
                    <div class="qty-controls">
                        <button type="button" class="qty-btn minus">-</button>
                        <span>${item.quantity}</span>
                        <button type="button" class="qty-btn">+</button>
                        <button type="button" class="qty-btn remove">x</button>
                    </div>
                </td>
                <td>${item.price.toFixed(2)}</td>
                <td>${lineTotal.toFixed(2)}</td>
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
            alert('Product not found for this barcode.');
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
            alert('Cart is empty. Scan or add products first.');
        }
    });

    renderCategories();
    renderProducts();
    renderCart();
</script>
</body>
</html>