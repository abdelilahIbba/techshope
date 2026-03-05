<x-settings-layout title="Printer Settings" subtitle="Configure receipt printers and ticket output" active="printer">
    <x-settings-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('pos.dashboard')],
        ['label' => 'Settings', 'href' => route('pos.settings')],
        ['label' => 'Printer Settings']
    ]" />

    <form method="POST" action="{{ route('pos.settings.system.update') }}" class="space-y-6 mt-6">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2" />
                            <path d="M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2" />
                            <path d="M7 17v2a2 2 0 002 2h6a2 2 0 002-2v-2" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Primary Receipt Printer</h3>
                        <p class="text-sm text-slate-500">Used for checkout receipts</p>
                    </div>
                </div>
                <div class="mt-5">
                    <label class="block text-sm font-medium text-slate-600 mb-2">Printer device</label>
                    <div class="relative">
                        <select name="printer_name_sale" class="w-full appearance-none rounded-xl bg-slate-100/70 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                            @if(count($printers) > 0)
                                @foreach($printers as $printer)
                                    <option value="{{ $printer }}" {{ ($settings['printer_name_sale'] ?? '') == $printer ? 'selected' : '' }}>{{ $printer }}</option>
                                @endforeach
                            @else
                                <option value="">Aucune imprimante detectee</option>
                            @endif
                            <option value="Manuel">--- Saisie manuelle ---</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Auto-detected devices from your system.</p>
                </div>
            </section>

            <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="3" width="16" height="18" rx="2" />
                            <path d="M8 7h8M8 12h8M8 17h5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Ticket Format</h3>
                        <p class="text-sm text-slate-500">Receipt paper size and layout</p>
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
                </div>
            </section>

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
                        <h3 class="text-lg font-semibold">Auto Print</h3>
                        <p class="text-sm text-slate-500">Print instantly after checkout</p>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Enable automatic printing</p>
                        <p class="text-xs text-slate-400">Reduces manual taps at the register.</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="hidden" name="auto_print" value="false">
                        <input type="checkbox" name="auto_print" value="true" class="peer sr-only" {{ $settings['auto_print'] == 'true' ? 'checked' : '' }}>
                        <div class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-slate-900"></div>
                        <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                    </label>
                </div>
            </section>

            <section class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 7h2v10H3zM7 7h2v10H7zM11 7h2v10h-2zM15 7h2v10h-2zM19 7h2v10h-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Barcode Printer</h3>
                        <p class="text-sm text-slate-500">Label printer for stock tags</p>
                    </div>
                </div>
                <div class="mt-5">
                    <label class="block text-sm font-medium text-slate-600 mb-2">Barcode device</label>
                    <div class="relative">
                        <select name="printer_name_barcode" class="w-full appearance-none rounded-xl bg-slate-100/70 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                            @if(count($printers) > 0)
                                @foreach($printers as $printer)
                                    <option value="{{ $printer }}" {{ ($settings['printer_name_barcode'] ?? '') == $printer ? 'selected' : '' }}>{{ $printer }}</option>
                                @endforeach
                            @else
                                <option value="">Aucune imprimante detectee</option>
                            @endif
                            <option value="Manuel">--- Saisie manuelle ---</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                </div>
            </section>
        </div>

        <div class="fixed bottom-6 right-6 z-40">
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                Save changes
            </button>
        </div>
    </form>
</x-settings-layout>
