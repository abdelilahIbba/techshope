<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventes & Annulations - Droguerie POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-btn { transition: all .2s; }
        .tab-btn.active { background: #2563eb; color: #fff; box-shadow: 0 4px 14px rgba(37,99,235,.3); }
        .tab-btn:not(.active) { background: #f1f5f9; color: #475569; }
        .tab-btn:not(.active):hover { background: #e2e8f0; }
        .modal-layer { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); backdrop-filter:blur(4px); z-index:50; align-items:center; justify-content:center; padding:1rem; animation: fadeIn .2s; }
        .modal-layer.open { display:flex; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-box { animation: slideUp .25s; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .row-hover:hover { background: #f8fafc; }
        details summary { cursor: pointer; list-style: none; }
        details summary::-webkit-details-marker { display:none; }
        #barcodeInput:focus { box-shadow: 0 0 0 3px rgba(37,99,235,.25); }
        .barcode-scanning { border-color: #2563eb !important; background: #eff6ff !important; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen antialiased text-slate-800">

{{-- ── Nav ── --}}
@include('partials.pos-nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- ── Header ── --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-5-5 5-5M20 19H9a2 2 0 01-2-2V7"/>
                </svg>
                Ventes &amp; Annulations
            </h1>
            <p class="text-slate-500 text-sm mt-1">Gérez les ventes de la période et consultez l'historique des annulations.</p>
        </div>
        <a href="{{ route('pos.reports') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-semibold text-sm hover:bg-slate-300 transition">
            ← Retour aux rapports
        </a>
    </div>

    {{-- ── Flash messages ── --}}
    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-semibold">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-semibold">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Date Filter ── --}}
    <form method="GET" id="filterForm" class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 mb-4">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="hidden" name="barcode" id="barcodeHidden" value="{{ $barcode }}">

        {{-- Row 1: dates + shortcuts + button --}}
        <div class="flex flex-wrap items-end gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">De</label>
                <input type="date" name="from" id="from" value="{{ $from }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">À</label>
                <input type="date" name="to" id="to" value="{{ $to }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            {{-- Period shortcuts --}}
            <div class="flex gap-2 flex-wrap">
                @php
                    $shortcuts = [
                        'Aujourd\'hui'   => [now()->format('Y-m-d'), now()->format('Y-m-d')],
                        'Cette semaine'  => [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')],
                        'Ce mois'        => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
                        'Mois précédent' => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
                        'Cette année'    => [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
                    ];
                @endphp
                @foreach($shortcuts as $label => [$sfrom, $sto])
                    <button type="button"
                        onclick="document.getElementById('from').value='{{ $sfrom }}';document.getElementById('to').value='{{ $sto }}'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-blue-100 hover:text-blue-700 transition">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <button type="submit" class="ml-auto px-5 py-2 bg-blue-600 text-white rounded-lg font-bold text-sm hover:bg-blue-700 shadow transition">
                Filtrer
            </button>
        </div>

        {{-- Row 2: Barcode search (visible only on Sales tab) --}}
        @if($tab === 'sales')
        <div class="border-t border-slate-100 pt-4">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">🔍 Recherche par code-barres</label>
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    {{-- Scanner icon --}}
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m0 14v1M4 12h1m14 0h1M6.343 6.343l.707.707M16.95 7.05l.707-.707M7.05 16.95l-.707.707M17.657 17.657l-.707-.707"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="barcodeInput"
                        value="{{ $barcode }}"
                        placeholder="Scanner ou saisir un code-barres…"
                        autocomplete="off"
                        class="w-full border-2 border-slate-200 rounded-xl pl-10 pr-10 py-2.5 text-sm font-mono transition-all duration-150 focus:outline-none"
                    >
                    {{-- Clear button --}}
                    <button type="button" id="clearBarcode"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-red-500 transition {{ $barcode ? '' : 'hidden' }}"
                        onclick="clearBarcodeSearch()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <button type="button" onclick="submitBarcodeSearch()"
                    class="px-4 py-2.5 bg-slate-800 text-white rounded-xl font-bold text-sm hover:bg-slate-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Rechercher
                </button>
                @if($barcode)
                    <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h3a1 1 0 010 2H5v2a1 1 0 01-2 0V5a1 1 0 010-2zm0 6a1 1 0 011 1v.01a1 1 0 01-2 0V11a1 1 0 011-1zm14-6a1 1 0 00-1-1h-3a1 1 0 000 2h2v2a1 1 0 002 0V5a1 1 0 00-1-2zm0 6a1 1 0 00-1 1v.01a1 1 0 002 0V11a1 1 0 00-1-1zM5 19v-2H4a1 1 0 010-2h1v-1a1 1 0 012 0v1h1a1 1 0 010 2H7v2H6a1 1 0 010 2 1 1 0 010-2zm9 0v-2a1 1 0 00-2 0v2h-1a1 1 0 000 2h1v2a1 1 0 002 0v-2h1a1 1 0 000-2h-1z"/></svg>
                        Filtré: {{ $barcode }}
                    </span>
                @endif

                <p class="text-xs text-slate-400 italic">Scan auto-detected</p>
            </div>
        </div>
        @endif
    </form>

    {{-- ── Stats Row ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Ventes</p>
                <p class="text-xl font-extrabold text-slate-900">{{ $sales->total() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">CA Période</p>
                <p class="text-xl font-extrabold text-slate-900">{{ number_format($sales->sum('total_amount'), 2) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Annulations</p>
                <p class="text-xl font-extrabold text-slate-900">{{ $cancellations->total() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Remboursé</p>
                <p class="text-xl font-extrabold text-slate-900">{{ number_format($cancellations->sum('refund_amount'), 2) }}</p>
            </div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="flex gap-2 mb-4">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'sales']) }}"
           class="tab-btn px-5 py-2.5 rounded-xl font-bold text-sm {{ $tab === 'sales' ? 'active' : '' }}">
            🛒 Liste des ventes <span class="ml-1 text-xs opacity-70">({{ $sales->total() }})</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'history']) }}"
           class="tab-btn px-5 py-2.5 rounded-xl font-bold text-sm {{ $tab === 'history' ? 'active' : '' }}">
            🔴 Historique annulations <span class="ml-1 text-xs opacity-70">({{ $cancellations->total() }})</span>
        </a>
    </div>

    {{-- ══════════════════════ TAB: Ventes ══════════════════════ --}}
    @if($tab === 'sales')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Vente #</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Date</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Caissier</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Client</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Articles</th>
                    <th class="px-5 py-3.5 text-right font-bold text-slate-600 uppercase text-xs tracking-wide">Montant</th>
                    <th class="px-5 py-3.5 text-center font-bold text-slate-600 uppercase text-xs tracking-wide">Paiement</th>
                    <th class="px-5 py-3.5 text-center font-bold text-slate-600 uppercase text-xs tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sales as $sale)
                    <tr class="row-hover transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-blue-600">#{{ $sale->id }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">
                            {{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3.5 font-medium">{{ $sale->cashier->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $sale->customer->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @if($sale->items->count())
                            <details>
                                <summary class="text-xs text-blue-600 font-semibold hover:underline select-none">
                                    {{ $sale->items->count() }} article(s)
                                </summary>
                                <ul class="mt-1 pl-2 space-y-0.5">
                                    @foreach($sale->items as $item)
                                        <li class="text-xs text-slate-500">
                                            {{ $item->product->name ?? '#'.$item->product_id }}
                                            — {{ number_format($item->quantity, 2) }} × {{ number_format($item->unit_price, 2) }} {{ $currencySymbol }}
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-slate-900">
                            {{ number_format($sale->total_amount, 2) }} {{ $currencySymbol }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @php $pmLabels = ['cash'=>'Espèces','card'=>'Carte','transfer'=>'Virement']; @endphp
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                {{ $pmLabels[$sale->payment_method] ?? ucfirst($sale->payment_method) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <button
                                onclick="openCancelModal({{ $sale->id }}, {{ $sale->total_amount }}, {{ $sale->items->map(fn($i)=>['id'=>$i->id,'name'=>$i->product->name??'Art.#'.$i->product_id,'qty'=>(float)$i->quantity,'unit_price'=>(float)$i->unit_price,'subtotal'=>(float)$i->subtotal,'unit'=>$i->product->unit??'unité'])->toJson() }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 font-semibold text-xs hover:bg-red-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Annuler
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="font-semibold text-slate-500">Aucune vente pour cette période</p>
                                <p class="text-sm">Modifiez la plage de dates pour voir d'autres ventes.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
        <div class="mt-5">{{ $sales->appends(request()->except('sales_page'))->links() }}</div>
    @endif
    @endif

    {{-- ══════════════════════ TAB: Historique ══════════════════════ --}}
    @if($tab === 'history')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Vente #</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Date vente</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Caissier</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Motif</th>
                    <th class="px-5 py-3.5 text-left font-bold text-slate-600 uppercase text-xs tracking-wide">Annulée par</th>
                    <th class="px-5 py-3.5 text-right font-bold text-slate-600 uppercase text-xs tracking-wide">Remboursé</th>
                    <th class="px-5 py-3.5 text-center font-bold text-slate-600 uppercase text-xs tracking-wide">Mode remb.</th>
                    <th class="px-5 py-3.5 text-center font-bold text-slate-600 uppercase text-xs tracking-wide">Date annul.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($cancellations as $cancellation)
                    @php
                        $reasonLabels = [
                            'client_request' => 'Demande client',
                            'error'          => 'Erreur de saisie',
                            'fraud'          => 'Suspicion fraude',
                            'return'         => 'Retour marchandise',
                            'other'          => 'Autre raison',
                        ];
                        $refundLabels = ['cash'=>'Espèces','card'=>'Carte','transfer'=>'Virement'];
                    @endphp
                    <tr class="row-hover transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-red-600">#{{ $cancellation->sale_id }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">
                            {{ $cancellation->sale ? \Carbon\Carbon::parse($cancellation->sale->date)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 font-medium">
                            {{ $cancellation->sale?->cashier?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                {{ $reasonLabels[$cancellation->reason] ?? $cancellation->reason }}
                            </span>
                            @if($cancellation->notes)
                                <p class="text-xs text-slate-400 mt-1">{{ $cancellation->notes }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-medium">
                            {{ $cancellation->cancelledBy->name ?? '—' }}
                            @if($cancellation->authorized_by_user_id && $cancellation->authorized_by_user_id !== $cancellation->cancelled_by_user_id)
                                <p class="text-xs text-slate-400">Auth: {{ $cancellation->authorizedBy->name ?? '—' }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-red-600">
                            {{ number_format($cancellation->refund_amount, 2) }} {{ $currencySymbol }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                {{ $refundLabels[$cancellation->refund_method] ?? ucfirst($cancellation->refund_method) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center text-slate-500 text-xs">
                            {{ \Carbon\Carbon::parse($cancellation->cancelled_at)->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="font-semibold text-slate-500">Aucune annulation sur cette période</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cancellations->hasPages())
        <div class="mt-5">{{ $cancellations->appends(request()->except('cancel_page'))->links() }}</div>
    @endif
    @endif

</div>{{-- /max-w-7xl --}}

{{-- ══════════════════════ UNIFIED CANCEL MODAL ══════════════════════ --}}
<div id="cancelModal" class="modal-layer">
  <div class="modal-box bg-white rounded-2xl shadow-2xl w-full max-w-lg">

    {{-- Header --}}
    <div class="flex items-center gap-3 px-6 pt-5 pb-4 border-b border-slate-100">
      <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <h2 class="text-base font-bold text-slate-900">Annulation — Vente <span id="modalSaleId" class="text-red-600"></span></h2>
        <p class="text-xs text-slate-500">Choisissez le type d'annulation</p>
      </div>
      <button onclick="closeCancelModal()" class="ml-auto text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    {{-- Step 1: Choose type --}}
    <div id="step1" class="px-6 py-5">
      <p class="text-sm font-semibold text-slate-700 mb-4">Que souhaitez-vous annuler ?</p>
      <div class="grid grid-cols-2 gap-3">
        <button onclick="showStep('ticket')"
          class="flex flex-col items-center gap-2 p-5 rounded-2xl border-2 border-slate-200 hover:border-red-400 hover:bg-red-50 transition group">
          <svg class="w-8 h-8 text-slate-400 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <div class="text-center">
            <p class="font-bold text-sm text-slate-800 group-hover:text-red-700">Tout le ticket</p>
            <p class="text-xs text-slate-400 mt-0.5">Annuler la vente entière</p>
          </div>
        </button>
        <button onclick="showStep('item')"
          class="flex flex-col items-center gap-2 p-5 rounded-2xl border-2 border-slate-200 hover:border-orange-400 hover:bg-orange-50 transition group">
          <svg class="w-8 h-8 text-slate-400 group-hover:text-orange-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
          </svg>
          <div class="text-center">
            <p class="font-bold text-sm text-slate-800 group-hover:text-orange-700">Un article</p>
            <p class="text-xs text-slate-400 mt-0.5">Annuler un produit du ticket</p>
          </div>
        </button>
      </div>
    </div>

    {{-- Step 2A: Cancel whole ticket --}}
    <div id="step-ticket" class="hidden">
      <div class="px-6 pb-1 pt-2">
        <button onclick="showStep('start')" class="flex items-center gap-1 text-xs text-slate-500 hover:text-blue-600 font-semibold mb-3 transition">
          ← Retour
        </button>
        <p class="text-sm font-bold text-red-600 mb-4">⚠ Annulation du ticket entier — stock entièrement restauré</p>
      </div>
      <form id="cancelTicketForm" method="POST" class="px-6 pb-5 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Motif <span class="text-red-500">*</span></label>
          <select name="reason" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option value="">— Sélectionner —</option>
            <option value="client_request">Demande client</option>
            <option value="error">Erreur de saisie</option>
            <option value="fraud">Suspicion de fraude</option>
            <option value="return">Retour marchandise</option>
            <option value="other">Autre raison</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Notes (optionnel)</label>
          <textarea name="notes" rows="2" placeholder="Détails…" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Montant remboursé <span class="text-red-500">*</span></label>
            <input type="number" name="refund_amount" id="ticketRefundAmount" step="0.01" min="0" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Mode remboursement <span class="text-red-500">*</span></label>
            <select name="refund_method" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
              <option value="cash">Espèces</option>
              <option value="card">Carte</option>
              <option value="transfer">Virement</option>
            </select>
          </div>
        </div>
        <div class="flex gap-3 pt-1">
          <button type="button" onclick="closeCancelModal()" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-sm hover:bg-slate-200 transition">Fermer</button>
          <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm hover:bg-red-700 shadow-lg shadow-red-600/20 transition">Annuler le ticket</button>
        </div>
      </form>
    </div>

    {{-- Step 2B: Cancel one item --}}
    <div id="step-item" class="hidden">
      <div class="px-6 pb-1 pt-2">
        <button onclick="showStep('start')" class="flex items-center gap-1 text-xs text-slate-500 hover:text-blue-600 font-semibold mb-3 transition">
          ← Retour
        </button>
        <p class="text-sm font-bold text-orange-600 mb-3">Sélectionnez l'article à annuler</p>
      </div>

      {{-- Product list rendered by JS --}}
      <div id="itemList" class="px-6 max-h-48 overflow-y-auto space-y-2 mb-4"></div>

      <form id="cancelItemForm" method="POST" class="px-6 pb-5 space-y-3">
        @csrf
        <input type="hidden" name="_selected_item_id" id="selectedItemId">
        <div id="itemFormFields" class="hidden space-y-3">
          <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3">
            <p class="text-xs font-bold text-orange-700 mb-1">Article sélectionné</p>
            <p id="selectedItemName" class="font-semibold text-slate-900 text-sm"></p>
            <p id="selectedItemInfo" class="text-xs text-slate-500 mt-0.5"></p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Qté à annuler <span class="text-red-500">*</span></label>
              <input type="number" name="qty_to_cancel" id="qtyToCancel" step="0.001" min="0.001" required
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Remboursement estimé</label>
              <div id="estimatedRefund" class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-800">—</div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Motif <span class="text-red-500">*</span></label>
              <select name="reason" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                <option value="">— Sélectionner —</option>
                <option value="client_request">Demande client</option>
                <option value="error">Erreur de saisie</option>
                <option value="return">Retour marchandise</option>
                <option value="other">Autre raison</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Mode remboursement <span class="text-red-500">*</span></label>
              <select name="refund_method" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                <option value="cash">Espèces</option>
                <option value="card">Carte</option>
                <option value="transfer">Virement</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Notes (optionnel)</label>
            <textarea name="notes" rows="2" placeholder="Détails…" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"></textarea>
          </div>
          <div class="flex gap-3 pt-1">
            <button type="button" onclick="closeCancelModal()" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-sm hover:bg-slate-200 transition">Fermer</button>
            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 shadow-lg shadow-orange-500/20 transition">Annuler l'article</button>
          </div>
        </div>
      </form>
    </div>

  </div>
</div>
<script>
    const cancelModal = document.getElementById('cancelModal');
    let currentSaleId    = null;
    let currentSaleTotal = 0;
    let currentItems     = [];
    let selectedItem     = null; // {id, name, qty, unit_price, unit}

    // ── Open/close ─────────────────────────────────────────────
    function openCancelModal(saleId, totalAmount, items) {
        currentSaleId    = saleId;
        currentSaleTotal = parseFloat(totalAmount);
        currentItems     = items || [];
        selectedItem     = null;

        document.getElementById('modalSaleId').textContent = '#' + saleId;
        document.getElementById('ticketRefundAmount').value = currentSaleTotal.toFixed(2);
        document.getElementById('ticketRefundAmount').max   = currentSaleTotal.toFixed(2);
        document.getElementById('cancelTicketForm').action  = '/pos/sales/' + saleId + '/cancel';

        showStep('start');
        cancelModal.classList.add('open');
    }

    function closeCancelModal() {
        cancelModal.classList.remove('open');
    }

    // ── Step navigation ───────────────────────────────────────
    function showStep(which) {
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step-ticket').classList.add('hidden');
        document.getElementById('step-item').classList.add('hidden');

        if (which === 'start') {
            document.getElementById('step1').classList.remove('hidden');
        } else if (which === 'ticket') {
            document.getElementById('step-ticket').classList.remove('hidden');
        } else if (which === 'item') {
            renderItemList();
            document.getElementById('step-item').classList.remove('hidden');
            document.getElementById('itemFormFields').classList.add('hidden');
        }
    }

    // ── Item list renderer ───────────────────────────────────────
    function renderItemList() {
        const container = document.getElementById('itemList');
        if (!currentItems.length) {
            container.innerHTML = '<p class="text-sm text-slate-400 italic text-center py-4">Aucun article dans ce ticket.</p>';
            return;
        }
        container.innerHTML = currentItems.map(it => `
            <button type="button"
                onclick="selectItem(${it.id})"
                data-item-id="${it.id}"
                class="item-btn w-full text-left flex items-center justify-between gap-3 px-4 py-3 rounded-xl border-2 border-slate-200 hover:border-orange-400 hover:bg-orange-50 transition">
              <div>
                <p class="font-semibold text-sm text-slate-900">${it.name}</p>
                <p class="text-xs text-slate-500">${it.qty.toFixed(3)} ${it.unit} &times; ${it.unit_price.toFixed(2)}</p>
              </div>
              <span class="text-sm font-bold text-slate-700 whitespace-nowrap">${it.subtotal.toFixed(2)}</span>
            </button>
        `).join('');
    }

    function selectItem(itemId) {
        selectedItem = currentItems.find(i => i.id === itemId);
        if (!selectedItem) return;

        // Highlight selected
        document.querySelectorAll('.item-btn').forEach(b => {
            b.classList.toggle('border-orange-500', parseInt(b.dataset.itemId) === itemId);
            b.classList.toggle('bg-orange-50',      parseInt(b.dataset.itemId) === itemId);
        });

        document.getElementById('selectedItemName').textContent = selectedItem.name;
        document.getElementById('selectedItemInfo').textContent =
            `Qté: ${selectedItem.qty.toFixed(3)} ${selectedItem.unit} × ${selectedItem.unit_price.toFixed(2)} = ${selectedItem.subtotal.toFixed(2)}`;

        const qtyInput = document.getElementById('qtyToCancel');
        qtyInput.max   = selectedItem.qty;
        qtyInput.value = selectedItem.qty.toFixed(3);
        updateEstimatedRefund();

        document.getElementById('cancelItemForm').action =
            '/pos/sales/' + currentSaleId + '/cancel-item/' + selectedItem.id;

        document.getElementById('itemFormFields').classList.remove('hidden');
    }

    function updateEstimatedRefund() {
        if (!selectedItem) return;
        const qty = parseFloat(document.getElementById('qtyToCancel').value) || 0;
        const refund = (qty * selectedItem.unit_price).toFixed(2);
        document.getElementById('estimatedRefund').textContent = refund;
    }

    document.getElementById('qtyToCancel').addEventListener('input', updateEstimatedRefund);

    // Backdrop click
    cancelModal.addEventListener('click', function(e) {
        if (e.target === cancelModal) closeCancelModal();
    });

    // ── Barcode Search ────────────────────────────────────────────
    const barcodeInput  = document.getElementById('barcodeInput');
    const barcodeHidden = document.getElementById('barcodeHidden');
    const clearBtn      = document.getElementById('clearBarcode');

    if (barcodeInput) {
        barcodeInput.addEventListener('input', function() {
            clearBtn && (clearBtn.classList.toggle('hidden', !this.value));
        });
        barcodeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); submitBarcodeSearch(); }
        });

        let scanBuffer = '', scanTimer = null;
        document.addEventListener('keydown', function(e) {
            const active = document.activeElement;
            const isOurInput    = active === barcodeInput;
            const isOtherInput  = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT') && !isOurInput;
            if (isOtherInput || cancelModal.classList.contains('open')) return;
            if (e.key === 'Enter') {
                if (scanBuffer.length >= 4) {
                    if (barcodeInput) { barcodeInput.value = scanBuffer; clearBtn && clearBtn.classList.remove('hidden'); }
                    submitBarcodeSearch();
                }
                scanBuffer = '';
                if (scanTimer) clearTimeout(scanTimer);
                return;
            }
            if (e.key.length === 1) {
                scanBuffer += e.key;
                if (scanTimer) clearTimeout(scanTimer);
                scanTimer = setTimeout(() => { scanBuffer = ''; }, 80);
            }
        });
    }

    function submitBarcodeSearch() {
        if (barcodeHidden) barcodeHidden.value = barcodeInput ? barcodeInput.value.trim() : '';
        document.getElementById('filterForm').submit();
    }
    function clearBarcodeSearch() {
        if (barcodeInput)  barcodeInput.value  = '';
        if (barcodeHidden) barcodeHidden.value = '';
        if (clearBtn)      clearBtn.classList.add('hidden');
        document.getElementById('filterForm').submit();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeCancelModal();
    });
</script>
</body>
</html>
