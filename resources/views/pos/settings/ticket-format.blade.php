<x-settings-layout title="Format du ticket" subtitle="Choisissez la taille du papier et la mise en page du ticket" active="ticket-format">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Tableau de bord', 'href' => route('pos.dashboard')],
        ['label' => 'Paramètres', 'href' => route('pos.settings')],
        ['label' => 'Format du ticket']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6">
        @csrf
        <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="3" width="16" height="18" rx="2" />
                        <path d="M8 7h8M8 12h8M8 17h5" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Format du ticket</h3>
                    <p class="text-sm text-slate-500">Aligner le format avec votre matériel d'impression</p>
                </div>
            </div>
            <div class="mt-5">
                <label class="block text-sm font-medium text-slate-600 mb-2">Format</label>
                <div class="relative">
                    <select name="ticket_format" class="w-full appearance-none rounded-xl bg-slate-100/70 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                        <option value="80mm thermal" {{ $settings['ticket_format'] == '80mm thermal' ? 'selected' : '' }}>80mm Thermal</option>
                        <option value="58mm thermal" {{ $settings['ticket_format'] == '58mm thermal' ? 'selected' : '' }}>58mm Thermal</option>
                        <option value="A4" {{ $settings['ticket_format'] == 'A4' ? 'selected' : '' }}>Format A4</option>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </div>
                <p class="mt-2 text-xs text-slate-400">Les modifications s'appliquent à tous les nouveaux tickets.</p>
            </div>
        </section>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</x-settings-layout>
