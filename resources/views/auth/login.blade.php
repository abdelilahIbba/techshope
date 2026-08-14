<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Droguerie POS</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* تأثيرات البطاقة عند الاختيار */
        .user-card {
            transition: all 0.2s ease-in-out;
        }

        .user-card.selected {
            border-color: #3b82f6;
            /* Blue-500 */
            background-color: rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .user-card.selected .user-avatar {
            background-color: #3b82f6;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        /* تأثير أزرار الأرقام */
        .pin-key {
            transition: transform 0.1s, background-color 0.1s;
        }

        .pin-key:active {
            transform: scale(0.92);
            background-color: #e2e8f0;
        }

        /* شريط التمرير إذا كانت الشاشة صغيرة جداً */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen w-full flex items-center justify-center p-4 sm:p-6 antialiased text-slate-800">

    <div
        class="bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row h-auto max-h-[95vh]">

        <div class="md:w-1/2 bg-slate-900 text-white p-6 sm:p-10 flex flex-col relative overflow-hidden">
            <div
                class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none">
            </div>
            <div
                class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none">
            </div>

            <div class="flex items-center gap-3 mb-8 relative z-10">
                <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-600/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight">Droguerie <span class="text-blue-400">POS</span></h1>
            </div>

            <div class="mb-6 relative z-10">
                <h2 class="text-2xl sm:text-3xl font-bold mb-2">Qui êtes-vous ?</h2>
                <p class="text-slate-400 text-sm">Sélectionnez votre profil pour accéder au système.</p>
            </div>

            @if ($errors->any())
                <div
                    class="bg-red-500/20 border border-red-500/50 text-red-200 p-3 rounded-xl mb-4 text-xs font-semibold flex items-start gap-3 relative z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div
                class="grid grid-cols-2 lg:grid-cols-3 gap-3 overflow-y-auto pr-2 pb-2 relative z-10 flex-1 content-start">
                @foreach($users as $user)
                    <!--------- cmt if === -------->
                    {{-- @if($user->name === 'Abdelilah') @continue @endif --}}
                    <div class="user-card border border-slate-700 bg-slate-800/40 hover:bg-slate-800 rounded-2xl p-4 cursor-pointer flex flex-col items-center gap-3"
                        onclick="selectUser({{ $user->id }}, this)">
                        <div
                            class="user-avatar w-12 h-12 bg-slate-700 text-slate-300 rounded-full flex items-center justify-center text-lg font-bold transition-all duration-300">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-white text-sm line-clamp-1">{{ $user->name }}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">
                                {{ $user->role ?? 'Utilisateur' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="md:w-1/2 bg-white p-6 sm:p-10 flex flex-col justify-center items-center overflow-y-auto">

            <form method="POST" action="{{ route('login') }}" id="loginForm" class="w-full max-w-[300px] py-4">
                @csrf
                <input type="hidden" name="user_id" id="user_id" value="">
                <input type="hidden" name="password" id="password" value="">

                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-1">Code d'accès</h3>
                    <p class="text-slate-500 text-sm font-medium">Saisissez votre PIN</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl h-14 flex items-center justify-center text-2xl font-black tracking-[0.5em] text-slate-300 mb-6 transition-colors duration-300 shadow-inner"
                    id="pinDisplay">
                    ••••
                </div>

                <div class="grid grid-cols-3 gap-3 mb-6">
                    @foreach(['1', '2', '3', '4', '5', '6', '7', '8', '9'] as $digit)
                        <button type="button"
                            class="pin-key h-12 sm:h-14 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xl font-bold text-slate-700 flex items-center justify-center shadow-sm"
                            onclick="addPin('{{ $digit }}')">{{ $digit }}</button>
                    @endforeach

                    <button type="button"
                        class="pin-key h-12 sm:h-14 bg-red-50 hover:bg-red-100 border border-red-100 text-red-500 rounded-xl flex items-center justify-center shadow-sm"
                        onclick="clearPin()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <button type="button"
                        class="pin-key h-12 sm:h-14 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xl font-bold text-slate-700 flex items-center justify-center shadow-sm"
                        onclick="addPin('0')">0</button>

                    <button type="button"
                        class="pin-key h-12 sm:h-14 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 rounded-xl flex items-center justify-center shadow-sm"
                        onclick="deletePin()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                        </svg>
                    </button>
                </div>

                <button type="submit" id="submitBtn" disabled
                    class="w-full bg-blue-600 text-white font-bold text-base h-14 rounded-xl shadow-lg shadow-blue-600/30 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none hover:bg-blue-700 active:scale-[0.98] flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Se connecter
                </button>
            </form>

        </div>
    </div>

    <script>
        let currentPin = '';
        let selectedUserId = null;

        function selectUser(userId, element) {
            selectedUserId = userId;
            document.getElementById('user_id').value = userId;

            // إزالة التحديد عن الجميع
            document.querySelectorAll('.user-card').forEach(card => {
                card.classList.remove('selected');
            });

            // إضافة التحديد للعنصر الحالي
            element.classList.add('selected');

            // مسح الـ PIN عند تغيير المستخدم
            clearPin();
        }

        function addPin(digit) {
            if (currentPin.length < 8) { // تحديد طول أقصى
                currentPin += digit;
                updatePinDisplay();
                document.getElementById('password').value = currentPin;
                checkSubmitState();
            }
        }

        function deletePin() {
            currentPin = currentPin.slice(0, -1);
            updatePinDisplay();
            document.getElementById('password').value = currentPin;
            checkSubmitState();
        }

        function clearPin() {
            currentPin = '';
            updatePinDisplay();
            document.getElementById('password').value = '';
            checkSubmitState();
        }

        function updatePinDisplay() {
            const display = document.getElementById('pinDisplay');
            if (currentPin.length === 0) {
                display.textContent = '••••';
                display.classList.remove('text-slate-800', 'border-blue-400');
                display.classList.add('text-slate-300', 'border-slate-200');
            } else {
                display.textContent = '•'.repeat(currentPin.length);
                display.classList.add('text-slate-800', 'border-blue-400');
                display.classList.remove('text-slate-300', 'border-slate-200');
            }
        }

        function checkSubmitState() {
            const btn = document.getElementById('submitBtn');
            if (currentPin.length > 0 && selectedUserId !== null) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }

        // Keyboard support for PIN entry
        document.addEventListener('keydown', function (event) {
            // Prevent default for specific keys
            if (['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Backspace', 'Delete', 'Escape', 'Enter'].includes(event.key)) {
                event.preventDefault();
            }

            // Number keys (0-9)
            if (event.key >= '0' && event.key <= '9') {
                addPin(event.key);
            }
            // Backspace or Delete - remove last digit
            else if (event.key === 'Backspace' || event.key === 'Delete') {
                deletePin();
            }
            // Escape - clear all
            else if (event.key === 'Escape') {
                clearPin();
            }
            // Enter - submit form if ready
            else if (event.key === 'Enter') {
                const btn = document.getElementById('submitBtn');
                if (!btn.disabled) {
                    document.getElementById('loginForm').submit();
                }
            }
        });

        checkSubmitState();
    </script>
</body>

</html>