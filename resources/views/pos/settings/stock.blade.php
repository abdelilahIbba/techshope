<x-settings-layout title="Paramètres du stock" subtitle="Valeurs par défaut de l'inventaire et seuils du système" active="stock">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Tableau de bord', 'href' => route('pos.dashboard')],
        ['label' => 'Paramètres', 'href' => route('pos.settings')],
        ['label' => 'Stock']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="mt-6 space-y-6">
        @csrf
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <h3 class="text-lg font-semibold text-slate-900">Alertes d'inventaire</h3>
            <p class="text-sm text-slate-500 mt-1">Contrôler les avertissements de stock bas et la fréquence de vérification.</p>
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Seuil de stock bas</label>
                    <input type="number" name="low_stock_threshold" min="1" value="{{ $settings['low_stock_threshold'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Fréquence de vérification du stock</label>
                    <div class="relative">
                        <select name="stock_check_frequency" class="w-full appearance-none rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option value="Chaque heure" {{ $settings['stock_check_frequency'] == 'Chaque heure' ? 'selected' : '' }}>Toutes les heures</option>
                            <option value="Quotidien" {{ $settings['stock_check_frequency'] == 'Quotidien' ? 'selected' : '' }}>Quotidien</option>
                            <option value="Manuel" {{ $settings['stock_check_frequency'] == 'Manuel' ? 'selected' : '' }}>Manuel uniquement</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-2">Email de notification</label>
                    <input type="email" name="notification_email" value="{{ $settings['notification_email'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <h3 class="text-lg font-semibold text-slate-900">Valeurs par défaut du POS</h3>
            <p class="text-sm text-slate-500 mt-1">Aligner la devise et les paramètres client par défaut.</p>
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Devise</label>
                    <input type="text" name="currency" value="{{ $settings['currency'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Client par défaut</label>
                    <input type="text" name="default_customer" value="{{ $settings['default_customer'] }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Libellé du paiement en espèces</label>
                    <input type="text" name="cash_label" value="{{ $settings['cash_label'] ?? 'DHs' }}" class="w-full rounded-xl bg-slate-100/80 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>
        </section>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</x-settings-layout>
