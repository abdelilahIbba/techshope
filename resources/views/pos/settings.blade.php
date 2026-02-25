<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Modal Animations */
        .modal { display: none; }
        .modal.active { display: flex; animation: fadeIn 0.2s ease-out; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden antialiased">

<nav class="top-nav flex items-center gap-2 p-4 bg-white border-b border-slate-200 shadow-sm shrink-0">
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link notification-badge relative px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent" href="{{ route('pos.notifications') }}">
        Notifications stock
        @if($notificationCount > 0)
            <span class="badge-count absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-sm">{{ $notificationCount }}</span>
        @endif
    </a>
    <a class="nav-link active px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-blue-600 text-white shadow-md border-blue-600" href="{{ route('pos.settings') }}">Settings</a>

    @include('partials.license-badge')

    <form method="POST" action="{{ route('logout') }}" >
        @csrf
        <button type="submit" class="nav-link px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 cursor-pointer flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Déconnexion
        </button>
    </form>
</nav>

<div class="container flex-1 p-6 lg:p-8 overflow-y-auto w-full max-w-7xl mx-auto">
    
    @if(session('success'))
        <div class="alert alert-success bg-emerald-50 text-emerald-700 border border-emerald-200 p-4 rounded-xl mb-6 font-semibold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error bg-red-50 text-red-700 border border-red-200 p-4 rounded-xl mb-6 font-semibold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="page-header mb-8">
        <h1 class="page-title text-3xl font-extrabold text-slate-900 tracking-tight">Paramètres système</h1>
        <p class="page-subtitle text-slate-500 mt-1 font-medium">Configuration globale, utilisateurs et périphériques</p>
    </div>

    <form method="POST" action="{{ route('pos.settings.system.update') }}">
        @csrf
        <div class="settings-grid grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            
            <div class="settings-section bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <h3 class="section-title text-lg font-bold text-slate-900 flex items-center gap-3 mb-2">
                    <div class="section-icon bg-indigo-50 text-indigo-600 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    </div>
                    Configuration imprimante
                </h3>
                <p class="section-desc text-sm text-slate-500 mb-5">Paramètres pour l'impression de tickets de caisse</p>
                
                <div class="form-group mb-4">
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Imprimante Ventes (Valider &amp; Imprimer)</label>
                    <select name="printer_name_sale" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 p-3 outline-none transition-all">
                        @if(count($printers) > 0)
                            @foreach($printers as $printer)
                                <option value="{{ $printer }}" {{ ($settings['printer_name_sale'] ?? '') == $printer ? 'selected' : '' }}>{{ $printer }}</option>
                            @endforeach
                        @else
                            <option value="">Aucune imprimante détectée</option>
                        @endif
                        <option value="Manuel">--- Saisie manuelle ---</option>
                    </select>
                    <small class="block text-slate-400 text-xs mt-1.5 font-medium">
                        @if(count($printers) > 0)
                            ✅ {{ count($printers) }} imprimante(s) détectée(s)
                        @else
                            ⚠️ Aucune imprimante installée détectée
                        @endif
                    </small>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Imprimante Codes-barres</label>
                    <select name="printer_name_barcode" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 p-3 outline-none transition-all">
                        @if(count($printers) > 0)
                            @foreach($printers as $printer)
                                <option value="{{ $printer }}" {{ ($settings['printer_name_barcode'] ?? '') == $printer ? 'selected' : '' }}>{{ $printer }}</option>
                            @endforeach
                        @else
                            <option value="">Aucune imprimante détectée</option>
                        @endif
                        <option value="Manuel">--- Saisie manuelle ---</option>
                    </select>
                    <small class="block text-slate-400 text-xs mt-1.5 font-medium">Sélectionnez l'imprimante dédiée à l'impression d'étiquettes / codes-barres.</small>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Format ticket</label>
                        <select name="ticket_format" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 p-3 outline-none transition-all">
                            <option value="80mm thermal" {{ $settings['ticket_format'] == '80mm thermal' ? 'selected' : '' }}>80mm Thermal</option>
                            <option value="58mm thermal" {{ $settings['ticket_format'] == '58mm thermal' ? 'selected' : '' }}>58mm Thermal</option>
                            <option value="A4" {{ $settings['ticket_format'] == 'A4' ? 'selected' : '' }}>Format A4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Auto-print</label>
                        <select name="auto_print" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 p-3 outline-none transition-all">
                            <option value="true" {{ $settings['auto_print'] == 'true' ? 'selected' : '' }}>Activé</option>
                            <option value="false" {{ $settings['auto_print'] == 'false' ? 'selected' : '' }}>Désactivé</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="settings-section bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <h3 class="section-title text-lg font-bold text-slate-900 flex items-center gap-3 mb-2">
                    <div class="section-icon bg-cyan-50 text-cyan-600 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                    </div>
                    Scanner barcode
                </h3>
                <p class="section-desc text-sm text-slate-500 mb-5">Configuration de la douchette / scanner</p>
                
                <div class="form-group mb-4">
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Mode d'entrée</label>
                    <input type="text" name="scanner_mode" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-cyan-500 p-3 outline-none transition-all" value="{{ $settings['scanner_mode'] }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Beep de confirmation</label>
                    <select name="scanner_beep" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-cyan-500 p-3 outline-none transition-all">
                        <option value="true" {{ $settings['scanner_beep'] == 'true' ? 'selected' : '' }}>Activé (Sonore)</option>
                        <option value="false" {{ $settings['scanner_beep'] == 'false' ? 'selected' : '' }}>Désactivé (Silencieux)</option>
                    </select>
                </div>
            </div>

            <div class="settings-section bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <h3 class="section-title text-lg font-bold text-slate-900 flex items-center gap-3 mb-2">
                    <div class="section-icon bg-emerald-50 text-emerald-600 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    Paramètres de vente
                </h3>
                <p class="section-desc text-sm text-slate-500 mb-5">Options générales du système POS</p>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Devise</label>
                        <input type="text" name="currency" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 font-bold text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 p-3 outline-none transition-all" value="{{ $settings['currency'] }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Seuil stock faible</label>
                        <input type="number" name="low_stock_threshold" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 font-bold text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 p-3 outline-none transition-all" value="{{ $settings['low_stock_threshold'] }}" min="1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Client par défaut</label>
                    <input type="text" name="default_customer" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 p-3 outline-none transition-all" value="{{ $settings['default_customer'] }}">
                </div>
            </div>

            <div class="settings-section bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <h3 class="section-title text-lg font-bold text-slate-900 flex items-center gap-3 mb-2">
                    <div class="section-icon bg-amber-50 text-amber-600 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </div>
                    Notifications & Alertes
                </h3>
                <p class="section-desc text-sm text-slate-500 mb-5">Gestion des alertes système et rapports</p>
                
                <div class="form-group mb-4">
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Email rapport quotidien CA</label>
                    <input type="email" name="notification_email" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-3 outline-none transition-all" value="{{ $settings['notification_email'] }}" placeholder="admin@droguerie.com">
                </div>
                
                <div class="form-group">
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Fréquence vérification stock</label>
                    <select name="stock_check_frequency" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-3 outline-none transition-all">
                        <option value="Chaque heure" {{ $settings['stock_check_frequency'] == 'Chaque heure' ? 'selected' : '' }}>Chaque heure</option>
                        <option value="Quotidien" {{ $settings['stock_check_frequency'] == 'Quotidien' ? 'selected' : '' }}>Quotidien (Fin de journée)</option>
                        <option value="Manuel" {{ $settings['stock_check_frequency'] == 'Manuel' ? 'selected' : '' }}>Manuelle uniquement</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="text-center mb-10">
            <button type="submit" class="btn btn-primary w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-base px-10 py-4 shadow-md transition-all flex items-center justify-center gap-3 mx-auto transform active:scale-[0.98]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                Enregistrer tous les paramètres
            </button>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Access Rights</h3>
                <p class="text-sm text-slate-500 mt-1">Créer des rôles, configurer les permissions (pages + actions) et attribuer les accès.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <form method="POST" action="{{ route('pos.settings.roles.create') }}" class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                @csrf
                <h4 class="font-bold text-slate-900 mb-3">Créer un rôle</h4>
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nom du rôle</label>
                    <input type="text" name="name" required class="w-full bg-white border border-slate-300 text-sm rounded-lg p-2.5 outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" class="w-full bg-white border border-slate-300 text-sm rounded-lg p-2.5 outline-none">
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto border border-slate-200 rounded-lg p-3 bg-white">
                    @foreach($permissionsByPage as $page => $pagePermissions)
                        <div>
                            <p class="text-xs uppercase tracking-wide font-bold text-slate-500 mb-1">{{ $page }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($pagePermissions as $permission)
                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" name="permission_keys[]" value="{{ $permission->key }}" class="rounded border-slate-300">
                                        <span>{{ $permission->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="mt-4 w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg px-4 py-2.5">Créer rôle</button>
            </form>

            <div class="space-y-4 max-h-[520px] overflow-y-auto pr-1">
                @forelse($roles as $role)
                    @php $rolePermissionKeys = $role->permissions->pluck('key')->all(); @endphp
                    <div class="border border-slate-200 rounded-xl p-4">
                        @if($role->name === 'Admin')
                            {{-- Admin role is immutable --}}
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $role->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $role->description }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-3 py-1 text-xs font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-1a2 2 0 00-2-2H6a2 2 0 00-2 2v1a2 2 0 002 2zm10-12V7a4 4 0 00-8 0v4h8z" /></svg>
                                    Rôle protégé — non modifiable
                                </span>
                            </div>
                            <div class="space-y-1 max-h-40 overflow-y-auto border border-slate-100 rounded-lg p-3 bg-slate-50">
                                @foreach($role->permissions->groupBy('page') as $page => $perms)
                                    <p class="text-xs text-slate-500"><span class="font-semibold">{{ $page }}:</span> {{ $perms->pluck('label')->implode(', ') }}</p>
                                @endforeach
                            </div>
                        @else
                            <form method="POST" action="{{ route('pos.settings.roles.update', $role) }}">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nom</label>
                                        <input type="text" name="name" value="{{ $role->name }}" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg p-2.5 outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Description</label>
                                        <input type="text" name="description" value="{{ $role->description }}" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg p-2.5 outline-none">
                                    </div>
                                </div>

                                <div class="space-y-3 max-h-52 overflow-y-auto border border-slate-200 rounded-lg p-3 bg-slate-50">
                                    @foreach($permissionsByPage as $page => $pagePermissions)
                                        <div>
                                            <p class="text-xs uppercase tracking-wide font-bold text-slate-500 mb-1">{{ $page }}</p>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                                @foreach($pagePermissions as $permission)
                                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                                        <input type="checkbox" name="permission_keys[]" value="{{ $permission->key }}" {{ in_array($permission->key, $rolePermissionKeys, true) ? 'checked' : '' }} class="rounded border-slate-300">
                                                        <span>{{ $permission->label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex gap-2 mt-4">
                                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-4 py-2.5 text-sm">Enregistrer permissions</button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('pos.settings.roles.delete', $role) }}" class="mt-2" onsubmit="return confirm('Supprimer ce rôle ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-lg px-4 py-2.5 text-sm">Supprimer rôle</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Aucun rôle défini.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="users-table bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Gestion des utilisateurs</h3>
                <p class="text-sm text-slate-500 mt-1">Gérer les accès et les rôles du personnel</p>
            </div>
            <button class="bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl text-sm px-6 py-3 shadow-sm transition-all flex items-center gap-2" onclick="openAddUserModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                Nouvel utilisateur
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-bold">Nom</th>
                        <th class="px-6 py-4 font-bold">Rôle</th>
                        <th class="px-6 py-4 font-bold">Dernière connexion</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0 group">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $user->name }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('pos.settings.user.assign-role', $user) }}" class="flex items-center gap-2">
                                    @csrf
                                    <select name="role_name" class="bg-slate-50 border border-slate-300 text-slate-900 text-xs rounded-lg p-2 min-w-32">
                                        <option value="">Aucun rôle</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ $user->role === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-2.5 py-2">OK</button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-slate-400 font-medium">{{ $user->updated_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $editUserPayload = [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'role_name' => $user->role,
                                    ];
                                @endphp
                                <div class="flex gap-2 justify-end opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button class="flex items-center gap-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 font-semibold text-xs px-3 py-2 rounded-lg transition-all shadow-sm" onclick='editUser(@json($editUserPayload))'>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        Éditer
                                    </button>
                                    <form method="POST" action="{{ route('pos.settings.user.delete', $user) }}" class="inline" onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-1.5 text-red-500 bg-red-50 hover:bg-red-500 hover:text-white font-semibold text-xs px-3 py-2 rounded-lg transition-all shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500 font-medium">Aucun utilisateur trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="info-box mt-6 bg-blue-50/50 border border-blue-100 rounded-xl p-5 text-sm text-blue-800 flex gap-4">
            <div class="text-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <strong class="block mb-2 font-bold text-blue-900">Rôles disponibles dans le système :</strong>
                <ul class="space-y-1.5 font-medium">
                    @foreach($roles as $role)
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            <strong>{{ $role->name }}</strong> : {{ $role->description ?: 'Aucune description' }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <h3 class="text-xl font-bold text-slate-900 mb-1">Audit Log (RBAC)</h3>
        <p class="text-sm text-slate-500 mb-4">Historique des assignations de rôles et modifications de permissions.</p>
        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Date</th>
                        <th class="px-4 py-3 font-bold">Événement</th>
                        <th class="px-4 py-3 font-bold">Acteur</th>
                        <th class="px-4 py-3 font-bold">Cible</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-4 py-3">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $log->event }}</td>
                            <td class="px-4 py-3">{{ optional($log->actor)->name ?? 'Système' }}</td>
                            <td class="px-4 py-3">{{ $log->target_type ? class_basename($log->target_type) . ' #' . $log->target_id : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-slate-500">Aucun événement RBAC pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- LICENSE MANAGEMENT SECTION (Admin only)                   --}}
{{-- ======================================================= --}}
@if(auth()->user()->role === 'Admin')
<div class="fixed inset-0 z-0" style="display:none" id="licenseSection"></div>
@endif

<div class="container-license px-6 lg:px-8 pb-8 w-full max-w-7xl mx-auto">
    {{-- ── Admin: Generate License ─────────────────────────── --}}
    @if(auth()->user()->role === 'Admin')
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900">Gestion des Licences</h3>
                <p class="text-sm text-slate-500">Générez une clé de licence à envoyer au client pour activer le système.</p>
            </div>
        </div>

        {{-- Current license status --}}
        @if ($license && $license->expires_at && $license->expires_at->isFuture())
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-semibold">Licence active</span> —
                    Période: <strong>{{ $licensePeriods[$license->period] ?? $license->period }}</strong> —
                    Expire le <strong>{{ $license->expires_at->format('d/m/Y à H:i') }}</strong>
                    ({{ $license->expires_at->diffForHumans() }}).
                </div>
            </div>
        @elseif ($license)
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold">Dernière licence expirée</span>
                @if($license->expires_at) — le {{ $license->expires_at->format('d/m/Y') }} @endif.
                Générez une nouvelle clé.
            </div>
        @else
            <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-3 mb-5 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold">Aucune licence trouvée.</span> Générez une première clé pour activer le système chez le client.
            </div>
        @endif

        {{-- Generator form --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="license_period">Période de validité</label>
                <select id="license_period" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl p-3 outline-none focus:ring-2 focus:ring-purple-500">
                    @foreach($licensePeriods as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" onclick="generateLicenseKey()"
                id="generateLicenseBtn"
                class="h-12 bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm rounded-xl shadow-md shadow-purple-600/20 transition-all flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                </svg>
                <span id="generateLicenseBtnText">Générer la clé</span>
            </button>
        </div>

        {{-- Generated key output --}}
        <div id="generatedKeyBox" class="hidden mt-5">
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-sm font-semibold text-slate-700">Clé générée – à envoyer au client</label>
                <button type="button" onclick="copyLicenseKey()" id="copyBtn"
                    class="text-xs font-semibold text-purple-600 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copier
                </button>
            </div>
            <textarea id="generatedKeyText" readonly rows="4"
                class="w-full border border-purple-200 bg-purple-50 rounded-xl p-3 text-xs font-mono text-purple-900 resize-none focus:outline-none select-all"
                onclick="this.select()"></textarea>
            <p class="text-xs text-slate-500 mt-1.5">Copiez cette clé et envoyez-la au client. Il devra l'entrer sur la page d'activation.</p>
        </div>
    </div>
    @endif

    {{-- ── License activation link (always visible for non-admin) --}}
    @if(auth()->user()->role !== 'Admin')
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-slate-900">Licence système</h3>
                @if ($license && $license->expires_at && $license->expires_at->isFuture())
                    <p class="text-sm text-emerald-600 font-medium">Active — expire le {{ $license->expires_at->format('d/m/Y') }} ({{ $license->expires_at->diffForHumans() }}).</p>
                @else
                    <p class="text-sm text-red-600 font-medium">Aucune licence active.</p>
                @endif
            </div>
            <a href="{{ route('license.activate') }}"
                class="text-sm font-semibold bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-xl px-4 py-2.5 transition-colors">
                Activer / Renouveler
            </a>
        </div>
    </div>
    @endif
</div>

<div id="addUserModal" class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="modal-content bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <div class="modal-header flex justify-between items-center mb-5">
            <h3 class="modal-title text-xl font-bold text-slate-900">Ajouter un utilisateur</h3>
            <button class="modal-close text-slate-400 hover:text-slate-700 transition-colors" onclick="closeAddUserModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('pos.settings.user.add') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Nom complet</label>
                <input type="text" name="name" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none" required>
            </div>
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Code PIN (4 chiffres minimum)</label>
                <input type="password" name="password" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none tracking-[0.3em] font-bold" required minlength="4">
            </div>
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Rôle</label>
                <select name="role_name" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none font-semibold" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="btn btn-secondary px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeAddUserModal()">Annuler</button>
                <button type="submit" class="btn btn-primary px-5 py-2.5 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div id="editUserModal" class="modal fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="modal-content bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 p-6 relative">
        <div class="modal-header flex justify-between items-center mb-5">
            <h3 class="modal-title text-xl font-bold text-slate-900">Modifier l'utilisateur</h3>
            <button class="modal-close text-slate-400 hover:text-slate-700 transition-colors" onclick="closeEditUserModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form method="POST" id="editUserForm" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" id="edit_user_id">
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Nom complet</label>
                <input type="text" name="name" id="edit_name" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none" required>
            </div>
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Nouveau code PIN <span class="text-slate-400 text-xs font-normal">(Optionnel)</span></label>
                <input type="password" name="password" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none tracking-[0.3em] font-bold" minlength="4" placeholder="••••">
            </div>
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Rôle</label>
                <select name="role_name" id="edit_role_name" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none font-semibold" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-100">
                <button type="button" class="btn btn-secondary px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors" onclick="closeEditUserModal()">Annuler</button>
                <button type="submit" class="btn btn-primary px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('addUserModal').classList.add('active');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.remove('active');
}

function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_role_name').value = user.role_name || '';
    document.getElementById('editUserForm').action = '/pos/settings/user/edit/' + user.id;
    document.getElementById('editUserModal').classList.add('active');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.remove('active');
}

// Close modal on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// ── License generation ────────────────────────────────────────
async function generateLicenseKey() {
    const period  = document.getElementById('license_period')?.value;
    const btn     = document.getElementById('generateLicenseBtn');
    const btnText = document.getElementById('generateLicenseBtnText');
    const box     = document.getElementById('generatedKeyBox');
    const textarea = document.getElementById('generatedKeyText');

    if (!period) return;

    btnText.textContent = 'Génération…';
    btn.disabled = true;

    try {
        const resp = await fetch(@json(route('license.generate')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    || @json(csrf_token()),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ period }),
        });

        const data = await resp.json();

        if (resp.ok && data.key) {
            textarea.value = data.key;
            box.classList.remove('hidden');
            textarea.select();
        } else {
            alert('Erreur : ' + (data.message || 'Impossible de générer la licence.'));
        }
    } catch (e) {
        alert('Erreur réseau : ' + e.message);
    } finally {
        btnText.textContent = 'Générer la clé';
        btn.disabled = false;
    }
}

function copyLicenseKey() {
    const ta = document.getElementById('generatedKeyText');
    ta.select();
    document.execCommand('copy');

    const copyBtn = document.getElementById('copyBtn');
    copyBtn.textContent = '✓ Copié !';
    setTimeout(() => { copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>Copier'; }, 2000);
}
</script>
</body>
</html>