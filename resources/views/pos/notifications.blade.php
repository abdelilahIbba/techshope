<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications stock</title>
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

        .alert-summary {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-icon {
            width: 48px;
            height: 48px;
            background: #fee2e2;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .alert-desc {
            color: var(--muted);
            font-size: 13px;
        }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notification-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-left: 4px solid var(--danger);
            border-radius: 12px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .notification-card.warning {
            border-left-color: var(--warning);
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            background: #fee2e2;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .notification-icon.warning-icon {
            background: #fef3c7;
        }

        .notification-body {
            flex: 1;
        }

        .notification-title {
            font-weight: 700;
            font-size: 14px;
            margin: 0 0 4px;
        }

        .notification-meta {
            color: var(--muted);
            font-size: 12px;
        }

        .notification-stock {
            font-weight: 700;
            font-size: 18px;
            color: var(--danger);
        }

        .notification-stock.warning-stock {
            color: var(--warning);
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .empty-state {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 48px 24px;
            text-align: center;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
            color: var(--ink);
        }

        .empty-desc {
            color: var(--muted);
            font-size: 14px;
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <a class="nav-link" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link notification-badge active" href="{{ route('pos.notifications') }}">
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
        <h1 class="page-title">Notifications stock</h1>
        <p class="page-subtitle">Alertes pour produits à réapprovisionner</p>
    </div>

    @if($lowStockProducts->where('stock_quantity', '<=', 5)->count() > 0 || $lowStockProducts->where('stock_quantity', '>', 5)->count() > 0)
        <div class="alert-summary">
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
                <h3 class="alert-title">{{ $lowStockProducts->count() }} produit(s) nécessite(nt) attention</h3>
                <p class="alert-desc">
                    {{ $lowStockProducts->where('stock_quantity', '<=', 5)->count() }} critique(s), 
                    {{ $lowStockProducts->where('stock_quantity', '>', 5)->where('stock_quantity', '<=', 10)->count() }} en alerte
                </p>
            </div>
        </div>

        <div class="notification-list">
            @foreach($lowStockProducts as $product)
                <div class="notification-card {{ $product->stock_quantity > 5 ? 'warning' : '' }}">
                    <div class="notification-icon {{ $product->stock_quantity > 5 ? 'warning-icon' : '' }}">
                        📦
                    </div>
                    <div class="notification-body">
                        <h4 class="notification-title">{{ $product->name }}</h4>
                        <p class="notification-meta">
                            Catégorie: {{ $product->category->name ?? 'N/A' }} | 
                            Barcode: {{ $product->barcode }} |
                            <span class="badge {{ $product->stock_quantity <= 5 ? 'badge-danger' : 'badge-warning' }}">
                                {{ $product->stock_quantity <= 5 ? 'Critique' : 'Attention' }}
                            </span>
                        </p>
                    </div>
                    <div class="notification-stock {{ $product->stock_quantity > 5 ? 'warning-stock' : '' }}">
                        {{ $product->stock_quantity }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">✅</div>
            <h3 class="empty-title">Tout est en ordre</h3>
            <p class="empty-desc">Aucun produit ne nécessite de réapprovisionnement pour le moment</p>
        </div>
    @endif
</div>
</body>
</html>
