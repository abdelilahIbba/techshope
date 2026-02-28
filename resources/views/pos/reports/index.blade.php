<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rapports - POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden antialiased">

<nav class="top-nav flex items-center gap-2 p-4 bg-white border-b border-slate-200 shadow-sm shrink-0">
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link active px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-blue-600 text-white shadow-md border-blue-600" href="{{ route('pos.reports') }}">Rapports</a>
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

<div class="container flex-1 p-6 lg:p-8 overflow-y-auto w-full max-w-7xl mx-auto">
    
    <div class="page-header mb-8">
        <h1 class="page-title text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Rapports & Historique
        </h1>
        <p class="page-subtitle text-slate-500 mt-1 font-medium">Analyses et statistiques détaillées</p>
    </div>

    {{-- Date Range Filter --}}
    <form method="GET" class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="flex items-end gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Date début</label>
                <input type="date" name="from" value="{{ $from }}" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Date fin</label>
                <input type="date" name="to" value="{{ $to }}" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl px-6 py-3 transition-all shadow-sm">
                Filtrer
            </button>
        </div>
    </form>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">CA Total</h3>
                <div class="bg-green-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalSales, 2) }} DZD</p>
            <p class="text-sm text-slate-500 mt-1">{{ $salesCount }} ventes</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Dépenses</h3>
                <div class="bg-red-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalExpenses, 2) }} DZD</p>
            <p class="text-sm text-slate-500 mt-1">Approuvées et payées</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Profit Net</h3>
                <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalSales - $totalExpenses, 2) }} DZD</p>
            <p class="text-sm text-slate-500 mt-1">Marge: {{ $totalSales > 0 ? number_format((($totalSales - $totalExpenses) / $totalSales) * 100, 1) : 0 }}%</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Annulations</h3>
                <div class="bg-yellow-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $totalCancellations }}</p>
            <p class="text-sm text-slate-500 mt-1">Ventes annulées</p>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('pos.reports.sales-by-product') }}?from={{ $from }}&to={{ $to }}" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-purple-100 p-3 rounded-xl group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Ventes par Article</h3>
                    <p class="text-sm text-slate-500">Analyse détaillée par produit</p>
                </div>
            </div>
            <p class="text-sm text-slate-600">Consultez les performances de chaque produit avec statistiques de vente complètes</p>
        </a>

        <a href="{{ route('pos.reports.sales-by-cashier') }}?from={{ $from }}&to={{ $to }}" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-green-100 p-3 rounded-xl group-hover:bg-green-200 transition-colors">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Performance Caissiers</h3>
                    <p class="text-sm text-slate-500">KPIs par utilisateur</p>
                </div>
            </div>
            <p class="text-sm text-slate-600">Suivez les performances individuelles : CA, panier moyen, taux d'annulation</p>
        </a>

        <a href="{{ route('pos.expenses') }}?from={{ $from }}&to={{ $to }}" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-red-100 p-3 rounded-xl group-hover:bg-red-200 transition-colors">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Gestion Dépenses</h3>
                    <p class="text-sm text-slate-500">Suivi opérationnel</p>
                </div>
            </div>
            <p class="text-sm text-slate-600">Enregistrez et suivez toutes les dépenses avec workflow d'approbation</p>
        </a>

        <a href="{{ route('pos.reports.cancellations') }}?from={{ $from }}&to={{ $to }}" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-yellow-100 p-3 rounded-xl group-hover:bg-yellow-200 transition-colors">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Annulations</h3>
                    <p class="text-sm text-slate-500">Audit trail complet</p>
                </div>
            </div>
            <p class="text-sm text-slate-600">Historique détaillé de toutes les ventes annulées avec justifications</p>
        </a>

        <a href="{{ route('pos.dashboard') }}" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-indigo-100 p-3 rounded-xl group-hover:bg-indigo-200 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Toutes les Ventes</h3>
                    <p class="text-sm text-slate-500">Historique complet</p>
                </div>
            </div>
            <p class="text-sm text-slate-600">Accédez à l'historique détaillé de toutes les transactions</p>
        </a>
    </div>

</div>

</body>
</html>
