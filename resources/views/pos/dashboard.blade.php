@extends('layouts.pos')
@section('title', 'Tableau de bord - CA')
@section('content')

<div class="container flex-1 p-6 lg:p-8 overflow-y-auto w-full max-w-7xl mx-auto">

    <div class="page-header mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="page-title text-3xl font-extrabold text-slate-900 tracking-tight">Tableau de bord</h1>
            <p class="page-subtitle text-slate-500 mt-1 font-medium">Vue d'ensemble des ventes, du stock et des annulations</p>
        </div>
        <p class="text-sm text-slate-400">Connecté en tant que <span class="font-semibold text-slate-600">{{ auth()->user()->name }}</span></p>
    </div>

    {{-- ── Day selector ─────────────────────────────────────────────────── --}}
    <div class="bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-5 mb-6 flex flex-wrap items-end gap-4">
        <form method="GET" action="{{ route('pos.dashboard') }}" id="day-filter-form" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-semibold text-slate-700">Jour sélectionné</label>
                <input type="date" name="date" id="day-filter-input" value="{{ $date }}"
                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-48 p-2.5 outline-none transition-all">
            </div>
            <div class="flex gap-2">
                <button type="button" data-quick-day="{{ now()->format('Y-m-d') }}" class="quick-day-btn bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-sm px-4 py-2.5 transition-all">Aujourd'hui</button>
                <button type="button" data-quick-day="{{ now()->subDay()->format('Y-m-d') }}" class="quick-day-btn bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-sm px-4 py-2.5 transition-all">Hier</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm px-6 py-2.5 transition-all shadow-sm focus:ring-4 focus:ring-blue-300">Voir ce jour</button>
            </div>
        </form>
    </div>

    {{-- ── Daily snapshot ───────────────────────────────────────────────── --}}
    <div class="stats-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">CA du jour</div>
            <div class="text-3xl font-extrabold text-emerald-600">{{ number_format($dailyRevenue, 2) }} <span class="text-xl text-emerald-500">{{ $currencySymbol }}</span></div>
        </div>
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Ventes du jour</div>
            <div class="text-3xl font-extrabold text-slate-900">{{ $dailySalesCount }}</div>
        </div>
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Produits vendus (jour)</div>
            <div class="text-3xl font-extrabold text-slate-900">{{ (float) $dailyProductsCount }}</div>
        </div>
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Annulations du jour</div>
            <div class="text-3xl font-extrabold text-rose-500">{{ $dailyCancellationsCount }}</div>
            @if($dailyCancellationsCount > 0)
                <div class="text-xs text-rose-400 font-semibold mt-1">{{ number_format($dailyRefundedAmount, 2) }} {{ $currencySymbol }} remboursés</div>
            @endif
        </div>
    </div>

    {{-- ── Tickets du jour ──────────────────────────────────────────────── --}}
    <div class="table-container bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl overflow-hidden mb-8">
        <h2 class="text-lg font-bold text-slate-900 p-5 border-b border-slate-200 bg-slate-50/50">
            Tickets du {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}
            <span class="text-sm font-medium text-slate-400 ml-2">({{ $dailyTickets->count() }} ticket(s))</span>
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-bold">Ticket</th>
                        <th class="px-6 py-4 font-bold">Heure</th>
                        <th class="px-6 py-4 font-bold">Client</th>
                        <th class="px-6 py-4 font-bold">Caissier</th>
                        <th class="px-6 py-4 font-bold">Montant</th>
                        <th class="px-6 py-4 font-bold">Paiement</th>
                        <th class="px-6 py-4 font-bold">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailyTickets as $ticket)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0 {{ $ticket->status === 'cancelled' ? 'opacity-60' : '' }}">
                            <td class="px-6 py-4 font-medium text-slate-900">#{{ $ticket->id }}</td>
                            <td class="px-6 py-4">{{ $ticket->date->format('H:i') }}</td>
                            <td class="px-6 py-4">{{ $ticket->customer->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $ticket->cashier->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ number_format($ticket->total_amount, 2) }} {{ $currencySymbol }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                    {{ $ticket->payment_method === 'cash' ? $cashPaymentLabel : ucfirst($ticket->payment_method) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->status === 'cancelled')
                                    <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                        Annulé{{ $ticket->cancellation?->reason ? ' · '.$ticket->cancellation->reason : '' }}
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">Complété</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">Aucun ticket pour ce jour</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Charts ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6">
            <h3 class="text-base font-bold text-slate-900 mb-1">Tendance des ventes (14 jours)</h3>
            <p class="text-xs text-slate-400 mb-4">CA quotidien jusqu'au {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</p>
            <div class="h-64"><canvas id="chart-sales-trend"></canvas></div>
        </div>
        <div class="bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6">
            <h3 class="text-base font-bold text-slate-900 mb-1">Annulations (14 jours)</h3>
            <p class="text-xs text-slate-400 mb-4">Nombre de ventes annulées par jour</p>
            <div class="h-64"><canvas id="chart-cancellations"></canvas></div>
        </div>
        <div class="bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6">
            <h3 class="text-base font-bold text-slate-900 mb-1">Meilleurs produits du jour</h3>
            <p class="text-xs text-slate-400 mb-4">Top 6 par chiffre d'affaires</p>
            <div class="h-64"><canvas id="chart-top-products"></canvas></div>
        </div>
        <div class="grid grid-cols-2 gap-6">
            <div class="bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6">
                <h3 class="text-base font-bold text-slate-900 mb-1">Paiements du jour</h3>
                <p class="text-xs text-slate-400 mb-4">Répartition par mode</p>
                <div class="h-48"><canvas id="chart-payment"></canvas></div>
            </div>
            <div class="bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6">
                <h3 class="text-base font-bold text-slate-900 mb-1">Alertes stock</h3>
                <p class="text-xs text-slate-400 mb-4">Seuil : {{ $lowStockThreshold }} · {{ $lowStockCount }} produit(s) concerné(s)</p>
                <div class="h-48"><canvas id="chart-stock"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ── Period report (existing detailed range report) ──────────────────── --}}
    <div class="filter-section bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-5 mb-8">
        <form method="GET" action="{{ route('pos.dashboard') }}" class="filter-form flex flex-wrap gap-4 items-end">
            <input type="hidden" name="date" value="{{ $date }}">
            <div class="filter-group flex flex-col gap-1.5">
                <label class="filter-label text-sm font-semibold text-slate-700">Date début</label>
                <input type="date" name="from" class="filter-input bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full md:w-48 p-2.5 outline-none transition-all" value="{{ $from }}">
            </div>
            <div class="filter-group flex flex-col gap-1.5">
                <label class="filter-label text-sm font-semibold text-slate-700">Date fin</label>
                <input type="date" name="to" class="filter-input bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full md:w-48 p-2.5 outline-none transition-all" value="{{ $to }}">
            </div>
            <button type="submit" class="filter-btn bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm px-6 py-2.5 transition-all shadow-sm focus:ring-4 focus:ring-blue-300">
                Filtrer la période
            </button>
            <button type="button" onclick="window.open('{{ route('pos.dashboard.print', ['from' => $from, 'to' => $to]) }}', '_blank')" class="filter-btn bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-sm px-6 py-2.5 transition-all shadow-sm">
                Imprimer le rapport
            </button>
        </form>
    </div>

    <div class="stats-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">CA Total (période)</div>
            <div class="stat-value success text-3xl font-extrabold text-emerald-600">{{ number_format($totalRevenue, 2) }} <span class="text-xl text-emerald-500">{{ $currencySymbol }}</span></div>
        </div>
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Ventes (période)</div>
            <div class="stat-value text-3xl font-extrabold text-slate-900">{{ $salesCount }}</div>
        </div>
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Produits vendus</div>
            <div class="stat-value text-3xl font-extrabold text-slate-900">{{ $productsCount }}</div>
        </div>
        <div class="stat-card bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl p-6 hover:shadow-md transition-shadow">
            <div class="stat-label text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">En stock</div>
            <div class="stat-value text-3xl font-extrabold text-red-500">{{ $lowStockCount }} <span class="text-xl text-red-400 font-medium">alertes</span></div>
        </div>
    </div>

    <div class="table-container bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl overflow-hidden mb-8">
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
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ number_format($sale->total_amount, 2) }} {{ $currencySymbol }}</td>
                            <td class="px-6 py-4">
                                <span class="badge badge-success bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                    {{ $sale->payment_method === 'cash' ? $cashPaymentLabel : ucfirst($sale->payment_method) }}
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

    <div class="table-container bg-white shadow-sm ring-1 ring-slate-900/5 rounded-2xl overflow-hidden mb-8">
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
                                <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-md font-semibold text-xs">{{ (float)$item->total_quantity }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-emerald-600">{{ number_format($item->total_revenue, 2) }} {{ $currencySymbol }}</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function() {
    if (typeof Chart === 'undefined') {
        return; // CDN unreachable (offline terminal) — page still works, just without charts.
    }

    document.querySelectorAll('.quick-day-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('day-filter-input').value = btn.dataset.quickDay;
            document.getElementById('day-filter-form').submit();
        });
    });

    var salesTrend = @json($salesTrend);
    var cancellationsTrend = @json($cancellationsTrend);
    var topProducts = @json($topProducts);
    var paymentBreakdown = @json($paymentBreakdownDaily);
    var criticalStockCount = {{ (int) $criticalStockCount }};
    var alertStockCount = {{ (int) $alertStockCount }};

    var palette = {
        blue: '#2563eb', blueSoft: 'rgba(37,99,235,0.12)',
        emerald: '#10b981', rose: '#f43f5e', amber: '#f59e0b', slate: '#64748b',
    };

    Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
    Chart.defaults.color = '#64748b';

    new Chart(document.getElementById('chart-sales-trend'), {
        type: 'bar',
        data: {
            labels: salesTrend.map(r => r.label),
            datasets: [{
                label: 'CA ({{ $currencySymbol }})',
                data: salesTrend.map(r => r.revenue),
                backgroundColor: palette.blueSoft,
                borderColor: palette.blue,
                borderWidth: 2,
                borderRadius: 6,
                maxBarThickness: 28,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
        },
    });

    new Chart(document.getElementById('chart-cancellations'), {
        type: 'line',
        data: {
            labels: cancellationsTrend.map(r => r.label),
            datasets: [{
                label: 'Annulations',
                data: cancellationsTrend.map(r => r.count),
                borderColor: palette.rose,
                backgroundColor: 'rgba(244,63,94,0.12)',
                fill: true, tension: 0.3, pointRadius: 3,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
        },
    });

    new Chart(document.getElementById('chart-top-products'), {
        type: 'bar',
        data: {
            labels: topProducts.map(p => p.product_name),
            datasets: [{
                label: 'CA ({{ $currencySymbol }})',
                data: topProducts.map(p => Number(p.total_revenue)),
                backgroundColor: palette.emerald,
                borderRadius: 6,
                maxBarThickness: 22,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } },
        },
    });

    var paymentLabels = paymentBreakdown.map(p => p.payment_method === 'cash' ? '{{ $cashPaymentLabel }}' : (p.payment_method.charAt(0).toUpperCase() + p.payment_method.slice(1)));
    new Chart(document.getElementById('chart-payment'), {
        type: 'doughnut',
        data: {
            labels: paymentLabels.length ? paymentLabels : ['Aucune donnée'],
            datasets: [{
                data: paymentBreakdown.length ? paymentBreakdown.map(p => Number(p.total)) : [1],
                backgroundColor: [palette.blue, palette.emerald, palette.amber, palette.slate],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        },
    });

    new Chart(document.getElementById('chart-stock'), {
        type: 'doughnut',
        data: {
            labels: ['Critique', 'En alerte'],
            datasets: [{
                data: (criticalStockCount + alertStockCount) > 0 ? [criticalStockCount, alertStockCount] : [0, 1],
                backgroundColor: [palette.rose, palette.amber],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        },
    });
})();
</script>
@endpush

@endsection
