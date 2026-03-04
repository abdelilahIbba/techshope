<x-settings-layout title="Stock Settings" subtitle="Inventory defaults and system thresholds" active="stock">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('pos.dashboard')],
        ['label' => 'Settings', 'href' => route('pos.settings')],
        ['label' => 'Stock']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6 space-y-6">
        @csrf
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <h3 class="text-lg font-semibold text-slate-900">Inventory alerts</h3>
            <p class="text-sm text-slate-500 mt-1">Control low-stock warnings and check frequency.</p>
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Low stock threshold</label>
                    <input type="number" name="low_stock_threshold" min="1" value="{{ $settings['low_stock_threshold'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Stock check frequency</label>
                    <div class="relative">
                        <select name="stock_check_frequency" class="w-full appearance-none rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option value="Chaque heure" {{ $settings['stock_check_frequency'] == 'Chaque heure' ? 'selected' : '' }}>Every hour</option>
                            <option value="Quotidien" {{ $settings['stock_check_frequency'] == 'Quotidien' ? 'selected' : '' }}>Daily</option>
                            <option value="Manuel" {{ $settings['stock_check_frequency'] == 'Manuel' ? 'selected' : '' }}>Manual only</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-2">Notification email</label>
                    <input type="email" name="notification_email" value="{{ $settings['notification_email'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <h3 class="text-lg font-semibold text-slate-900">POS defaults</h3>
            <p class="text-sm text-slate-500 mt-1">Align currency and default customer settings.</p>
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Currency</label>
                    <input type="text" name="currency" value="{{ $settings['currency'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Default customer</label>
                    <input type="text" name="default_customer" value="{{ $settings['default_customer'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Cash payment label</label>
                    <input type="text" name="cash_label" value="{{ $settings['cash_label'] ?? 'DHs' }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>
        </section>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Save changes
            </button>
        </div>
    </form>
</x-settings-layout>
