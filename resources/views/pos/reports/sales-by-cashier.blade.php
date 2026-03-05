<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Caissiers - POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50">

<nav class="flex items-center gap-2 p-4 bg-white border-b border-slate-200 shadow-sm">
    <a class="px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100" href="{{ route('pos.index') }}">POS</a>
    <a class="px-4 py-2.5 rounded-lg text-sm font-semibold bg-blue-600 text-white shadow-md" href="{{ route('pos.reports') }}">Rapports</a>
    <a class="px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100" href="{{ route('pos.settings') }}">Settings</a>
</nav>

<div class="container p-6 lg:p-8 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-3">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Performance des Caissiers
        </h1>
        <p class="text-slate-500 mt-1">KPIs et statistiques par utilisateur</p>
    </div>

    {{-- Date Filter --}}
    <form method="GET" class="bg-white rounded-xl p-4 mb-6 border border-slate-200">
        <div class="flex items-end gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">De</label>
                <input type="date" name="from" value="{{ $from }}" class="border border-slate-300 rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">À</label>
                <input type="date" name="to" value="{{ $to }}" class="border border-slate-300 rounded-lg p-2">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700">
                Filtrer
            </button>
            <a href="{{ route('pos.reports') }}" class="bg-slate-100 text-slate-700 px-6 py-2 rounded-lg font-semibold hover:bg-slate-200">
                ← Retour
            </a>
        </div>
    </form>

    {{-- Cashier Performance Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($salesData as $cashier)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-900">{{ $cashier->cashier->name ?? 'Inconnu' }}</h3>
                        <p class="text-sm text-slate-500">Caissier</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Nombre de ventes</span>
                        <span class="font-bold text-slate-900">{{ number_format($cashier->total_sales) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">CA généré</span>
                        <span class="font-bold text-green-600">{{ number_format($cashier->total_revenue, 2) }} DZD</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Panier moyen</span>
                        <span class="font-bold text-blue-600">{{ number_format($cashier->avg_basket, 2) }} DZD</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                        <span class="text-sm text-slate-600">Taux d'annulation</span>
                        <span class="font-bold {{ $cashier->cancellation_rate > 5 ? 'text-red-600' : 'text-slate-900' }}">
                            {{ $cashier->cancellation_rate }}%
                        </span>
                    </div>
                </div>

                {{-- Performance Badge --}}
                <div class="mt-4 pt-4 border-t border-slate-100">
                    @if($cashier->total_revenue > 100000)
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 rounded-full px-3 py-1 text-xs font-semibold">
                            ⭐⭐⭐⭐ Excellent
                        </span>
                    @elseif($cashier->total_revenue > 50000)
                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-3 py-1 text-xs font-semibold">
                            ⭐⭐⭐ Très bon
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-slate-50 text-slate-700 border border-slate-200 rounded-full px-3 py-1 text-xs font-semibold">
                            ⭐⭐ Bon
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white border border-slate-200 rounded-xl p-12 text-center">
                <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="font-semibold text-slate-900">Aucune donnée disponible</p>
                <p class="text-sm text-slate-500 mt-1">Aucune vente enregistrée pour cette période</p>
            </div>
        @endforelse
    </div>
</div>

</body>
</html>
