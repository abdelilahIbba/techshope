<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Dépenses - POS System</title>
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
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Gestion des Dépenses
            </h1>
            <p class="text-slate-500 mt-1">Suivi des coûts opérationnels</p>
        </div>
        <button onclick="alert('Fonctionnalité en développement')" class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nouvelle Dépense
        </button>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold text-slate-500 uppercase">En attente</h3>
                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Pending</span>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalPending, 2) }} DZD</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold text-slate-500 uppercase">Approuvées</h3>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Approved</span>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalApproved, 2) }} DZD</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold text-slate-500 uppercase">Payées</h3>
                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Paid</span>
            </div>
            <p class="text-3xl font-bold text-red-600">{{ number_format($totalPaid, 2) }} DZD</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl p-4 mb-6 border border-slate-200">
        <div class="flex items-end gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Statut</label>
                <select name="status" class="border border-slate-300 rounded-lg p-2 bg-white">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Tous</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approuvées</option>
                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejetées</option>
                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Payées</option>
                </select>
            </div>
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

    {{-- Expenses Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Catégorie</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Description</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Créée par</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-slate-700 uppercase">Montant</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-slate-700 uppercase">Statut</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-slate-700 uppercase">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-6 py-4">
                            @php
                                $categoryLabels = [
                                    'rent' => 'Loyer',
                                    'utilities' => 'Utilités',
                                    'salaries' => 'Salaires',
                                    'supplies' => 'Fournitures',
                                    'maintenance' => 'Maintenance',
                                    'marketing' => 'Marketing',
                                    'other' => 'Autre'
                                ];
                            @endphp
                            <span class="font-semibold text-slate-900">{{ $categoryLabels[$expense->category] ?? $expense->category }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-900">{{ $expense->description }}</div>
                            @if($expense->notes)
                                <div class="text-sm text-slate-500 mt-1">{{ $expense->notes }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-900">{{ $expense->createdBy->name ?? 'Inconnu' }}</div>
                            <div class="text-sm text-slate-500">{{ $expense->payment_method }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-red-600">{{ number_format($expense->amount, 2) }} DZD</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-blue-100 text-blue-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'paid' => 'bg-green-100 text-green-800'
                                ];
                                $statusLabels = [
                                    'pending' => 'En attente',
                                    'approved' => 'Approuvée',
                                    'rejected' => 'Rejetée',
                                    'paid' => 'Payée'
                                ];
                            @endphp
                            <span class="inline-flex items-center {{ $statusColors[$expense->status] ?? 'bg-slate-100 text-slate-800' }} text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                {{ $statusLabels[$expense->status] ?? $expense->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-slate-500">
                            {{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="font-semibold">Aucune dépense trouvée</p>
                            <p class="text-sm mt-1">Aucune dépense enregistrée pour cette période</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($expenses->hasPages())
        <div class="mt-6">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

</body>
</html>
