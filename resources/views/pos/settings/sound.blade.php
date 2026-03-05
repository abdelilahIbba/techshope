<x-settings-layout title="Sound Settings" subtitle="Tune the scanner feedback" active="sound">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('pos.dashboard')],
        ['label' => 'Settings', 'href' => route('pos.settings')],
        ['label' => 'Sound Settings']
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
                    <h3 class="text-lg font-semibold">Scanner Beep</h3>
                    <p class="text-sm text-slate-500">Control the audible scan confirmation</p>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Enable beep</p>
                    <p class="text-xs text-slate-400">Sound plays after a successful scan.</p>
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
                Save changes
            </button>
        </div>
    </form>
</x-settings-layout>
