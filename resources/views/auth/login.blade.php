<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS System</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --panel: #ffffff;
            --line: #d7e1ef;
            --ink: #1f2a44;
            --muted: #5b6b88;
            --primary: #3b82f6;
            --danger: #dc2626;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        body {
            font-family: Arial, sans-serif;
            color: var(--ink);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: grid;
            grid-template-rows: auto 1fr;
        }

        .login-header {
            text-align: center;
            padding: 15px 20px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }

        .logo-text {
            font-size: 26px;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .login-content {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 20px;
            padding: 20px;
            align-items: center;
            overflow: hidden;
        }

        .users-section {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            text-align: center;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        .user-card {
            background: var(--panel);
            border-radius: 12px;
            padding: 20px 15px;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
            border: 3px solid transparent;
            user-select: none;
        }

        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .user-card.selected {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .user-number {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .user-card.selected .user-number {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .user-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--ink);
        }

        .pin-section {
            background: var(--panel);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .pin-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 15px;
            text-align: center;
        }

        .pin-display {
            background: #f9fafb;
            border: 2px solid var(--line);
            border-radius: 10px;
            padding: 15px;
            font-size: 28px;
            text-align: center;
            letter-spacing: 12px;
            margin-bottom: 15px;
            min-height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .pin-keyboard {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .pin-key {
            background: #f9fafb;
            border: 2px solid var(--line);
            border-radius: 10px;
            padding: 18px;
            font-size: 22px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.1s;
            color: var(--ink);
            user-select: none;
        }

        .pin-key:hover {
            background: #e5e7eb;
            transform: scale(1.05);
        }

        .pin-key:active {
            transform: scale(0.95);
        }

        .pin-key.clear {
            background: #fee2e2;
            color: var(--danger);
            border-color: #fca5a5;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: #2563eb;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .error-message {
            background: #fee2e2;
            border: 2px solid #fca5a5;
            color: var(--danger);
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-header">
        <h1 class="logo-text">🏪 POS System - Droguerie Management</h1>
    </div>

    <div class="login-content">
        <div class="users-section">
            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <h2 class="section-title">Sélectionner utilisateur</h2>
            <div class="users-grid">
                @foreach($users as $index => $user)
                    <div class="user-card" onclick="selectUser({{ $user->id }}, this)">
                        <div class="user-number">{{ $index + 1 }}</div>
                        <div class="user-name">{{ $user->name }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            <input type="hidden" name="user_id" id="user_id" value="">
            <input type="hidden" name="password" id="password" value="">

            <div class="pin-section" id="pinSection">
                <h3 class="pin-title">Entrez votre mot de passe</h3>
                <div class="pin-display" id="pinDisplay">••••</div>
                <div class="pin-keyboard">
                    <button type="button" class="pin-key" onclick="addPin('1')">1</button>
                    <button type="button" class="pin-key" onclick="addPin('2')">2</button>
                    <button type="button" class="pin-key" onclick="addPin('3')">3</button>
                    <button type="button" class="pin-key" onclick="addPin('4')">4</button>
                    <button type="button" class="pin-key" onclick="addPin('5')">5</button>
                    <button type="button" class="pin-key" onclick="addPin('6')">6</button>
                    <button type="button" class="pin-key" onclick="addPin('7')">7</button>
                    <button type="button" class="pin-key" onclick="addPin('8')">8</button>
                    <button type="button" class="pin-key" onclick="addPin('9')">9</button>
                    <button type="button" class="pin-key clear" onclick="clearPin()">✕</button>
                    <button type="button" class="pin-key" onclick="addPin('0')">0</button>
                    <button type="button" class="pin-key" onclick="deletePin()">⌫</button>
                </div>
                <button type="submit" class="btn-submit" id="submitBtn" disabled>Se connecter</button>
            </div>
        </form>
    </div>

    <script>
        let currentPin = '';
        let selectedUserId = null;

        function selectUser(userId, element) {
            selectedUserId = userId;
            document.getElementById('user_id').value = userId;
            
            document.querySelectorAll('.user-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            element.classList.add('selected');
            clearPin();
        }

        function addPin(digit) {
            currentPin += digit;
            updatePinDisplay();
            document.getElementById('password').value = currentPin;
            document.getElementById('submitBtn').disabled = currentPin.length === 0;
        }

        function deletePin() {
            currentPin = currentPin.slice(0, -1);
            updatePinDisplay();
            document.getElementById('password').value = currentPin;
            document.getElementById('submitBtn').disabled = currentPin.length === 0;
        }

        function clearPin() {
            currentPin = '';
            updatePinDisplay();
            document.getElementById('password').value = '';
            document.getElementById('submitBtn').disabled = true;
        }

        function updatePinDisplay() {
            const display = document.getElementById('pinDisplay');
            if (currentPin.length === 0) {
                display.textContent = '••••';
                display.style.color = '#9ca3af';
            } else {
                display.textContent = '•'.repeat(currentPin.length);
                display.style.color = '#1f2a44';
            }
        }
    </script>
</body>
</html>
