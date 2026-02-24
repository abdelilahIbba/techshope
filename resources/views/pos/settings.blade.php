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
    <div class="page-header">
        <h1 class="page-title">Paramètres système</h1>
        <p class="page-subtitle">Configuration utilisateurs et périphériques</p>
    </div>

    <div class="settings-grid">
        <div class="settings-section">
            <h3 class="section-title">
                <div class="section-icon">🖨️</div>
                Configuration imprimante
            </h3>
            <p class="section-desc">Paramètres pour l'impression de tickets</p>
            <div class="setting-item">
                <div class="setting-label">Imprimante par défaut</div>
                <div class="setting-value">Ticket POS Printer (USB)</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Format ticket</div>
                <div class="setting-value">80mm thermal</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Auto-print après vente</div>
                <div class="setting-value">Activé</div>
            </div>
            <button class="btn btn-secondary">Configurer</button>
        </div>

        <div class="settings-section">
            <h3 class="section-title">
                <div class="section-icon">📷</div>
                Scanner barcode
            </h3>
            <p class="section-desc">Configuration scanner de codes-barres</p>
            <div class="setting-item">
                <div class="setting-label">Scanner connecté</div>
                <div class="setting-value">Détecté automatiquement</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Mode d'entrée</div>
                <div class="setting-value">Scan direct vers champ barcode</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Beep confirmation</div>
                <div class="setting-value">Activé</div>
            </div>
            <button class="btn btn-secondary">Tester scanner</button>
        </div>

        <div class="settings-section">
            <h3 class="section-title">
                <div class="section-icon">💰</div>
                Paramètres de vente
            </h3>
            <p class="section-desc">Options générales du système POS</p>
            <div class="setting-item">
                <div class="setting-label">Devise</div>
                <div class="setting-value">DZD (Dinar algérien)</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Seuil stock faible</div>
                <div class="setting-value">10 unités</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Client par défaut</div>
                <div class="setting-value">Zbon عام (Cash)</div>
            </div>
            <button class="btn btn-secondary">Modifier</button>
        </div>

        <div class="settings-section">
            <h3 class="section-title">
                <div class="section-icon">🔔</div>
                Notifications
            </h3>
            <p class="section-desc">Gestion des alertes système</p>
            <div class="setting-item">
                <div class="setting-label">Alerte stock bas</div>
                <div class="setting-value">Activé</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Rapport quotidien CA</div>
                <div class="setting-value">Envoyé à admin@droguerie.com</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Fréquence vérification stock</div>
                <div class="setting-value">Chaque heure</div>
            </div>
            <button class="btn btn-secondary">Gérer</button>
        </div>
    </div>

    <div class="users-table">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Gestion des utilisateurs</h3>
            <button class="btn btn-primary">+ Nouvel utilisateur</button>
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
                            <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">Éditer</button>
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
</body>
</html>
