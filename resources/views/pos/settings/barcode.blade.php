<x-settings-layout title="Imprimante codes-barres" subtitle="Sélectionnez l'imprimante utilisée pour les étiquettes de produits" active="barcode">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Tableau de bord', 'href' => route('pos.dashboard')],
        ['label' => 'Paramètres', 'href' => route('pos.settings')],
        ['label' => 'Imprimante codes-barres']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6">
        @csrf
        <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 7h2v10H3zM7 7h2v10H7zM11 7h2v10h-2zM15 7h2v10h-2zM19 7h2v10h-2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Imprimante codes-barres</h3>
                    <p class="text-sm text-slate-500">Utilisée pour les étiquettes et les étiquettes de rayon</p>
                </div>
            </div>
            <div class="mt-5">
                <label class="block text-sm font-medium text-slate-600 mb-2">Périphérique d'impression</label>
                <div class="relative">
                    <select name="printer_name_barcode" class="w-full appearance-none rounded-xl bg-slate-100/70 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                        @if(count($printers) > 0)
                            @foreach($printers as $printer)
                                <option value="{{ $printer }}" {{ ($settings['printer_name_barcode'] ?? '') == $printer ? 'selected' : '' }}>{{ $printer }}</option>
                            @endforeach
                        @else
                            <option value="">Aucune imprimante détectée</option>
                        @endif
                        <option value="Manuel">--- Saisie manuelle ---</option>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </div>
                <p class="mt-2 text-xs text-slate-400">Assurez-vous que l'appareil est allumé et installé.</p>
            </div>
        </section>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</x-settings-layout>
