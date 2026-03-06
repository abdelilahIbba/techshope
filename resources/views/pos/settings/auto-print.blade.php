<x-settings-layout title="Impression automatique" subtitle="Contrôler l'impression automatique après l'encaissement" active="auto-print">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Tableau de bord', 'href' => route('pos.dashboard')],
        ['label' => 'Paramètres', 'href' => route('pos.settings')],
        ['label' => 'Impression automatique']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6">
        @csrf
        <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9V4h12v5" />
                        <path d="M6 18h12v-5H6z" />
                        <path d="M6 14H4a2 2 0 01-2-2V9a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Automatic Printing</h3>
                    <p class="text-sm text-slate-500">Reduce manual actions at the register</p>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Enable auto print</p>
                    <p class="text-xs text-slate-400">Receipts print immediately after payment.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="hidden" name="auto_print" value="false">
                    <input type="checkbox" name="auto_print" value="true" class="peer sr-only" {{ $settings['auto_print'] == 'true' ? 'checked' : '' }}>
                    <div class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-slate-900"></div>
                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                </label>
            </div>
        </section>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Save changes
            </button>
        </div>
    </form>
</x-settings-layout>
