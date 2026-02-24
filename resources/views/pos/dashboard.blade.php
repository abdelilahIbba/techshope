<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CA</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --panel: #ffffff;
            --line: #d7e1ef;
            --ink: #1f2a44;
            --muted: #5b6b88;
            --primary: #3b82f6;
            --danger: #dc2626;
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

        .filter-section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
        }

        .filter-input {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .filter-btn {
            border: 0;
            border-radius: 8px;
            padding: 9px 18px;
            cursor: pointer;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--ink);
        }

        .stat-value.success {
            color: var(--success);
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 12px;
        }

        .table-container {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
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

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .muted {
            color: var(--muted);
            font-size: 12px;
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <a class="nav-link active" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link" href="{{ route('pos.index') }}">POS</a>
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

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Chiffre d'affaires (CA)</h1>
        <p class="page-subtitle">Tableau de bord des ventes et statistiques</p>
    </div>

    <div class="filter-section">
        <form method="GET" action="{{ route('pos.dashboard') }}" class="filter-form">
            <div class="filter-group">
                <label class="filter-label">Date début</label>
                <input type="date" name="from" class="filter-input" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">Date fin</label>
                <input type="date" name="to" class="filter-input" value="{{ request('to', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="filter-btn">Filtrer</button>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">CA Total (période)</div>
            <div class="stat-value success">{{ number_format($totalRevenue, 2) }} DZD</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Ventes (période)</div>
            <div class="stat-value">{{ $salesCount }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Produits vendus</div>
            <div class="stat-value">{{ $productsCount }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">En stock</div>
            <div class="stat-value">{{ $lowStockCount }} alertes</div>
        </div>
    </div>

    <div class="table-container">
        <h2 class="section-title">Transactions de la période</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Vente</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Montant</th>
                    <th>Paiement</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                        <td>{{ number_format($sale->total_amount, 2) }} DZD</td>
                        <td><span class="badge badge-success">{{ ucfirst($sale->payment_method) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--muted);">Aucune vente pour cette période</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h2 class="section-title">Produits vendus (période)</h2>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité totale</th>
                    <th>CA généré</th>
                    <th>Vendeur</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productsSold as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->total_quantity }}</td>
                        <td>{{ number_format($item->total_revenue, 2) }} DZD</td>
                        <td><span class="muted">{{ $item->seller_name ?? 'Multiple' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--muted);">Aucun produit vendu</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
