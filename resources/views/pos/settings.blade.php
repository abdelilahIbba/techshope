<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --panel: #ffffff;
            --line: #d7e1ef;
            --ink: #1f2a44;
            --muted: #5b6b88;
            --primary: #3b82f6;
            --danger: #dc2626;
            --success: #10b981;
        }

        html, body { height: 100%; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .top-nav {
            display: flex;
            gap: 10px;
            padding: 10px 16px 6px;
            flex-wrap: nowrap;
            flex: 0 0 auto;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--ink);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
        }

        .nav-link.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .notification-badge {
            position: relative;
        }

        .notification-badge .badge-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc2626;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            border: 2px solid var(--bg);
        }

        .container {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            min-height: 0;
        }

        .page-header {
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .page-subtitle {
            color: var(--muted);
            font-size: 14px;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .settings-section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            background: #eff6ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .section-desc {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 16px;
        }

        .setting-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--line);
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .setting-value {
            color: var(--muted);
            font-size: 13px;
        }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            margin-top: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: var(--ink);
        }

        .users-table {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
        }

        th {
            font-weight: 700;
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-admin {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-cashier {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-commercial {
            background: #fef3c7;
            color: #92400e;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            margin-top: 16px;
            font-size: 13px;
            color: #1e40af;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: var(--danger);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: var(--ink);
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--panel);
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--muted);
            padding: 0;
            width: 32px;
            height: 32px;
        }

        .modal-close:hover {
            color: var(--ink);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .setting-item {
            padding: 0;
            border-bottom: none;
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <a class="nav-link" href="{{ route('pos.dashboard') }}">Dashboard</a>
    <a class="nav-link" href="{{ route('pos.index') }}">POS</a>
    <a class="nav-link" href="{{ route('pos.stock') }}">Gestion de stock</a>
    <a class="nav-link notification-badge" href="{{ route('pos.notifications') }}">
        Notifications stock
        @if($notificationCount > 0)
            <span class="badge-count">{{ $notificationCount }}</span>
        @endif
    </a>
    <a class="nav-link active" href="{{ route('pos.settings') }}">Settings</a>
    <form method="POST" action="{{ route('logout') }}" style="display: inline; margin-left: auto;">
        @csrf
        <button type="submit" class="nav-link" style="background: #fee2e2; border-color: #fca5a5; color: #991b1b; cursor: pointer;">Déconnexion</button>
    </form>
</nav>

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="page-header">
        <h1 class="page-title">Paramètres système</h1>
        <p class="page-subtitle">Configuration utilisateurs et périphériques</p>
    </div>

    <form method="POST" action="{{ route('pos.settings.system.update') }}">
        @csrf
        <div class="settings-grid">
            <div class="settings-section">
                <h3 class="section-title">
                    <div class="section-icon">🖨️</div>
                    Configuration imprimante
                </h3>
                <p class="section-desc">Paramètres pour l'impression de tickets</p>
                
                <div class="form-group">
                    <label class="form-label">Imprimante par défaut</label>
                    <select name="printer_name" class="form-select">
                        @if(count($printers) > 0)
                            @foreach($printers as $printer)
                                <option value="{{ $printer }}" {{ $settings['printer_name'] == $printer ? 'selected' : '' }}>{{ $printer }}</option>
                            @endforeach
                        @else
                            <option value="Aucune imprimante détectée">Aucune imprimante détectée</option>
                        @endif
                        <option value="Manuel">--- Saisie manuelle ---</option>
                    </select>
                    <small style="display: block; color: var(--muted); font-size: 12px; margin-top: 4px;">
                        @if(count($printers) > 0)
                            {{ count($printers) }} imprimante(s) détectée(s)
                        @else
                            Aucune imprimante installée détectée
                        @endif
                    </small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Format ticket</label>
                    <select name="ticket_format" class="form-select">
                        <option value="80mm thermal" {{ $settings['ticket_format'] == '80mm thermal' ? 'selected' : '' }}>80mm thermal</option>
                        <option value="58mm thermal" {{ $settings['ticket_format'] == '58mm thermal' ? 'selected' : '' }}>58mm thermal</option>
                        <option value="A4" {{ $settings['ticket_format'] == 'A4' ? 'selected' : '' }}>A4</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Auto-print après vente</label>
                    <select name="auto_print" class="form-select">
                        <option value="true" {{ $settings['auto_print'] == 'true' ? 'selected' : '' }}>Activé</option>
                        <option value="false" {{ $settings['auto_print'] == 'false' ? 'selected' : '' }}>Désactivé</option>
                    </select>
                </div>
            </div>

            <div class="settings-section">
                <h3 class="section-title">
                    <div class="section-icon">📷</div>
                    Scanner barcode
                </h3>
                <p class="section-desc">Configuration scanner de codes-barres</p>
                
                <div class="form-group">
                    <label class="form-label">Mode d'entrée</label>
                    <input type="text" name="scanner_mode" class="form-input" value="{{ $settings['scanner_mode'] }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Beep confirmation</label>
                    <select name="scanner_beep" class="form-select">
                        <option value="true" {{ $settings['scanner_beep'] == 'true' ? 'selected' : '' }}>Activé</option>
                        <option value="false" {{ $settings['scanner_beep'] == 'false' ? 'selected' : '' }}>Désactivé</option>
                    </select>
                </div>
            </div>

            <div class="settings-section">
                <h3 class="section-title">
                    <div class="section-icon">💰</div>
                    Paramètres de vente
                </h3>
                <p class="section-desc">Options générales du système POS</p>
                
                <div class="form-group">
                    <label class="form-label">Devise</label>
                    <input type="text" name="currency" class="form-input" value="{{ $settings['currency'] }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Seuil stock faible</label>
                    <input type="number" name="low_stock_threshold" class="form-input" value="{{ $settings['low_stock_threshold'] }}" min="1">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Client par défaut</label>
                    <input type="text" name="default_customer" class="form-input" value="{{ $settings['default_customer'] }}">
                </div>
            </div>

            <div class="settings-section">
                <h3 class="section-title">
                    <div class="section-icon">🔔</div>
                    Notifications
                </h3>
                <p class="section-desc">Gestion des alertes système</p>
                
                <div class="form-group">
                    <label class="form-label">Email rapport quotidien CA</label>
                    <input type="email" name="notification_email" class="form-input" value="{{ $settings['notification_email'] }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Fréquence vérification stock</label>
                    <select name="stock_check_frequency" class="form-select">
                        <option value="Chaque heure" {{ $settings['stock_check_frequency'] == 'Chaque heure' ? 'selected' : '' }}>Chaque heure</option>
                        <option value="Quotidien" {{ $settings['stock_check_frequency'] == 'Quotidien' ? 'selected' : '' }}>Quotidien</option>
                        <option value="Manuel" {{ $settings['stock_check_frequency'] == 'Manuel' ? 'selected' : '' }}>Manuel</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 30px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 16px;">
                💾 Enregistrer tous les paramètres
            </button>
        </div>
    </form>

    <div class="users-table">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Gestion des utilisateurs</h3>
            <button class="btn btn-primary" onclick="openAddUserModal()">+ Nouvel utilisateur</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Dernière connexion</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-admin">Admin</span>
                        </td>
                        <td>{{ $user->updated_at->diffForHumans() }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-secondary btn-sm" onclick='editUser({{ json_encode($user) }})'>✏️ Éditer</button>
                                <form method="POST" action="{{ route('pos.settings.user.delete', $user) }}" style="display: inline;" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--muted);">Aucun utilisateur</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="info-box">
            <strong>Rôles disponibles:</strong><br>
            • <strong>Admin</strong> - Accès complet au système<br>
            • <strong>Cashier</strong> - POS et ventes uniquement<br>
            • <strong>Commercial</strong> - POS, ventes et rapport CA personnel
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter un utilisateur</h3>
            <button class="modal-close" onclick="closeAddUserModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.settings.user.add') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Code PIN (4 chiffres minimum)</label>
                <input type="password" name="password" class="form-input" required minlength="4">
            </div>
            <div class="form-group">
                <label class="form-label">Rôle</label>
                <select name="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                    <option value="commercial">Commercial</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier l'utilisateur</h3>
            <button class="modal-close" onclick="closeEditUserModal()">&times;</button>
        </div>
        <form method="POST" id="editUserForm">
            @csrf
            <input type="hidden" id="edit_user_id">
            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" id="edit_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nouveau code PIN (laisser vide pour ne pas changer)</label>
                <input type="password" name="password" class="form-input" minlength="4">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
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
