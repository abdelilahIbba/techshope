<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation Licence – Droguerie POS</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="flex items-center gap-3 justify-center mb-8">
            <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <span class="text-2xl font-extrabold tracking-tight text-white">Droguerie <span class="text-blue-400">POS</span></span>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl">

            {{-- Header --}}
            <div class="border-b border-slate-100 p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900">Activation de Licence</h1>
                        <p class="text-sm text-slate-500">Entrez la clé fournie par votre administrateur.</p>
                    </div>
                </div>
            </div>

            <div class="p-6">

                {{-- Current license status --}}
                @if ($current)
                    @if ($current->expires_at && $current->expires_at->isFuture())
                        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-semibold">Licence active</span> —
                                expire le <strong>{{ $current->expires_at->translatedFormat('d M Y') }}</strong>
                                ({{ $current->expires_at->diffForHumans() }}).
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-semibold">Licence expirée</span>
                                @if($current->expires_at) — le {{ $current->expires_at->translatedFormat('d M Y') }}@endif.
                                Veuillez activer une nouvelle clé.
                            </div>
                        </div>
                    @endif
                @else
                    <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-3 mb-5 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div><span class="font-semibold">Aucune licence active.</span> Le système est verrouillé.</div>
                    </div>
                @endif

                {{-- Session success --}}
                @if (session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Session error --}}
                @if (session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Activation form --}}
                <form method="POST" action="{{ route('license.store') }}">
                    @csrf

                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="license_key">
                        Clé de licence
                    </label>
                    <textarea
                        id="license_key"
                        name="license_key"
                        rows="4"
                        class="w-full border rounded-xl p-3 text-sm font-mono text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('license_key') border-red-500 bg-red-50 @else border-slate-200 @enderror"
                        placeholder="DRG-XXXX…"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                    >{{ old('license_key') }}</textarea>

                    @error('license_key')
                        <p class="text-red-600 text-xs font-medium mt-1.5">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                        class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm h-11 rounded-xl shadow-lg shadow-blue-600/20 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Activer la licence
                    </button>
                </form>

            </div>

            {{-- Footer --}}
            <div class="border-t border-slate-100 px-6 py-4 flex items-center justify-between">
                <span class="text-xs text-slate-400">Contactez votre Admin pour obtenir une clé.</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-slate-500 hover:text-red-600 font-medium transition-colors">
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
