<x-settings-layout title="Parametres" subtitle="Organisez la configuration de votre POS par module" active="index">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Tableau de bord', 'href' => route('pos.dashboard')],
        ['label' => 'Parametres']
    ]" />

    <section class="mt-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="h-5 w-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h2 class="text-lg font-semibold text-slate-900">Rapports & Historique</h2>
        </div>
        <p class="text-sm text-slate-500 mb-5">Accès rapide aux rapports et historiques de ventes.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            <x-settings-card title="Tableau de bord rapports" description="Vue globale et statistiques" :href="route('pos.reports')">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4" />
                        <path d="M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17" />
                        <circle cx="9" cy="20" r="1" />
                        <circle cx="17" cy="20" r="1" />
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card title="Ventes par article" description="Performance detaillee par produit" :href="route('pos.reports.sales-by-product')">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card title="Ventes par caissier" description="KPIs et panier moyen" :href="route('pos.reports.sales-by-cashier')">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 00-3-3.9" />
                        <path d="M16 3.1a4 4 0 010 7.8" />
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card title="Historique annulations" description="Ventes annulees et remboursements" :href="route('pos.reports.cancellations')">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v2m0 4h.01" />
                        <path d="M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card title="Depenses" description="Suivi des couts operationnels" :href="route('pos.expenses')">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 8c-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2" />
                        <path d="M12 8V7m0 10v1" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </x-slot:icon>
            </x-settings-card>
        </div>
    </section>

    <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <x-settings-card title="Paramètres de l'imprimante" description="Imprimante de reçus principale et paramètres par défaut" :href="route('pos.settings.printer')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2" />
                    <path d="M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2" />
                    <path d="M7 17v2a2 2 0 002 2h6a2 2 0 002-2v-2" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Imprimante codes-barres" description="Sélection de l'imprimante d'étiquettes et paramètres par défaut" :href="route('pos.settings.barcode')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 7h2v10H3zM7 7h2v10H7zM11 7h2v10h-2zM15 7h2v10h-2zM19 7h2v10h-2z" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Format du ticket" description="Taille du papier et mise en page du reçu" :href="route('pos.settings.ticket-format')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 7h8M8 12h8M8 17h5" />
                    <rect x="4" y="3" width="16" height="18" rx="2" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Impression automatique" description="Impression instantanée après l'encaissement" :href="route('pos.settings.auto-print')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9V4h12v5" />
                    <path d="M6 18h12v-5H6z" />
                    <path d="M6 14H4a2 2 0 01-2-2V9a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Mode de saisie" description="Mode scanner et comportement de saisie" :href="route('pos.settings.input-mode')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 7h16M4 12h10M4 17h8" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Paramètres sonores" description="Retour sonore et alertes" :href="route('pos.settings.sound')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 6v12" />
                    <path d="M8 8v8" />
                    <path d="M16 8v8" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Gestion de licence" description="Activation, statut et plan" :href="route('pos.settings.license')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 7v10" />
                    <path d="M7 12h10" />
                    <rect x="4" y="4" width="16" height="16" rx="3" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Stock" description="Paramètres d'inventaire par défaut" :href="route('pos.settings.stock')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 7l9-4 9 4" />
                    <path d="M3 7v10l9 4 9-4V7" />
                    <path d="M12 22V12" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Utilisateurs" description="Contrôle d'accès et rôles" :href="route('pos.settings.users')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 00-3-3.87" />
                    <path d="M16 3.13a4 4 0 010 7.75" />
                </svg>
            </x-slot:icon>
        </x-settings-card>

        <x-settings-card title="Taxes" description="Règles et taux de taxe" :href="route('pos.settings.taxes')">
            <x-slot:icon>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18" />
                    <path d="M3 12h18" />
                    <path d="M3 18h18" />
                </svg>
            </x-slot:icon>
        </x-settings-card>
    </div>

</x-settings-layout>
