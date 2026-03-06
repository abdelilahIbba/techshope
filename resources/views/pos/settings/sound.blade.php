<x-settings-layout title="Paramètres sonores" subtitle="Régler le retour du scanner" active="sound">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Tableau de bord', 'href' => route('pos.dashboard')],
        ['label' => 'Paramètres', 'href' => route('pos.settings')],
        ['label' => 'Paramètres sonores']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6">
        @csrf
        <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 6v12" />
                        <path d="M8 8v8" />
                        <path d="M16 8v8" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Bip du scanner</h3>
                    <p class="text-sm text-slate-500">Contrôler la confirmation sonore du scan</p>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Activer le bip</p>
                    <p class="text-xs text-slate-400">Un son est émis après un scan réussi.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="hidden" name="scanner_beep" value="false">
                    <input type="checkbox" name="scanner_beep" value="true" class="peer sr-only" {{ $settings['scanner_beep'] == 'true' ? 'checked' : '' }}>
                    <div class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-slate-900"></div>
                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                </label>
            </div>
        </section>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</x-settings-layout>
