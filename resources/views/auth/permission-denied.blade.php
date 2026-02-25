<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-900/95 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900">Accès refusé</h1>
                <p class="text-sm text-slate-500">Permission manquante</p>
            </div>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 p-3 text-sm font-medium mb-4">
            {{ $message }}
        </div>

        <div class="text-sm text-slate-600 mb-2">Permission requise: <span class="font-semibold text-slate-800">{{ $permission }}</span></div>
        <div class="text-sm text-slate-600 mb-6">Redirection vers POS dans <span id="countdown" class="font-bold text-blue-700">{{ $countdownSeconds }}</span>s...</div>

        <a href="{{ $redirectUrl }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-4 py-2.5 transition-colors">
            Aller maintenant
        </a>
    </div>

    <script>
        let seconds = {{ (int) $countdownSeconds }};
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(() => {
            seconds -= 1;
            countdownEl.textContent = Math.max(seconds, 0);

            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = @json($redirectUrl);
            }
        }, 1000);
    </script>
</body>
</html>
