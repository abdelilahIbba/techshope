<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications stock - POS</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden antialiased">

@include('partials.pos-nav')

<div class="container flex-1 p-6 lg:p-8 overflow-y-auto w-full max-w-4xl mx-auto">
    
    <div class="page-header mb-8 text-center sm:text-left">
        <h1 class="page-title text-3xl font-extrabold text-slate-900 tracking-tight">Centre de Notifications</h1>
        <p class="page-subtitle text-slate-500 mt-1 font-medium">Alertes pour les produits nécessitant un réapprovisionnement</p>
    </div>

    @if($lowStockProducts->where('stock_quantity', '<=', 5)->count() > 0 || $lowStockProducts->where('stock_quantity', '>', 5)->count() > 0)
        
        <div class="alert-summary bg-white border border-slate-200 rounded-2xl p-6 mb-8 shadow-sm flex items-center gap-5 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-red-500 rounded-l-2xl"></div>
            <div class="alert-icon flex-shrink-0 w-14 h-14 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <div class="alert-content flex-1">
                <h3 class="alert-title text-xl font-extrabold text-slate-900 mb-1">
                    Attention : {{ $lowStockProducts->count() }} produit(s) à surveiller
                </h3>
                <div class="flex flex-wrap gap-2 mt-2">
                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold tracking-wide">
                        {{ $lowStockProducts->where('stock_quantity', '<=', 5)->count() }} Critique(s)
                    </span>
                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold tracking-wide">
                        {{ $lowStockProducts->where('stock_quantity', '>', 5)->where('stock_quantity', '<=', 10)->count() }} En alerte
                    </span>
                </div>
            </div>
        </div>

        <div class="notification-list flex flex-col gap-4">
            @foreach($lowStockProducts as $product)
                @php
                    $isCritical = $product->stock_quantity <= 5;
                    $borderClass = $isCritical ? 'border-red-200' : 'border-amber-200';
                    $bgClass = $isCritical ? 'bg-red-50' : 'bg-amber-50';
                    $iconColor = $isCritical ? 'text-red-600' : 'text-amber-600';
                    $badgeClass = $isCritical ? 'bg-red-500 text-white shadow-[0_0_10px_rgba(239,68,68,0.4)]' : 'bg-amber-500 text-white shadow-[0_0_10px_rgba(245,158,11,0.4)]';
                    $statusText = $isCritical ? 'Rupture imminente' : 'Stock bas';
                @endphp

                <div class="notification-card bg-white border {{ $borderClass }} rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-center gap-5 relative overflow-hidden group">
                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isCritical ? 'bg-red-500' : 'bg-amber-500' }}"></div>
                    
                    <div class="notification-icon w-12 h-12 flex-shrink-0 {{ $bgClass }} {{ $iconColor }} rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    
                    <div class="notification-body flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="notification-title text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $product->name }}</h4>
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $badgeClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                        
                        <div class="notification-meta flex flex-wrap gap-4 text-xs font-medium text-slate-500 mt-2">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                {{ $product->category->name ?? 'N/A' }}
                            </span>
                            <span class="flex items-center gap-1 font-mono tracking-wide">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                {{ $product->barcode }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="notification-stock text-right">
                        <div class="text-xs text-slate-400 font-bold uppercase mb-1">Reste en stock</div>
                        <div class="text-3xl font-extrabold {{ $iconColor }}">
                            {{ (float)$product->stock_quantity }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <div class="empty-state bg-white border border-slate-200 rounded-3xl p-12 text-center shadow-sm flex flex-col items-center justify-center min-h-[400px]">
            <div class="empty-icon w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <h3 class="empty-title text-2xl font-extrabold text-slate-900 mb-2">Tout est en ordre !</h3>
            <p class="empty-desc text-slate-500 font-medium max-w-sm mx-auto">Aucun produit ne nécessite de réapprovisionnement pour le moment. Votre stock est en parfaite santé.</p>
            
            <a href="{{ route('pos.stock') }}" class="mt-8 inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-6 py-3 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Retourner à la gestion de stock
            </a>
        </div>
    @endif
</div>
</body>
</html>