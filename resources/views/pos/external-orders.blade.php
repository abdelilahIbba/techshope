@extends('layouts.pos')
@section('title', 'Commandes Externes — TechShope')

@push('styles')
<style>
[x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="flex-1 flex flex-col h-full bg-slate-50 overflow-hidden" x-data="externalOrdersManager()" x-cloak>

    {{-- ═══════════════════════════════════════════════════════════ TOP HEADER --}}
    <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                 style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 leading-tight">Commandes Externes</h1>
                <p class="text-xs text-slate-500 font-medium">طلبات Instagram & WhatsApp — إدارة الكوموندات الخارجية</p>
            </div>
        </div>

        <button type="button" @click="openCreate()"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm text-white shadow-md"
                style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle Commande
        </button>
    </header>

    {{-- ═══════════════════════════════════════════════════════════ MAIN AREA --}}
    <div class="flex-1 overflow-y-auto p-5 space-y-5">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-4 py-3 shadow-sm text-sm font-semibold">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl px-4 py-3 shadow-sm text-sm font-semibold space-y-1">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- ─── Stats Row ─────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">

            @php
                $statsConfig = [
                    ['label' => 'Total', 'value' => $stats['total'], 'bg' => '#f1f5f9', 'text' => '#475569', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['label' => 'En attente', 'value' => $stats['en_attente'], 'bg' => '#fef9c3', 'text' => '#854d0e', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Expédié', 'value' => $stats['expedie'], 'bg' => '#dbeafe', 'text' => '#1e40af', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8'],
                    ['label' => 'Livré', 'value' => $stats['livre'], 'bg' => '#dcfce7', 'text' => '#166534', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Annulé', 'value' => $stats['annule'], 'bg' => '#fee2e2', 'text' => '#991b1b', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
            @endphp

            @foreach($statsConfig as $s)
                <div class="rounded-2xl p-4 flex items-center gap-3 border border-white/60"
                     style="background-color: {{ $s['bg'] }};">
                    <div class="p-2 rounded-xl" style="background: rgba(0,0,0,0.06);">
                        <svg class="w-5 h-5" style="color: {{ $s['text'] }};" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wide" style="color: {{ $s['text'] }}; opacity: 0.7;">{{ $s['label'] }}</div>
                        <div class="text-xl font-black" style="color: {{ $s['text'] }};">{{ $s['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CA Livré Banner --}}
        @if($stats['ca_total'] > 0)
        <div class="rounded-2xl px-5 py-3 flex items-center justify-between"
             style="background: linear-gradient(135deg, #7c3aed22, #2563eb22); border: 1px solid #7c3aed33;">
            <div class="flex items-center gap-2 text-sm font-bold text-violet-900">
                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                CA Réalisé (Commandes Livrées)
            </div>
            <span class="text-lg font-black text-violet-800">{{ number_format($stats['ca_total'], 2) }} DH</span>
        </div>
        @endif

        {{-- ─── Filters ────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('pos.external-orders') }}"
              class="bg-white rounded-2xl border border-slate-200 px-4 py-3 flex flex-wrap gap-3 items-end shadow-sm">
            <div class="flex-1 min-w-40">
                <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom client, téléphone…"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 outline-none focus:border-violet-400">
            </div>
            <div>
                <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Plateforme</label>
                <select name="platform" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 outline-none focus:border-violet-400">
                    <option value="">Toutes</option>
                    @foreach($platforms as $p)
                        <option value="{{ $p }}" @selected(request('platform') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Statut</label>
                <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 outline-none focus:border-violet-400">
                    <option value="">Tous</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white"
                    style="background: #7c3aed;">Filtrer</button>
            @if(request()->anyFilled(['search','platform','status']))
                <a href="{{ route('pos.external-orders') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200">Réinitialiser</a>
            @endif
        </form>

        {{-- ─── Table ──────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-4 py-3.5">Client</th>
                            <th class="px-4 py-3.5">Téléphone</th>
                            <th class="px-4 py-3.5">Source</th>
                            <th class="px-4 py-3.5">Statut</th>
                            <th class="px-4 py-3.5 text-right">Montant</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($orders as $order)
                            @php
                                $sc = $statusColors[$order->status] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'dot' => '#94a3b8'];
                                $pc = $platformColors[$order->platform_source] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                            @endphp
                            <tr class="hover:bg-violet-50/30 transition-colors">

                                {{-- Date --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $order->created_at->format('d/m/Y') }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $order->created_at->format('H:i') }}</div>
                                </td>

                                {{-- Client --}}
                                <td class="px-4 py-3.5">
                                    <div class="font-extrabold text-slate-900">{{ $order->client_name }}</div>
                                    <div class="text-slate-400 text-[11px] max-w-xs truncate" title="{{ $order->client_address }}">
                                        📍 {{ Str::limit($order->client_address, 40) }}
                                    </div>
                                </td>

                                {{-- Phone --}}
                                <td class="px-4 py-3.5 font-mono font-bold text-slate-700">{{ $order->client_phone }}</td>

                                {{-- Platform --}}
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold"
                                          style="background-color: {{ $pc['bg'] }}; color: {{ $pc['text'] }};">
                                        @if($order->platform_source === 'Instagram')
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                        @elseif($order->platform_source === 'WhatsApp')
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                        @endif
                                        {{ $order->platform_source }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold"
                                          style="background-color: {{ $sc['bg'] }}; color: {{ $sc['text'] }};">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $sc['dot'] }};"></span>
                                        {{ $order->status }}
                                    </span>
                                </td>

                                {{-- Amount --}}
                                <td class="px-4 py-3.5 text-right font-extrabold text-slate-900">
                                    {{ number_format($order->total_amount, 2) }} DH
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        {{-- Quick status change --}}
                                        <div class="relative" x-data="{ open: false }">
                                            <button type="button" @click="open = !open"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-violet-600 hover:bg-violet-50 transition-colors"
                                                    title="Changer le statut">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                 class="absolute right-0 mt-1 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden z-20 min-w-36">
                                                @foreach($statuses as $st)
                                                    @php $stc = $statusColors[$st] ?? []; @endphp
                                                    <form method="POST" action="{{ route('pos.external-orders.status', $order) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ $st }}">
                                                        <button type="submit"
                                                                class="w-full text-left px-4 py-2.5 text-xs font-bold hover:bg-slate-50 transition-colors flex items-center gap-2"
                                                                style="color: {{ $stc['text'] ?? '#475569' }};">
                                                            <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $stc['dot'] ?? '#94a3b8' }};"></span>
                                                            {{ $st }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Edit --}}
                                        <button type="button" @click="openEdit({{ json_encode($order) }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('pos.external-orders.delete', $order) }}"
                                              onsubmit="return confirm('Supprimer la commande de {{ addslashes($order->client_name) }} ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Supprimer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <p class="text-sm font-bold text-slate-500">Aucune commande externe pour le moment</p>
                                        <p class="text-xs text-slate-400">Cliquez sur "Nouvelle Commande" pour en enregistrer une.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- ═══════════════════════════════════════ MODAL: CREATE / EDIT ORDER --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200"
             @click.away="showModal = false">

            {{-- Modal header --}}
            <div class="px-6 py-4 flex items-center justify-between"
                 style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);">
                <div>
                    <h3 class="text-base font-black text-white" x-text="isEditing ? 'Modifier la Commande' : 'Nouvelle Commande Externe'"></h3>
                    <p class="text-xs text-white/70 font-medium">Instagram / WhatsApp / Autre</p>
                </div>
                <button type="button" @click="showModal = false" class="text-white/70 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form :action="isEditing ? '/pos/external-orders/' + currentId : '{{ route('pos.external-orders.store') }}'"
                  method="POST" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto" id="ext-order-form">
                @csrf
                <span x-show="isEditing" x-cloak>
                    <input type="hidden" name="_method" value="PUT">
                </span>

                {{-- Client Name --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Nom du Client *</label>
                    <input type="text" name="client_name" x-model="form.client_name" required
                           placeholder="ex : Fatima Zahra Alaoui"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-400/20">
                </div>

                {{-- Phone + Platform row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Téléphone *</label>
                        <input type="text" name="client_phone" x-model="form.client_phone" required
                               placeholder="ex : 0612345678"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-400/20">
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Plateforme *</label>
                        <select name="platform_source" x-model="form.platform_source" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 outline-none focus:border-violet-400">
                            @foreach($platforms as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Address --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Adresse de Livraison *</label>
                    <textarea name="client_address" x-model="form.client_address" required rows="2"
                              placeholder="ex : Rue Hassan II, Appt 3, Casablanca"
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-400/20 resize-none"></textarea>
                </div>

                {{-- Amount + Status row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Montant Total (DH) *</label>
                        <input type="number" name="total_amount" x-model="form.total_amount" required min="0" step="0.01"
                               placeholder="0.00"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-400/20">
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Statut *</label>
                        <select name="status" x-model="form.status" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 outline-none focus:border-violet-400">
                            @foreach($statuses as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase text-slate-500 mb-1">Notes (Facultatif)</label>
                    <textarea name="notes" x-model="form.notes" rows="2"
                              placeholder="Détails supplémentaires, couleur, taille, remarques…"
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none focus:border-violet-400 resize-none"></textarea>
                </div>

                {{-- Action buttons --}}
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-sm font-black text-white shadow-md"
                            style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);"
                            x-text="isEditing ? 'Enregistrer Modifications' : 'Ajouter la Commande'">
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function externalOrdersManager() {
    return {
        showModal: false,
        isEditing: false,
        currentId: null,
        form: {
            client_name: '',
            client_phone: '',
            client_address: '',
            platform_source: 'Instagram',
            status: 'En attente',
            total_amount: '',
            notes: '',
        },

        openCreate() {
            this.isEditing = false;
            this.currentId = null;
            this.form = {
                client_name: '',
                client_phone: '',
                client_address: '',
                platform_source: 'Instagram',
                status: 'En attente',
                total_amount: '',
                notes: '',
            };
            this.showModal = true;
        },

        openEdit(order) {
            this.isEditing = true;
            this.currentId = order.id;
            this.form = {
                client_name: order.client_name,
                client_phone: order.client_phone,
                client_address: order.client_address,
                platform_source: order.platform_source,
                status: order.status,
                total_amount: order.total_amount,
                notes: order.notes || '',
            };
            // Also update the form action via DOM just in case
            const form = document.getElementById('ext-order-form');
            if (form) form.action = '/pos/external-orders/' + order.id;
            this.showModal = true;
        },
    };
}
</script>
@endpush
@endsection
