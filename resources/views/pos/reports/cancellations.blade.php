<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique Annulations - POS System</title>
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
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Historique des Annulations
        </h1>
        <p class="text-slate-500 mt-1">Audit trail complet des ventes annulées</p>
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

    {{-- Cancellations Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Vente #</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Motif</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase">Annulée par</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-slate-700 uppercase">Montant remboursé</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-slate-700 uppercase">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cancellations as $cancellation)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-900">Vente #{{ $cancellation->sale_id }}</div>
                            <div class="text-sm text-slate-500">{{ $cancellation->refund_method }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $reasonLabels = [
                                    'client_request' => 'Demande client',
                                    'error' => 'Erreur de saisie',
                                    'fraud' => 'Suspicion fraude',
                                    'return' => 'Retour marchandise',
                                    'other' => 'Autre raison'
                                ];
                            @endphp
                            <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                {{ $reasonLabels[$cancellation->reason] ?? $cancellation->reason }}
                            </span>
                            @if($cancellation->notes)
                                <div class="text-sm text-slate-500 mt-1">{{ $cancellation->notes }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-900">{{ $cancellation->cancelledBy->name ?? 'Inconnu' }}</div>
                            @if($cancellation->authorized_by_user_id && $cancellation->authorized_by_user_id != $cancellation->cancelled_by_user_id)
                                <div class="text-sm text-slate-500">Autorisée par: {{ $cancellation->authorizedBy->name ?? 'Inconnu' }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-red-600">{{ number_format($cancellation->refund_amount, 2) }} DZD</span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-slate-500">
                            {{ \Carbon\Carbon::parse($cancellation->cancelled_at)->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="font-semibold">Aucune annulation</p>
                            <p class="text-sm mt-1">Aucune vente annulée dans la période sélectionnée</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($cancellations->hasPages())
        <div class="mt-6">
            {{ $cancellations->links() }}
        </div>
    @endif
</div>

</body>
</html>
