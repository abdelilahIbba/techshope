<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    
    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
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
                    <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Imprimante par défaut</label>
                    <select name="printer_name" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 p-3 outline-none transition-all">
                        @if(count($printers) > 0)
                            @foreach($printers as $printer)
                                <option value="{{ $printer }}" {{ $settings['printer_name'] == $printer ? 'selected' : '' }}>{{ $printer }}</option>
                            @endforeach
                        @else
                            <option value="Aucune imprimante détectée">Aucune imprimante détectée</option>
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
                        <th class="px-6 py-4 font-bold">Email</th>
                        <th class="px-6 py-4 font-bold">Rôle</th>
                        <th class="px-6 py-4 font-bold">Dernière connexion</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors last:border-0 group">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="badge badge-admin bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">
                                    {{ $user->role ?? 'Admin' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-400 font-medium">{{ $user->updated_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2 justify-end opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button class="flex items-center gap-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 font-semibold text-xs px-3 py-2 rounded-lg transition-all shadow-sm" onclick='editUser({{ json_encode($user) }})'>
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
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 font-medium">Aucun utilisateur trouvé</td>
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
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> <strong>Admin</strong> : Accès complet au système et aux paramètres.</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> <strong>Cashier</strong> : Accès limité au POS et aux ventes uniquement.</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> <strong>Commercial</strong> : POS, ventes et accès à ses propres rapports de CA.</li>
                </ul>
            </div>
        </div>
    </div>
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
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none" required>
            </div>
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Code PIN (4 chiffres minimum)</label>
                <input type="password" name="password" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none tracking-[0.3em] font-bold" required minlength="4">
            </div>
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Rôle</label>
                <select name="role" class="form-select w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none font-semibold" required>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                    <option value="commercial">Commercial</option>
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
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" id="edit_email" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none" required>
            </div>
            <div>
                <label class="form-label block text-sm font-semibold text-slate-700 mb-1.5">Nouveau code PIN <span class="text-slate-400 text-xs font-normal">(Optionnel)</span></label>
                <input type="password" name="password" class="form-input w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 p-3 outline-none tracking-[0.3em] font-bold" minlength="4" placeholder="••••">
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
    document.getElementById('edit_email').value = user.email;
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
</script>
</body>
</html>