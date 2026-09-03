@extends('layouts.pos')
@section('title', 'Gestion des Promotions & Soldes')

@push('styles')
<style>
[x-cloak] { display: none !important; }
.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}
</style>
@endpush

@section('content')
<div class="flex-1 flex flex-col h-full bg-slate-50/70 overflow-hidden" x-data="promoManager()" x-cloak>
    
    <!-- Top Bar Header -->
    <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between shrink-0 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl shadow-md flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #f43f5e 0%, #f59e0b 100%); color: #ffffff;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 leading-tight">Promotions & Soldes</h1>
                <p class="text-xs font-semibold text-slate-500">إدارة التخفيضات والعروض الخاصة بالمنتجات</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5">
            <!-- Option 2: Apply Promo to Existing Product -->
            <button type="button" @click="openApplyModal()"
                class="px-4 py-2.5 border border-rose-200 hover:bg-rose-50 font-bold text-xs md:text-sm rounded-xl shadow-xs active:scale-95 transition-all flex items-center gap-2"
                style="background-color: #ffffff; color: #be123c !important;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: #be123c;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span style="color: #be123c !important;">Appliquer Promo sur Produit Existant</span>
            </button>

            <!-- Option 1: Create New Product with Promo -->
            <button type="button" @click="openNewModal()"
                class="px-4 py-2.5 font-bold text-xs md:text-sm rounded-xl shadow-md active:scale-95 transition-all flex items-center gap-2"
                style="background: linear-gradient(135deg, #e11d48 0%, #d97706 100%); color: #ffffff !important;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span style="color: #ffffff !important;">Nouveau Produit en Promo</span>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 bg-emerald-500 text-white rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl space-y-1 shadow-xs">
                <div class="flex items-center gap-2 font-bold text-sm mb-1 text-rose-900">
                    <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Erreur de saisie / خطأ في البيانات :</span>
                </div>
                <ul class="list-disc list-inside text-xs font-semibold space-y-0.5 pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Stats Overview Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Active Promos -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3.5 bg-rose-50 text-rose-600 rounded-2xl border border-rose-100 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Produits en Promo</span>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $totalPromosCount }}</div>
                </div>
            </div>

            <!-- Average Discount % -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3.5 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Moyenne de Réduction</span>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">-{{ $avgDiscount }}%</div>
                </div>
            </div>

            <!-- Total Promo Stock -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Stock Total en Promo</span>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalPromoStock, 0) }} un.</div>
                </div>
            </div>
        </div>

        <!-- Active Promotions Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Liste des Produits en Promotion (Soldes)</h2>
                    <p class="text-xs font-medium text-slate-500">Les prix ci-dessous sont appliqués automatiquement lors de la vente et du calcul du Chiffre d'Affaires.</p>
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-72">
                    <input type="text" x-model="searchQuery" placeholder="Rechercher nom, code-barres..."
                        class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200/80 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-5">Produit</th>
                            <th class="py-3.5 px-4">Code-barres</th>
                            <th class="py-3.5 px-4 text-center">Stock</th>
                            <th class="py-3.5 px-4 text-right">Prix Initial</th>
                            <th class="py-3.5 px-4 text-right">Prix Solde (Promo)</th>
                            <th class="py-3.5 px-4 text-center">Réduction</th>
                            <th class="py-3.5 px-4 text-center">Statut</th>
                            <th class="py-3.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        @forelse($promoProducts as $product)
                            <tr class="hover:bg-rose-50/40 transition-colors"
                                x-show="matchesSearch('{{ strtolower($product->name) }}', '{{ strtolower($product->barcode) }}')">
                                
                                <!-- Product Name & Image -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 text-sm">{{ $product->name }}</div>
                                            <div class="text-[11px] text-slate-400 flex items-center gap-2">
                                                <span>{{ $product->category->name ?? 'Général' }}</span>
                                                @if($product->brand)
                                                    <span>• {{ $product->brand->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Barcode -->
                                <td class="py-4 px-4 font-mono font-bold text-slate-600">
                                    {{ $product->barcode }}
                                </td>

                                <!-- Stock -->
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                                        {{ number_format($product->stock_quantity, 0) }} {{ $product->unit }}
                                    </span>
                                </td>

                                <!-- Original Price -->
                                <td class="py-4 px-4 text-right">
                                    <span class="line-through text-slate-400 font-bold">
                                        {{ number_format($product->sale_price, 2) }} DH
                                    </span>
                                </td>

                                <!-- Promo Price -->
                                <td class="py-4 px-4 text-right">
                                    <span class="text-sm font-black text-rose-600 bg-rose-50 px-2.5 py-1 rounded-xl border border-rose-100">
                                        {{ number_format($product->promo_price, 2) }} DH
                                    </span>
                                </td>

                                <!-- Discount Badge -->
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                        -{{ $product->discount_percentage }}%
                                    </span>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-4 px-4 text-center">
                                    <form method="POST" action="{{ route('pos.promos.toggle', $product) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1 rounded-full text-[11px] font-extrabold transition-all cursor-pointer border {{ $product->is_promo ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}">
                                            {{ $product->is_promo ? '● Active' : '○ Inactive' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Promo Button -->
                                        <button type="button" @click="openEditModal({{ json_encode($product) }})"
                                            class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors" title="Modifier la promo">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>

                                        <!-- Remove Promo Button -->
                                        <form method="POST" action="{{ route('pos.promos.remove', $product) }}" onsubmit="return confirm('Voulez-vous vraiment retirer la promotion de ce produit ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="Retirer la promo">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 font-medium">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        <span class="text-sm font-bold text-slate-600">Aucun produit en promotion pour le moment</span>
                                        <span class="text-xs text-slate-400 mt-1">Utilisez les boutons en haut pour ajouter ou appliquer des promotions.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- ===== MODAL 1: CREATE NEW PRODUCT WITH PROMO (OPTION 1) ===== -->
    <div x-show="showNewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden" @click.away="showNewModal = false">
            <div class="px-6 py-4 bg-gradient-to-r from-rose-600 to-amber-600 text-white flex items-center justify-between">
                <h3 class="font-black text-lg">Créer un Nouveau Produit en Promo</h3>
                <button type="button" @click="showNewModal = false" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('pos.promos.store') }}" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                @csrf
                
                <!-- Product Name -->
                <div>
                    <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1">Nom du Produit *</label>
                    <input type="text" name="name" required placeholder="ex: TV Samsung 55 pouces"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-rose-500">
                </div>

                <!-- Category & Brand -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1">Catégorie *</label>
                        <select name="category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-rose-500">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1">Marque</label>
                        <select name="brand_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-rose-500">
                            <option value="">-- Aucune --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Barcode & Stock -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1">Code-barres (Auto si vide)</label>
                        <input type="text" name="barcode" placeholder="Ex: 611123456789"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-800 outline-none focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1">Quantité Stock *</label>
                        <input type="number" step="1" min="0" name="stock_quantity" value="10" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 outline-none focus:border-rose-500">
                    </div>
                </div>

                <!-- Prices Block -->
                <div class="p-4 bg-rose-50/50 rounded-2xl border border-rose-100 space-y-3">
                    <span class="text-xs font-black uppercase text-rose-800 tracking-wider block">Prix & Réduction</span>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Prix d'Achat *</label>
                            <input type="number" step="0.01" min="0" name="purchase_price" required placeholder="0.00"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Prix Normal (1er) *</label>
                            <input type="number" step="0.01" min="0" name="sale_price" x-model="newSalePrice" required placeholder="0.00"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800">
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold uppercase text-rose-700 mb-1">Prix Solde (2ème) *</label>
                            <input type="number" step="0.01" min="0" name="promo_price" x-model="newPromoPrice" required placeholder="0.00"
                                class="w-full bg-white border border-rose-300 rounded-xl px-3 py-2 text-xs font-black text-rose-700 ring-2 ring-rose-500/20">
                        </div>
                    </div>

                    <!-- Discount Preview -->
                    <template x-if="newSalePrice > 0 && newPromoPrice > 0 && Number(newPromoPrice) < Number(newSalePrice)">
                        <div class="text-xs font-extrabold text-emerald-700 bg-emerald-50 p-2 rounded-xl border border-emerald-200 text-center">
                            🔥 Réduction de <span x-text="Math.round(((newSalePrice - newPromoPrice)/newSalePrice)*100)"></span>% (Économie : <span x-text="(newSalePrice - newPromoPrice).toFixed(2)"></span> DH)
                        </div>
                    </template>
                </div>

                <!-- Submit Button -->
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showNewModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-md">Enregistrer Produit Promo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL 2: APPLY PROMO TO EXISTING PRODUCT (OPTION 2) ===== -->
    <div x-show="showApplyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden" @click.away="showApplyModal = false">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <h3 class="font-black text-lg">Appliquer Promo sur Produit Existant</h3>
                <button type="button" @click="showApplyModal = false" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('pos.promos.apply') }}" class="p-6 space-y-4">
                @csrf
                
                <!-- Product Selector -->
                <div>
                    <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1">Sélectionner le Produit *</label>
                    <select name="product_id" id="apply-product-select" x-model="selectedProductId" @change="onProductSelect()" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-rose-500">
                        <option value="">-- Choisir un produit dans la base de données --</option>
                        @foreach($allProducts as $prod)
                            <option value="{{ $prod->id }}" data-price="{{ $prod->sale_price }}">
                                {{ $prod->name }} ({{ number_format($prod->sale_price, 2) }} DH) — Barcode: {{ $prod->barcode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Input & Info Card -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-extrabold text-slate-500">Prix Initial :</span>
                        <span class="font-black text-slate-900 text-sm" id="apply-initial-price-text">
                            <span x-text="selectedProduct ? (selectedProduct.sale_price + ' DH') : '0.00 DH'"></span>
                        </span>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold uppercase text-rose-700 mb-1">Nouveau Prix de Promo (DH) *</label>
                        <input type="number" step="0.01" min="0" name="promo_price" id="apply-promo-price-input" x-model="applyPromoPrice" required placeholder="Saisir le prix de réduction"
                            class="w-full bg-white border border-rose-300 rounded-xl px-3.5 py-2.5 text-sm font-black text-rose-700 ring-2 ring-rose-500/20">
                    </div>

                    <template x-if="selectedProduct && applyPromoPrice > 0 && Number(applyPromoPrice) < Number(selectedProduct.sale_price)">
                        <div class="text-xs font-extrabold text-emerald-700 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200 text-center">
                            🔥 Réduction : -<span x-text="Math.round(((selectedProduct.sale_price - applyPromoPrice)/selectedProduct.sale_price)*100)"></span>% 
                            (Nouveau Prix Solde : <span x-text="parseFloat(applyPromoPrice).toFixed(2)"></span> DH)
                        </div>
                    </template>
                </div>

                <!-- Submit Button -->
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showApplyModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-md">Appliquer la Promotion</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL 3: EDIT PROMO ===== -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden" @click.away="showEditModal = false">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <h3 class="font-black text-lg">Modifier la Promotion</h3>
                <button type="button" @click="showEditModal = false" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="edit-promo-form" :action="'/pos/promos/' + (editProduct ? editProduct.id : 0) + '/update'" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div class="text-sm font-extrabold text-slate-900" id="edit-product-title" x-text="editProduct ? editProduct.name : ''"></div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1">Prix Initial (DH) *</label>
                        <input type="number" step="0.01" min="0" name="sale_price" id="edit-sale-price-input" x-model="editSalePrice" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-rose-700 mb-1">Prix Promo (DH) *</label>
                        <input type="number" step="0.01" min="0" name="promo_price" id="edit-promo-price-input" x-model="editPromoPrice" required
                            class="w-full bg-white border border-rose-300 rounded-xl px-3 py-2 text-xs font-black text-rose-700 ring-2 ring-rose-500/20">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-md">Enregistrer Modifications</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function promoManager() {
    return {
        searchQuery: '',
        showNewModal: false,
        showApplyModal: false,
        showEditModal: false,
        
        newSalePrice: null,
        newPromoPrice: null,
        
        selectedProductId: '',
        selectedProduct: null,
        applyPromoPrice: null,

        editProduct: null,
        editSalePrice: null,
        editPromoPrice: null,

        allProductsList: @json($allProducts),

        matchesSearch(name, barcode) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase();
            return name.includes(q) || barcode.includes(q);
        },

        openNewModal() {
            this.newSalePrice = null;
            this.newPromoPrice = null;
            this.showNewModal = true;
        },

        openApplyModal() {
            this.selectedProductId = '';
            this.selectedProduct = null;
            this.applyPromoPrice = null;
            this.showApplyModal = true;
        },

        onProductSelect() {
            const select = document.getElementById('apply-product-select');
            if (!select || !select.value) {
                this.selectedProduct = null;
                return;
            }
            const productId = select.value;
            this.selectedProductId = productId;
            this.selectedProduct = this.allProductsList.find(p => p.id == productId);
            if (this.selectedProduct) {
                this.applyPromoPrice = this.selectedProduct.promo_price || '';
                const initialText = document.getElementById('apply-initial-price-text');
                if (initialText) initialText.textContent = Number(this.selectedProduct.sale_price).toFixed(2) + ' DH';
                const promoInput = document.getElementById('apply-promo-price-input');
                if (promoInput && !promoInput.value && this.selectedProduct.promo_price) {
                    promoInput.value = this.selectedProduct.promo_price;
                }
            }
        },

        openEditModal(product) {
            this.editProduct = product;
            this.editSalePrice = product.sale_price;
            this.editPromoPrice = product.promo_price || '';
            this.showEditModal = true;

            const form = document.getElementById('edit-promo-form');
            if (form) {
                form.action = '/pos/promos/' + product.id + '/update';
            }
            const saleInput = document.getElementById('edit-sale-price-input');
            if (saleInput) saleInput.value = product.sale_price;
            const promoInput = document.getElementById('edit-promo-price-input');
            if (promoInput) promoInput.value = product.promo_price || '';
            const titleEl = document.getElementById('edit-product-title');
            if (titleEl) titleEl.textContent = product.name;
        }
    };
}
</script>
@endpush
@endsection
