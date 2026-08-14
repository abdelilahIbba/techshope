@extends('layouts.pos')
@section('title', 'Rapports - POS System')
@section('content')



<div class="container p-6 lg:p-8 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-3">
            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Ventes par Article
        </h1>
        <p class="text-slate-500 mt-1">Analyse détaillée des performances par produit</p>
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

    {{-- Sales Data Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Article</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Variante</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-slate-700 uppercase">Qté Vendue</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-slate-700 uppercase">Prix Achat</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-slate-700 uppercase">CA Total</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-slate-700 uppercase">Profit Net</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-slate-700 uppercase">Dernière Vente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesData as $item)
                    @php
                        $profit = $item->total_revenue - $item->total_cost;
                        $profitColor = $profit >= 0 ? 'text-blue-600' : 'text-red-600';
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                            <div class="text-sm text-slate-500">{{ $item->brand ?: 'Marque non définie' }}</div>
                            <div class="text-sm text-slate-500">ID: {{ $item->id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-slate-800">
                                {{ $item->size ? 'Taille '.$item->size : 'Taille standard' }}
                                ·
                                {{ $item->color ? 'Couleur '.$item->color : 'Couleur standard' }}
                            </div>
                            <div class="text-xs text-slate-400 font-mono">{{ $item->variant_barcode ?: 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-900">
                            {{ (float)$item->total_quantity }} <span class="text-[10px] text-slate-400 uppercase">{{ $item->unit === 'metre' ? 'm' : ($item->unit === 'cm' ? 'cm' : 'pcs') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-slate-500 font-medium">
                            {{ number_format($item->purchase_price, 2) }} {{ $currencySymbol }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-900">{{ number_format($item->total_revenue, 2) }} {{ $currencySymbol }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold {{ $profitColor }}">{{ number_format($profit, 2) }} {{ $currencySymbol }}</span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-slate-500">
                            {{ \Carbon\Carbon::parse($item->last_sale_date)->format('d/m/Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="font-semibold">Aucune vente trouvée</p>
                            <p class="text-sm mt-1">Aucun produit vendu dans la période sélectionnée</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

