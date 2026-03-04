<x-settings-layout title="Input Mode" subtitle="Define barcode scanner input behavior" active="input-mode">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('pos.dashboard')],
        ['label' => 'Settings', 'href' => route('pos.settings')],
        ['label' => 'Input Mode']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6">
        @csrf
        <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 7h16M4 12h10M4 17h8" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Scanner Input Mode</h3>
                    <p class="text-sm text-slate-500">Customize how scans fill fields</p>
                </div>
            </div>
            <div class="mt-5">
                <label class="block text-sm font-medium text-slate-600 mb-2">Mode</label>
                <input type="text" name="scanner_mode" value="{{ $settings['scanner_mode'] }}" class="w-full rounded-xl bg-slate-100/70 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10" />
                <p class="mt-2 text-xs text-slate-400">Example: Scan direct vers champ barcode</p>
            </div>
        </section>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Save changes
            </button>
        </div>
    </form>
</x-settings-layout>
