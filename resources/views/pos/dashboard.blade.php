<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* تم الاعتماد بالكامل على Tailwind، ولكن أبقينا هذا الجزء فارغاً لاحترام هيكل الكود الأصلي */
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden antialiased">

<nav class="top-nav flex items-center gap-2 p-4 bg-white border-b border-slate-200 shadow-sm shrink-0">
    <a class="nav-link active px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-blue-600 text-white shadow-md border-blue-600" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link notification-badge relative px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.notifications') }}">
        Notifications stock
        @if($notificationCount > 0)
            <span class="badge-count absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-sm">{{ $notificationCount }}</span>
        @endif
    </a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.settings') }}">Settings</a>
    
    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
        @csrf
        <button type="submit" class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 cursor-pointer flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Déconnexion
        </button>
    </form>
</nav>

<div class="container flex-1 p-6 lg:p-8 overflow-y-auto w-full max-w-7xl mx-auto">
    
    <div class="page-header mb-8">
        <h1 class="page-title text-3xl font-extrabold text-slate-900 tracking-tight">Chiffre d'affaires (CA)</h1>
        <p class="page-subtitle text-slate-500 mt-1 font-medium">Tableau de bord des ventes et statistiques</p>
    </div>

    <div class="filter-section bg-white border border-slate-200 rounded-2xl p-5 mb-8 shadow-sm">
        <form method="GET" action="{{ route('pos.dashboard') }}" class="filter-form flex flex-wrap gap-4 items-end">
            <div class="filter-group flex flex-col gap-1.5">
                <label class="filter-label text-sm font-semibold text-slate-700">Date début</label>
                <input type="date" name="from" class="filter-input bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full md:w-48 p-2.5 outline-none transition-all" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="filter-group flex flex-col gap-1.5">
                <label class="filter-label text-sm font-semibold text-slate-700">Date fin</label>
                <input type="date" name="to" class="filter-input bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full md:w-48 p-2.5 outline-none transition-all" value="{{ request('to', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="filter-btn bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm px-6 py-2.5 transition-all shadow-sm focus:ring-4 focus:ring-blue-300">
                Filtrer
            </button>
        </form>
    </div>

    <div class="stats-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">CA Total (période)</div>
            <div class="stat-value success text-3xl font-extrabold text-emerald-600">{{ number_format($totalRevenue, 2) }} <span class="text-xl text-emerald-500">DZD</span></div>
        </div>
        <div class="stat-card bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Ventes (période)</div>
            <div class="stat-value text-3xl font-extrabold text-slate-900">{{ $salesCount }}</div>
        </div>
        <div class="stat-card bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Produits vendus</div>
            <div class="stat-value text-3xl font-extrabold text-slate-900">{{ $productsCount }}</div>
        </div>
        <div class="stat-card bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">En stock</div>
            <div class="stat-value text-3xl font-extrabold text-red-500">{{ $lowStockCount }} <span class="text-xl text-red-400 font-medium">alertes</span></div>
        </div>
    </div>

    <div class="table-container bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        <h2 class="section-title text-lg font-bold text-slate-900 p-5 border-b border-slate-200 bg-slate-50/50">Transactions de la période</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-bold">ID Vente</th>
                        <th class="px-6 py-4 font-bold">Date</th>
                        <th class="px-6 py-4 font-bold">Client</th>
                        <th class="px-6 py-4 font-bold">Montant</th>
                        <th class="px-6 py-4 font-bold">Paiement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0">
                            <td class="px-6 py-4 font-medium text-slate-900">#{{ $sale->id }}</td>
                            <td class="px-6 py-4">{{ $sale->date->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">{{ $sale->customer->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ number_format($sale->total_amount, 2) }} DZD</td>
                            <td class="px-6 py-4">
                                <span class="badge badge-success bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                    {{ ucfirst($sale->payment_method) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Aucune vente pour cette période</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-container bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        <h2 class="section-title text-lg font-bold text-slate-900 p-5 border-b border-slate-200 bg-slate-50/50">Produits vendus (période)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-bold">Produit</th>
                        <th class="px-6 py-4 font-bold">Quantité totale</th>
                        <th class="px-6 py-4 font-bold">CA généré</th>
                        <th class="px-6 py-4 font-bold">Vendeur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productsSold as $item)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $item->product_name }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-md font-semibold text-xs">{{ $item->total_quantity }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-emerald-600">{{ number_format($item->total_revenue, 2) }} DZD</td>
                            <td class="px-6 py-4"><span class="muted text-slate-400">{{ $item->seller_name ?? 'Multiple' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">Aucun produit vendu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>