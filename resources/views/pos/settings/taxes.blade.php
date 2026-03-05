<x-settings-layout title="Taxes" subtitle="Configure VAT and receipt tax behavior" active="taxes">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('pos.dashboard')],
        ['label' => 'Settings', 'href' => route('pos.settings')],
        ['label' => 'Taxes']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6 space-y-6">
        @csrf
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Tax collection</h3>
                    <p class="text-sm text-slate-500 mt-1">Enable or disable tax calculation in POS.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="hidden" name="tax_enabled" value="false">
                    <input type="checkbox" name="tax_enabled" value="true" class="peer sr-only" {{ $settings['tax_enabled'] == 'true' ? 'checked' : '' }}>
                    <div class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-slate-900"></div>
                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                </label>
            </div>
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Tax label</label>
                    <input type="text" name="tax_label" value="{{ $settings['tax_label'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Tax rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ $settings['tax_rate'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Price display</h3>
                    <p class="text-sm text-slate-500 mt-1">Define whether prices include tax.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="hidden" name="tax_inclusive" value="false">
                    <input type="checkbox" name="tax_inclusive" value="true" class="peer sr-only" {{ $settings['tax_inclusive'] == 'true' ? 'checked' : '' }}>
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
